<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use ZipArchive;

/** Bounded, value-only reader for the first sheet. Does not evaluate formulas or links. */
class XlsxTableReader
{
    public function read(string $path, int $maxRows = 5002, int $maxColumns = 64): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            $this->error('Invalid XLSX file.');
        }
        try {
            $size = 0;
            if ($zip->numFiles > 1000) {
                $this->error('The expanded Excel file is too large.');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $size += $zip->statIndex($i)['size'];
                if ($size > 20 * 1024 * 1024) {
                    $this->error('The expanded Excel file is too large.');
                }
            }
            $shared = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach ($this->xml($zip, 'xl/sharedStrings.xml')->si as $item) {
                    $shared[] = implode('', array_map('strval', $item->xpath('.//*[local-name()="t"]')));
                }
            }
            $book = $this->xml($zip, 'xl/workbook.xml');
            if (! isset($book->sheets->sheet[0])) {
                $this->error('Cannot read the first worksheet.');
            }
            $id = (string) $book->sheets->sheet[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
            $target = null;
            foreach ($this->xml($zip, 'xl/_rels/workbook.xml.rels')->Relationship as $rel) {
                if ((string) $rel['Id'] === $id && (string) $rel['TargetMode'] !== 'External') {
                    $target = (string) $rel['Target'];
                }
            }
            if (! $target || str_contains($target, '..') || str_contains($target, ':')) {
                $this->error('Cannot read the first worksheet.');
            }
            $target = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/' . $target;
            $rows = [];
            foreach ($this->xml($zip, $target)->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $cell) {
                    if (isset($cell->f)) {
                        $this->error('Excel formulas are not supported. Paste values before uploading.');
                    }
                    if (! preg_match('/^([A-Z]+)[0-9]+$/', (string) $cell['r'], $match)) {
                        $this->error('Invalid cell address.');
                    }
                    $column = 0;
                    foreach (str_split($match[1]) as $letter) {
                        $column = $column * 26 + ord($letter) - 64;
                    }
                    if ($column > $maxColumns) {
                        $this->error('Too many columns in this spreadsheet.');
                    }
                    $type = (string) $cell['t'];
                    if ($type === 'e') {
                        $this->error('The spreadsheet contains a cell error. Correct it before uploading.');
                    }
                    $value = (string) $cell->v;
                    if ($type === 's') {
                        $value = $shared[(int) $value] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]')));
                    }
                    $cells[$column - 1] = $value;
                }
                $rows[(int) $row['r']] = $cells;
                if (count($rows) > $maxRows) {
                    $this->error('Too many rows in this spreadsheet.');
                }
            }

            return ['rows' => $rows, 'date1904' => in_array((string) $book->workbookPr['date1904'], ['1', 'true'], true)];
        } finally {
            $zip->close();
        }
    }

    private function xml(ZipArchive $zip, string $name): SimpleXMLElement
    {
        $raw = $zip->getFromName($name);
        if ($raw === false || stripos($raw, '<!DOCTYPE') !== false || stripos($raw, '<!ENTITY') !== false) {
            $this->error('Invalid Excel structure.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($raw, SimpleXMLElement::class, LIBXML_NONET);
            if ($xml === false) {
                $this->error('Invalid Excel XML.');
            }

            return $xml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function error(string $message): void
    {
        throw ValidationException::withMessages(['file' => __($message)]);
    }
}
