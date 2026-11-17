<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseAccessTokenProvider
{
    public function accessToken(): string
    {
        $clientEmail = (string) config('services.fcm.client_email');
        $privateKey = str_replace('\\n', "\n", (string) config('services.fcm.private_key'));

        if ($clientEmail === '' || $privateKey === '') {
            throw new RuntimeException('Service account Firebase belum dikonfigurasi.');
        }

        $cacheKey = 'fcm:oauth:'.hash('sha256', $clientEmail.$privateKey);
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $issuedAt = now()->timestamp;
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ], JSON_THROW_ON_ERROR));
        $unsignedToken = $header.'.'.$claims;

        if (! openssl_sign($unsignedToken, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Kunci service account Firebase tidak valid.');
        }

        $response = Http::asForm()->connectTimeout(3)->timeout(10)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsignedToken.'.'.$this->base64UrlEncode($signature),
            ])->throw();
        $accessToken = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 0);

        if (! is_string($accessToken) || $accessToken === '' || $expiresIn <= 60) {
            throw new RuntimeException('Respons autentikasi Firebase tidak valid.');
        }

        Cache::put($cacheKey, $accessToken, min($expiresIn - 60, 3540));

        return $accessToken;
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
