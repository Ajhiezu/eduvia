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
        protected ?string $model = null,
        protected int $timeoutSeconds = 30
    ) {
        $this->apiKey = $apiKey ?: config('services.gemini.api_key');
        $this->model = $model ?: config('services.gemini.model', 'gemini-3.1-flash-lite');
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
        if (! preg_match('/^gemini-[a-zA-Z0-9._-]+$/', $model)) {
            throw new RuntimeException('Model Gemini pada konfigurasi server tidak valid.', 503);
        }
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

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
                'maxOutputTokens' => $options['max_output_tokens'] ?? 8192,
            ],
        ];

        try {
            $response = Http::connectTimeout(5)->timeout($this->timeoutSeconds)
                ->withHeader('x-goog-api-key', $this->apiKey)
                ->post($url, $payload);

            if ($response->failed()) {
                $status = $response->status();
                Log::warning('Gemini API request failed.', ['status' => $status]);

                if ($status === 429) {
                    throw new RuntimeException('Kuota atau batas laju Gemini tercapai. Coba lagi nanti atau periksa kuota di Google AI Studio.', 429);
                }

                if ($response->serverError()) {
                    throw new RuntimeException('Layanan Gemini sedang sibuk atau mengalami gangguan sementara. Coba lagi beberapa saat.', 503);
                }

                throw new RuntimeException('Gemini belum dapat memproses permintaan. Periksa API key, model, dan akses proyek Google AI Studio.', 502);
            }

            $json = $response->json();
            $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $parsed = json_decode($rawText, true);

            if (! is_array($parsed)) {
                // If model wrapped JSON in markdown code fence
                $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawText));
                $parsed = json_decode($clean, true);
            }

            if (! is_array($parsed)) {
                throw new RuntimeException('Penyedia AI mengembalikan format respons yang tidak valid.', 502);
            }

            $usage = $json['usageMetadata'] ?? [];
            $promptTokens = $usage['promptTokenCount'] ?? 0;
            $completionTokens = $usage['candidatesTokenCount'] ?? 0;

            return [
                'content' => $parsed,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $promptTokens + $completionTokens,
                'model' => $model,
                'provider' => 'gemini',
            ];
        } catch (ConnectionException $e) {
            Log::warning('Gemini connection failed.');
            throw new RuntimeException('Koneksi Gemini belum berhasil. Periksa jaringan server lalu coba lagi.', 503);
        }
    }
}
