<?php

namespace Tests\Unit;

use App\Support\CsvSanitizer;
use PHPUnit\Framework\TestCase;

class CsvSanitizerTest extends TestCase
{
    public function test_it_disarms_formula_characters(): void
    {
        $this->assertEquals("'=SUM(A1:A10)", CsvSanitizer::sanitize('=SUM(A1:A10)'));
        $this->assertEquals("'+123456", CsvSanitizer::sanitize('+123456'));
        $this->assertEquals("'-calc", CsvSanitizer::sanitize('-calc'));
        $this->assertEquals("'@payload", CsvSanitizer::sanitize('@payload'));
        $this->assertEquals("'\tcmd", CsvSanitizer::sanitize("\tcmd"));
        $this->assertEquals("'\rcmd", CsvSanitizer::sanitize("\rcmd"));
    }

    public function test_it_leaves_safe_strings_and_numbers_untouched(): void
    {
        $this->assertEquals('Ahmad Dahlan', CsvSanitizer::sanitize('Ahmad Dahlan'));
        $this->assertEquals('user@eduvia.sch.id', CsvSanitizer::sanitize('user@eduvia.sch.id'));
        $this->assertEquals(150000, CsvSanitizer::sanitize(150000));
        $this->assertNull(CsvSanitizer::sanitize(null));
    }

    public function test_it_sanitizes_entire_row(): void
    {
        $rawRow = [
            1,
            '=cmd|\' /C calc\'!A0',
            'Safe Name',
            '@evil',
        ];

        $sanitizedRow = CsvSanitizer::sanitizeRow($rawRow);

        $this->assertEquals(1, $sanitizedRow[0]);
        $this->assertEquals("'=cmd|' /C calc'!A0", $sanitizedRow[1]);
        $this->assertEquals('Safe Name', $sanitizedRow[2]);
        $this->assertEquals("'@evil", $sanitizedRow[3]);
    }
}
