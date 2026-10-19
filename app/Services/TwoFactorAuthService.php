<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    protected const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographic random Base32 secret key for TOTP.
     */
    public function generateSecretKey(int $length = 32): string
    {
        $secret = '';
        $bytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[ord($bytes[$i]) % 32];
        }

        return $secret;
    }

    /**
     * Generate an otpauth:// URI for authenticator apps (Google Authenticator, Microsoft Authenticator).
     */
    public function getOtpAuthUrl(string $company, string $accountName, string $secret): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($company),
            rawurlencode($accountName),
            $secret,
            rawurlencode($company)
        );
    }

    /**
     * Calculate TOTP code for a specific timestamp and secret.
     */
    public function calculateCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = (int) floor($timestamp / 30);

        $secretBinary = $this->base32Decode($secret);

        // Pack timeSlice into 64-bit big-endian binary string
        $timePacked = pack('N*', 0).pack('N*', $timeSlice);

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $timePacked, $secretBinary, true);

        // Dynamic truncation (RFC 4226)
        $offset = ord(substr($hash, -1)) & 0x0F;
        $hashPart = substr($hash, $offset, 4);

        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $code = (string) ($value % 1000000);

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify whether a provided 6-digit TOTP code is valid within the time window.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $currentTime = time();
        $code = trim($code);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculated = $this->calculateCode($secret, $currentTime + ($i * 30));
            if (hash_equals($calculated, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a batch of random alphanumeric recovery codes.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(10));
        }

        return $codes;
    }

    /**
     * Enable 2FA on a user.
     */
    public function enable(User $user, string $secret, array $recoveryCodes): void
    {
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Disable 2FA on a user.
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Verify and consume a recovery code.
     */
    public function verifyAndConsumeRecoveryCode(User $user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        if (! is_array($codes)) {
            return false;
        }

        $code = strtoupper(trim($code));
        $key = array_search($code, $codes, true);

        if ($key !== false) {
            unset($codes[$key]);
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Decode a Base32 string to binary data.
     */
    protected function base32Decode(string $b32): string
    {
        $b32 = strtoupper(rtrim($b32, '='));
        $binary = '';

        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($b32); $i++) {
            $val = strpos(self::BASE32_ALPHABET, $b32[$i]);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }
}
