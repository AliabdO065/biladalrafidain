<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/** Read values only from the first XLSX sheet, or a UTF-8 CSV. Never evaluate formulas. */
class AccountSpreadsheetReader
{
    public function read(UploadedFile $file): array
    {
        $rows = strtolower($file->getClientOriginalExtension()) === 'xlsx'
            ? $this->xlsx($file->getRealPath()) : $this->csv($file->getRealPath());
        $header = array_shift($rows);
        $aliases = ['الاسم' => 'name', 'اسم التاجر' => 'name', 'الهاتف' => 'phone', 'رقم الهاتف' => 'phone', 'الحساب' => 'amount', 'المبلغ' => 'amount', 'العملة' => 'currency', 'البيان' => 'details'];
        $header = array_map(fn ($value) => $aliases[trim($value)] ?? strtolower(trim($value)), $header ?? []);
        foreach (['name', 'phone', 'amount', 'currency'] as $column) {
            if (! in_array($column, $header, true)) {
                $this->error(__('Missing required column: :column', ['column' => $column]));
            }
        }
        if (count($header) !== count(array_unique($header))) {
            $this->error('Duplicate column headings.');
        }
        $result = [];
        $phones = [];
        foreach ($rows as $index => $row) {
            if (! array_filter($row, fn ($value) => trim($value) !== '')) {
                continue;
            }
            $values = [];
            foreach ($header as $position => $column) {
                $values[$column] = trim($row[$position] ?? '');
            }
            $phone = $this->digits($values['phone']);
            $phone = preg_replace('/[\s()\-]/u', '', $phone);
            if (str_starts_with($phone, '00')) {
                $phone = '+' . substr($phone, 2);
            } elseif (! str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }
            $amount = $this->digits($values['amount']);
            $amount = str_replace('٫', '.', $amount);
            $currency = strtoupper($values['currency']);
            $line = $index + 2;
            if (! preg_match('/^\+[1-9][0-9]{7,14}$/D', $phone)) {
                $this->error(__('Row :row: phone must include the country code, for example +201012345678.', ['row' => $line]));
            }
            if (isset($phones[$phone])) {
                $this->error(__('Row :row: duplicate phone number in this file.', ['row' => $line]));
            }
            if (! preg_match('/^-?(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?$/D', $amount)) {
                $this->error(__('Row :row: invalid amount. Use up to two decimal places without thousands separators.', ['row' => $line]));
            }
            if (! in_array($currency, ['EGP', 'IQD', 'USD', 'EUR', 'SAR', 'AED', 'TRY'], true)) {
                $this->error(__('Row :row: unsupported currency. Use EGP, IQD, USD, EUR, SAR, AED or TRY.', ['row' => $line]));
            }
            if ($values['name'] === '' || mb_strlen($values['name']) > 191 || mb_strlen($values['details'] ?? '') > 500) {
                $this->error(__('Row :row: enter a name up to 191 characters and details up to 500 characters.', ['row' => $line]));
            }
            $phones[$phone] = true;
            $result[] = ['name' => $values['name'], 'phone' => $phone, 'amount' => $amount, 'currency' => $currency, 'details' => $values['details'] ?? null];
        }
        if (! $result) {
            $this->error('The file contains no account rows.');
        }

        return $result;
    }

    private function csv(string $path): array
    {
        $handle = fopen($path, 'rb');
        try {
            $first = fgets($handle);
            $delimiter = substr_count((string) $first, ';') > substr_count((string) $first, ',') ? ';' : ',';
            rewind($handle);
            $rows = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $row = array_map(fn ($value) => (string) $value, $row);
                if (! mb_check_encoding(implode('', $row), 'UTF-8')) {
                    $this->error('Save the CSV using UTF-8 encoding.');
                }
                if (! $rows) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                }
                $rows[] = $row;
                if (count($rows) > 1001 || count($row) > 30) {
                    $this->error('Maximum 1000 account rows and 30 columns per file.');
                }
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function xlsx(string $path): array
    {
        return array_values(app(XlsxTableReader::class)->read($path, 1001, 30)['rows']);
    }
    private function digits(string $value): string
    {
        return strtr($value, array_combine(preg_split('//u', '٠١٢٣٤٥٦٧٨٩۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789')));
    }

    private function error(string $message): void
    {
        throw ValidationException::withMessages(['file' => __($message)]);
    }
}
