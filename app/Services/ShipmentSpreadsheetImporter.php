<?php

namespace App\Services;

use App\Models\LandingShipment;
use App\Models\LandingTrip;
use App\Models\ShipmentImport;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentSpreadsheetImporter
{
    public const HEADERS = [
        'م' => 'report_sequence',
        'بيانات السند - رقم السند' => 'receipt_number',
        'بيانات السند - التاريخ' => 'receipt_date',
        'بيانات المصنع / الشركة - الرقم' => 'factory_number',
        'بيانات المصنع / الشركة - المرسل' => 'sender_name',
        'بيانات التاجر / المستفيد - الرقم' => 'merchant_number',
        'بيانات التاجر / المستفيد - المستلم' => 'receiver_name',
        'بيانات التاجر / المستفيد - الهاتف' => 'receiver_phone',
        'بيانات التاجر / المستفيد - المدينة' => 'city',
        'رقم الرحلة' => 'trip_reference',
        'عدد الطرود' => 'packages_count',
        'الوزن/ كجم' => 'weight_kg',
        'سعر الصنف' => 'item_price',
        'أجرة الشحن' => 'shipping_fee',
        'الدفع المحصلة' => 'paid_amount',
        'الباقي' => 'balance',
        'المجموع' => 'total',
        'ملاحظات' => 'notes',
    ];

    public function import(UploadedFile $file, array $defaults, int $userId): ShipmentImport
    {
        $hash = hash_file('sha256', $file->getRealPath());
        if ($existing = ShipmentImport::where('file_hash', $hash)->first()) {
            return $existing;
        }
        $rows = $this->parse($file);
        try {
            return DB::transaction(function () use ($file, $hash, $defaults, $userId, $rows) {
                $import = ShipmentImport::create(['file_name' => mb_substr($file->getClientOriginalName(), 0, 191), 'file_hash' => $hash, 'created_by' => $userId]);
                $created = 0;
                $skipped = 0;
                $tripCount = 0;
                $trips = [];
                foreach ($rows as $row) {
                    if (LandingShipment::where('source_fingerprint', $row['source_fingerprint'])->exists()) {
                        $skipped++;
                        continue;
                    }
                    $reference = $row['trip_reference'];
                    if (! isset($trips[$reference])) {
                        $trip = LandingTrip::where('reference', $reference)->first();
                        if (! $trip) {
                            $trip = LandingTrip::create([
                                'reference' => $reference, 'origin' => $defaults['origin'], 'destination' => $defaults['destination'],
                                'departure_date' => $defaults['departure_date'] ?? null, 'status' => 'received',
                            ]);
                            $trip->events()->create(['status' => 'received', 'happened_at' => now()]);
                            $tripCount++;
                        }
                        $trips[$reference] = $trip;
                    }
                    $trip = $trips[$reference];
                    unset($row['trip_reference']);
                    $shipment = LandingShipment::create($row + [
                        'trip_id' => $trip->id, 'import_id' => $import->id,
                        'tracking_code' => 'BR-' . strtoupper(Str::random(16)),
                        'origin' => $trip->origin, 'destination' => $row['city'] ?: $trip->destination,
                        'currency' => $defaults['currency'] ?? null,
                        'status' => 'received',
                    ]);
                    $shipment->events()->create(['status' => 'received', 'happened_at' => now()]);
                    app(OperationNotifications::class)->shipment($shipment);
                    $created++;
                }
                $import->update(['shipments_created' => $created, 'shipments_skipped' => $skipped, 'trips_created' => $tripCount]);

                return $import;
            });
        } catch (QueryException $exception) {
            // Simultaneous upload of the same file resolves to the completed import.
            if ($existing = ShipmentImport::where('file_hash', $hash)->first()) {
                return $existing;
            }
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['file' => __('Another import changed the same records. Upload the file again.')]);
            }
            throw $exception;
        }
    }

    public function parse(UploadedFile $file): array
    {
        $sheet = app(XlsxTableReader::class)->read($file->getRealPath(), 10010);
        $map = null;
        $rows = [];
        $occurrences = [];
        $labels = array_flip(self::HEADERS);
        foreach ($sheet['rows'] as $line => $cells) {
            if (! array_filter($cells, fn ($value) => trim($value) !== '')) {
                continue;
            }
            if ($map === null) {
                $candidate = [];
                foreach ($cells as $column => $value) {
                    $normalized = $this->header($value);
                    foreach (self::HEADERS as $label => $field) {
                        if ($normalized === $this->header($label)) {
                            if (in_array($field, $candidate, true)) {
                                $this->error($line, __('Duplicate column headings.'));
                            }
                            $candidate[$column] = $field;
                        }
                    }
                }
                if (in_array('trip_reference', $candidate, true) && in_array('receiver_name', $candidate, true)) {
                    foreach (array_values(self::HEADERS) as $field) {
                        if (! in_array($field, $candidate, true)) {
                            $this->error($line, __('Missing required column: :column', ['column' => $labels[$field]]));
                        }
                    }
                    $map = $candidate;
                }
                continue;
            }
            $totalsLabels = ['الاجمالي', 'الإجمالي', 'اجمالي', 'الإجمالى', 'الاجمالى', 'total', 'grand total'];
            $sequenceColumn = array_search('report_sequence', $map, true);
            $tripColumn = array_search('trip_reference', $map, true);
            if (in_array($this->header($cells[$sequenceColumn] ?? ''), $totalsLabels, true)
                && (trim($cells[$tripColumn] ?? '') === '' || in_array($this->header($cells[$tripColumn] ?? ''), $totalsLabels, true))) {
                continue;
            }
            // Some ERP exports repeat their headings between printed pages.
            if ($this->header($cells[array_search('trip_reference', $map, true)] ?? '') === $this->header('رقم الرحلة')) {
                continue;
            }
            $data = [];
            foreach ($map as $column => $field) {
                $value = trim($cells[$column] ?? '');
                $data[$field] = $value === '' ? null : $value;
            }
            $data['trip_reference'] = $this->digits($data['trip_reference'] ?? '');
            if (preg_match('/^[0-9]+\.0+$/D', $data['trip_reference'])) {
                $data['trip_reference'] = strstr($data['trip_reference'], '.', true);
            }
            if ($data['receipt_date']) {
                $data['receipt_date'] = $this->date($data['receipt_date'], $sheet['date1904'], $line);
            }
            foreach (['packages_count' => 0, 'weight_kg' => 3, 'item_price' => 4, 'shipping_fee' => 2, 'paid_amount' => 2, 'balance' => 2, 'total' => 2] as $field => $scale) {
                if ($data[$field] === null) {
                    continue;
                }
                $data[$field] = $this->decimal($data[$field], $scale, $line, $labels[$field]);
            }
            $validator = Validator::make($data, [
                'trip_reference' => 'required|string|max:100',
                'sender_name' => 'required|string|max:191',
                'receiver_name' => 'required|string|max:191',
                'receipt_number' => 'nullable|string|max:191',
                'factory_number' => 'nullable|string|max:191',
                'merchant_number' => 'nullable|string|max:191',
                'receiver_phone' => 'nullable|string|max:191',
                'city' => 'nullable|string|max:191',
                'report_sequence' => 'nullable|string|max:191',
                'packages_count' => 'nullable|integer|min:0|max:1000000',
                'weight_kg' => 'nullable|numeric|min:0|max:999999999999.999',
                'notes' => 'nullable|string|max:5000',
            ], [], $labels);
            if ($validator->fails()) {
                $this->error($line, $validator->errors()->first());
            }
            // Preserve legitimate identical lines while recognizing them in later exports.
            $identity = $data;
            unset($identity['report_sequence']);
            ksort($identity);
            $signature = hash('sha256', json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            $occurrences[$signature] = ($occurrences[$signature] ?? 0) + 1;
            $data['source_fingerprint'] = hash('sha256', $signature . ':' . $occurrences[$signature]);
            $data['source_row'] = $line;
            $rows[] = $data;
            if (count($rows) > 5000) {
                $this->error($line, __('Maximum 5000 shipments per file.'));
            }
        }
        if ($map === null || ! $rows) {
            throw ValidationException::withMessages(['file' => __('No shipment rows found. Use the Infosas receipt report with its original column headings.')]);
        }

        return $rows;
    }

    private function decimal(string $value, int $scale, int $line, string $label): string
    {
        $value = str_replace(['٬', ','], '', str_replace('٫', '.', $this->digits($value)));
        if (! preg_match('/^(-?)([0-9]{1,12})(?:\.([0-9]+))?$/D', $value, $match)) {
            $this->error($line, __('Invalid number in column :column.', ['column' => $label]));
        }
        $fraction = rtrim($match[3] ?? '', '0');
        if (strlen($fraction) > $scale) {
            $this->error($line, __('Too many decimal places in column :column.', ['column' => $label]));
        }
        $whole = ltrim($match[2], '0') ?: '0';
        $fraction = str_pad($fraction, $scale, '0');
        $sign = $match[1] === '-' && ($whole !== '0' || trim($fraction, '0') !== '') ? '-' : '';

        return $sign . $whole . ($scale ? '.' . $fraction : '');
    }

    private function date(string $value, bool $date1904, int $line): string
    {
        $value = $this->digits($value);
        if (preg_match('/^[0-9]+(?:\.[0-9]+)?$/D', $value) && (int) $value > 0 && (int) $value < 100000) {
            return CarbonImmutable::parse($date1904 ? '1904-01-01' : '1899-12-30')->addDays((int) $value)->format('Y-m-d');
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $exception) {
                // Try the next explicit, unambiguous format.
            }
        }
        $this->error($line, __('Invalid receipt date. Use YYYY-MM-DD or DD/MM/YYYY.'));
    }

    private function header(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $value))));
    }

    private function digits(string $value): string
    {
        return strtr($value, array_combine(preg_split('//u', '٠١٢٣٤٥٦٧٨٩۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789')));
    }

    private function error(int $line, string $message): void
    {
        throw ValidationException::withMessages(['file' => __('Excel row :row: :message', ['row' => $line, 'message' => $message])]);
    }
}
