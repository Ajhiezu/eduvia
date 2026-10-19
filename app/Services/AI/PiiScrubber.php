<?php

namespace App\Services\AI;

class PiiScrubber
{
    /**
     * Scrub all Personally Identifiable Information (PII) from text string.
     */
    public function scrub(string $text): string
    {
        // 1. Scrub NIK (16 digits)
        $text = preg_replace('/\b[1-9][0-9]{15}\b/', '[NIK_DISAMARKAN]', $text);

        // 2. Scrub NISN (10 digits)
        $text = preg_replace('/\b[0-9]{10}\b/', '[NISN_DISAMARKAN]', $text);

        // 3. Scrub Indonesian phone / WhatsApp numbers (08xx, +628xx, 628xx)
        $text = preg_replace('/(?:\+62|62|0)8[1-9][0-9]{7,11}\b/', '[NO_HP_DISAMARKAN]', $text);

        // 4. Scrub email addresses
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL_DISAMARKAN]', $text);

        return $text;
    }

    /**
     * Scrub an associative array recursively.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function scrubArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->scrub($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->scrubArray($value);
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
