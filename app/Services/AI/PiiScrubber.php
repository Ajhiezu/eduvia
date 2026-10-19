<?php

namespace App\Services\AI;

class PiiScrubber
{
    /**
     * Scrub all Personally Identifiable Information (PII) from text string.
     *
     * @param  list<string>  $namesToScrub
     */
    public function scrub(string $text, array $namesToScrub = []): string
    {
        // 1. Scrub explicit names if provided
        if (! empty($namesToScrub)) {
            $text = $this->scrubNames($text, $namesToScrub);
        }

        // 2. Scrub NIK (16 digits)
        $text = preg_replace('/\b[1-9][0-9]{15}\b/', '[NIK_DISAMARKAN]', $text);

        // 3. Scrub NISN (10 digits)
        $text = preg_replace('/\b[0-9]{10}\b/', '[NISN_DISAMARKAN]', $text);

        // 4. Scrub Indonesian phone / WhatsApp numbers (08xx, +628xx, 628xx)
        $text = preg_replace('/(?:\+62|62|0)8[1-9][0-9]{7,11}\b/', '[NO_HP_DISAMARKAN]', $text);

        // 5. Scrub email addresses
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL_DISAMARKAN]', $text);

        return $text;
    }

    /**
     * Scrub explicit personal names (Student / Teacher names) from text string.
     *
     * @param  list<string>  $names
     */
    public function scrubNames(string $text, array $names): string
    {
        foreach ($names as $name) {
            $name = trim($name);
            if (mb_strlen($name) < 3) {
                continue;
            }
            $escaped = preg_quote($name, '/');
            $text = preg_replace('/\b'.$escaped.'\b/iu', '[NAMA_DISAMARKAN]', $text);
        }

        return $text;
    }

    /**
     * Scrub an associative array recursively.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $namesToScrub
     * @return array<string, mixed>
     */
    public function scrubArray(array $data, array $namesToScrub = []): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->scrub($value, $namesToScrub);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->scrubArray($value, $namesToScrub);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Check if a text contains unscrubbed PII.
     */
    public function containsPii(string $text): bool
    {
        // Check 16 digit NIK
        if (preg_match('/\b[1-9][0-9]{15}\b/', $text)) {
            return true;
        }

        // Check 10 digit NISN
        if (preg_match('/\b[0-9]{10}\b/', $text)) {
            return true;
        }

        // Check phone number
        if (preg_match('/(?:\+62|62|0)8[1-9][0-9]{7,11}\b/', $text)) {
            return true;
        }

        // Check email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            return true;
        }

        return false;
    }
}
