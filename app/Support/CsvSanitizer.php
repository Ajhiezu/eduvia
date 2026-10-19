<?php

namespace App\Support;

class CsvSanitizer
{
    /**
     * Sanitize a cell value against CSV / Spreadsheet formula injection (CWE-1236).
     * Disarms =, +, -, @, tab (\t), and carriage return (\r) prefixes.
     */
    public static function sanitize(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (preg_match('/^[\=\+\-\@\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Sanitize an entire array of CSV row cells.
     *
     * @param  array<mixed>  $row
     * @return array<mixed>
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map([static::class, 'sanitize'], $row);
    }
}
