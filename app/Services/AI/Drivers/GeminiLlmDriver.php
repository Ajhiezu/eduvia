<?php

namespace App\Services\AI\Drivers;

use App\Services\AI\Contracts\LlmDriverInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiLlmDriver implements LlmDriverInterface
{
    public function __construct(
        protected ?string $apiKey = null,
        protected string $model = 'gemini-1.5-flash',
        protected int $timeoutSeconds = 30
    ) {
        $this->apiKey = $apiKey ?: config('services.gemini.api_key', env('GEMINI_API_KEY'));
    }

    /**
     * Generate structured response from Google Gemini.
     */
    public function generateStructured(string $systemPrompt, string $userContent, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Google Gemini API Key belum dikonfigurasi pada sistem Eduvia.');
        }

        $model = $options['model'] ?? $this->model;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userContent],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.3,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->retry(2, 500, throw: false)
                ->post($url, $payload);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->json();
                $message = $body['error']['message'] ?? $response->body();

                Log::error("Gemini API call failed with status {$status}: {$message}");

                if ($status === 429) {
                    throw new RuntimeException('Layanan AI sedang sibuk (Batas laju per menit tercapai). Silakan coba 30 detik lagi.');
                }

                throw new RuntimeException("Koneksi penyedia AI gagal: {$message}");
            }

            $json = $response->json();
            $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed = json_decode($rawText, true);

            if (! is_array($parsed)) {
                // If model wrapped JSON in markdown code fence
                $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawText));
                $parsed = json_decode($clean, true);
            }

            if (! is_array($parsed)) {
                throw new RuntimeException('Penyedia AI mengembalikan format respons yang tidak valid.');
            }

            $usage = $json['usageMetadata'] ?? [];
            $promptTokens = $usage['promptTokenCount'] ?? 250;
            $completionTokens = $usage['candidatesTokenCount'] ?? 400;

            return [
                'content' => $parsed,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $promptTokens + $completionTokens,
                'model' => $model,
                'provider' => 'gemini',
            ];
        } catch (ConnectionException $e) {
            Log::error('Gemini connection timeout: '.$e->getMessage());
            throw new RuntimeException('Koneksi ke penyedia AI mengalami batas waktu (timeout). Silakan periksa jaringan server Anda.');
        }
    }
}
