<?php

namespace App\Services;

use App\Models\TradeImport;
use App\Models\TradeVoucher;
use App\Models\TradingContact;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VoucherSpreadsheetImporter
{
    private function normal(string $text): string
    {
        return preg_replace('/[\s\x{0640}\x{064B}-\x{065F}]+/u', '', trim($text));
    }

    public function preview(UploadedFile $file, string $source, string $currency, int $userId): TradeImport
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $existing = TradeImport::where(['kind' => $source, 'file_hash' => $hash, 'currency' => $currency])->first();
        if ($existing && ($existing->confirmed_at || $existing->payload)) {
            return $existing;
        }
        $table = (new XlsxTableReader)->read($file->getRealPath());
        $headers = null;
        $rows = [];
        $excluded = 0;
        foreach ($table['rows'] as $line => $cells) {
            if (in_array($this->normal('رقم السند'), array_map(fn ($value) => $this->normal($value), $cells), true)) {
                $headers = [];
                foreach ($cells as $column => $heading) {
                    $headers[$this->normal($heading)] = $column;
                }
                foreach (['رقم السند', 'التاريخ', 'نوع العملية', 'رقم التاجر', 'المستفيد / التاجر', 'المبلغ', 'رقم الرحلة', 'المصانع / الشركات'] as $heading) {
                    if (! array_key_exists($this->normal($heading), $headers)) {
                        $this->error($line, __('trade.wrong_columns'));
                    }
                }

                continue;
            }
            if (! $headers || ! preg_match('/^[0-9]+$/D', trim($cells[0] ?? ''))) {
                continue;
            }
            $cell = fn ($name) => trim((string) ($cells[$headers[$this->normal($name)] ?? -1] ?? ''));
            $type = $this->normal($cell('نوع العملية'));
            $kind = $source === 'payments' ? ($type === 'مشتريات' ? 'supplier_payment' : null)
                : (['تسديدلحسابالمشتريات' => 'purchase_receipt', 'تسديدنقل' => 'shipping_receipt'][$type] ?? null);
            if (! $kind) {
                $excluded++;

                continue;
            }
            $amount = $cell('المبلغ');
            if (preg_match('/^[0-9]{1,3}(?:,[0-9]{3})+(?:\.[0-9]{1,2})?$/D', $amount)) {
                $amount = str_replace(',', '', $amount);
            }
            $data = ['reference' => $cell('رقم السند'), 'custom_reference' => $cell('رقم السند المخصص') ?: null,
                'number' => $cell('رقم التاجر'), 'name' => $cell('المستفيد / التاجر'), 'amount' => $amount,
                'supplier' => $cell('المصانع / الشركات') ?: null, 'trip_reference' => $cell('رقم الرحلة') ?: null,
                'description' => $cell('البيان') ?: null, 'kind' => $kind, 'currency' => $currency];
            $validator = Validator::make($data, ['reference' => 'required|string|max:100', 'custom_reference' => 'nullable|string|max:100',
                'number' => 'required|string|max:100', 'name' => 'required|string|max:191', 'amount' => ['required', 'regex:/^[0-9]{1,12}(?:\.[0-9]{1,2})?$/D', 'numeric', 'gt:0'],
                'supplier' => 'nullable|string|max:191', 'trip_reference' => 'nullable|string|max:100', 'description' => 'nullable|string|max:2000']);
            if ($validator->fails()) {
                $this->error($line, $validator->errors()->first());
            }
            try {
                $date = $cell('التاريخ');
                if (preg_match('/^[0-9]{5}$/D', $date)) {
                    $parsed = CarbonImmutable::parse($table['date1904'] ? '1904-01-01' : '1899-12-30')->addDays((int) $date);
                } else {
                    $format = str_contains($date, '/') ? '!Y/m/d' : '!Y-m-d';
                    $parsed = CarbonImmutable::createFromFormat($format, $date);
                    if (! $parsed || $parsed->format(substr($format, 1)) !== $date) {
                        throw new \RuntimeException;
                    }
                }
                $data['occurred_at'] = $parsed->format('Y-m-d');
            } catch (\Throwable $error) {
                $this->error($line, __('trade.invalid_date'));
            }
            [$whole,$fraction] = array_pad(explode('.', $amount, 2), 2, '');
            $data['amount'] = (ltrim($whole, '0') ?: '0').'.'.str_pad($fraction, 2, '0');
            $data['fingerprint'] = hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            if (isset($rows[$data['reference']]) && $rows[$data['reference']]['fingerprint'] !== $data['fingerprint']) {
                $this->error($line, __('trade.changed_voucher'));
            }
            $rows[$data['reference']] = $data;
            if (count($rows) > 5000) {
                $this->error($line, __('Maximum 5000 shipments per file.'));
            }
        }
        if (! $headers || ! $rows) {
            $this->error(0, __('trade.no_operations'));
        }
        $values = ['payload' => array_values($rows), 'excluded' => $excluded, 'created_by' => $userId];
        if ($existing) {
            $existing->update($values);

            return $existing;
        }

        return TradeImport::create(['kind' => $source, 'file_hash' => $hash, 'currency' => $currency] + $values);
    }

    public function confirm(TradeImport $import): TradeImport
    {
        return DB::transaction(function () use ($import) {
            $import = TradeImport::lockForUpdate()->findOrFail($import->id);
            if ($import->confirmed_at) {
                return $import;
            }
            if (! $import->payload) {
                throw ValidationException::withMessages(['file' => __('trade.preview_expired')]);
            }
            $created = 0;
            foreach ($import->payload as $row) {
                $old = TradeVoucher::where(['source' => $import->kind, 'reference' => $row['reference']])->first();
                if ($old) {
                    if ($old->fingerprint !== $row['fingerprint']) {
                        throw ValidationException::withMessages(['file' => __('trade.changed_voucher').' '.$row['reference']]);
                    }

                    continue;
                }
                $contact = TradingContact::firstOrCreate(['number' => $row['number']], ['name' => $row['name']]);
                unset($row['number'], $row['name']);
                $voucher = TradeVoucher::create($row + ['source' => $import->kind, 'import_id' => $import->id, 'contact_id' => $contact->id]);
                app(OperationNotifications::class)->voucher($voucher);
                $created++;
            }
            $import->update(['payload' => null, 'created_count' => $created, 'confirmed_at' => now()]);

            return $import;
        });
    }

    private function error(int $line, string $message): void
    {
        throw ValidationException::withMessages(['file' => __('trade.row_error', ['row' => $line]).' '.$message]);
    }
}
