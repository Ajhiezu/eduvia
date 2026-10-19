<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransPaymentDriver implements PaymentGatewayInterface
{
    protected string $serverKey;

    protected bool $isProduction;

    protected string $baseUrl;

    public function __construct(?string $serverKey = null, ?bool $isProduction = null)
    {
        $this->serverKey = $serverKey ?? (string) config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-eduvia-mock-key'));
        $this->isProduction = $isProduction ?? (bool) config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $this->baseUrl = $this->isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Charge transaction via Midtrans Core API or mock fallback.
     */
    public function charge(array $params): array
    {
        $orderId = $params['order_id'];
        $amount = (int) $params['gross_amount'];
        $method = $params['payment_method'];

        // If in mock/test environment without real credentials, return deterministic sandbox mock response
        if (app()->environment('testing') || str_contains($this->serverKey, 'mock')) {
            return $this->generateMockChargeResponse($orderId, $amount, $method);
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $params['customer_details']['name'] ?? 'Wali Murid',
                'email' => $params['customer_details']['email'] ?? 'parent@eduvia.sch.id',
                'phone' => $params['customer_details']['phone'] ?? '081234567890',
            ],
        ];

        switch ($method) {
            case 'bca_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bca'];
                break;
            case 'bni_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bni'];
                break;
            case 'bri_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bri'];
                break;
            case 'permata_va':
                $payload['payment_type'] = 'permata';
                break;
            case 'mandiri_bill':
                $payload['payment_type'] = 'echannel';
                $payload['echannel'] = [
                    'bill_info1' => 'Pembayaran Eduvia',
                    'bill_info2' => 'SPP Sekolah',
                ];
                break;
            case 'qris':
                $payload['payment_type'] = 'qris';
                $payload['qris'] = ['acquirer' => 'gopay'];
                break;
            default:
                throw new \InvalidArgumentException("Metode pembayaran gateway tidak didukung: {$method}");
        }

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->acceptJson()
                ->post("{$this->baseUrl}/charge", $payload);

            if ($response->failed()) {
                Log::error('Midtrans charge failed', ['response' => $response->json(), 'payload' => $payload]);
                throw new \RuntimeException('Gagal menghubungkan ke payment gateway Midtrans: '.($response->json('status_message') ?? 'Error'));
            }

            $resData = $response->json();

            return $this->parseChargeResponse($orderId, $method, $resData);
        } catch (\Throwable $e) {
            Log::warning('Midtrans network exception, fallback to sandbox response in non-prod', ['error' => $e->getMessage()]);
            if (! $this->isProduction) {
                return $this->generateMockChargeResponse($orderId, $amount, $method);
            }
            throw $e;
        }
    }

    /**
     * Parse Midtrans response into standard structure.
     *
     * @param  array<string, mixed>  $resData
     * @return array{
     *     order_id: string,
     *     payment_method: string,
     *     va_number?: string|null,
     *     bill_key?: string|null,
     *     biller_code?: string|null,
     *     qr_code_url?: string|null,
     *     expiry_time: string,
     *     raw_response: array<string, mixed>
     * }
     */
    protected function parseChargeResponse(string $orderId, string $method, array $resData): array
    {
        $vaNumber = null;
        $billKey = null;
        $billerCode = null;
        $qrCodeUrl = null;

        if (isset($resData['va_numbers'][0]['va_number'])) {
            $vaNumber = $resData['va_numbers'][0]['va_number'];
        } elseif (isset($resData['permata_va_number'])) {
            $vaNumber = $resData['permata_va_number'];
        }

        if (isset($resData['bill_key'])) {
            $billKey = $resData['bill_key'];
            $billerCode = $resData['biller_code'] ?? '70012';
        }

        if (isset($resData['actions'])) {
            foreach ($resData['actions'] as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    $qrCodeUrl = $action['url'] ?? null;
                    break;
                }
            }
        }

        $expiryTime = $resData['expiry_time'] ?? now()->addHours(24)->toIso8601String();

        return [
            'order_id' => $orderId,
            'payment_method' => $method,
            'va_number' => $vaNumber,
            'bill_key' => $billKey,
            'biller_code' => $billerCode,
            'qr_code_url' => $qrCodeUrl,
            'expiry_time' => $expiryTime,
            'raw_response' => $resData,
        ];
    }

    /**
     * Generate synthetic sandbox response for local development and testing.
     *
     * @return array{
     *     order_id: string,
     *     payment_method: string,
     *     va_number?: string|null,
     *     bill_key?: string|null,
     *     biller_code?: string|null,
     *     qr_code_url?: string|null,
     *     expiry_time: string,
     *     raw_response: array<string, mixed>
     * }
     */
    protected function generateMockChargeResponse(string $orderId, int $amount, string $method): array
    {
        $hashSuffix = substr(abs(crc32($orderId)), 0, 8);
        $vaNumber = null;
        $billKey = null;
        $billerCode = null;
        $qrCodeUrl = null;

        switch ($method) {
            case 'bca_va':
                $vaNumber = '82138'.$hashSuffix;
                break;
            case 'bni_va':
                $vaNumber = '98801'.$hashSuffix;
                break;
            case 'bri_va':
                $vaNumber = '12345'.$hashSuffix;
                break;
            case 'permata_va':
                $vaNumber = '85220'.$hashSuffix;
                break;
            case 'mandiri_bill':
                $billKey = '990'.$hashSuffix;
                $billerCode = '70012';
                break;
            case 'qris':
                $qrCodeUrl = "https://api.sandbox.midtrans.com/v2/qris/{$orderId}/qr-code";
                break;
        }

        $expiry = now()->addHours(24)->toDateTimeString();

        return [
            'order_id' => $orderId,
            'payment_method' => $method,
            'va_number' => $vaNumber,
            'bill_key' => $billKey,
            'biller_code' => $billerCode,
            'qr_code_url' => $qrCodeUrl,
            'expiry_time' => $expiry,
            'raw_response' => [
                'status_code' => '201',
                'status_message' => 'Success, Mock transaction created',
                'transaction_id' => 'mock-tx-'.$orderId,
                'order_id' => $orderId,
                'gross_amount' => (string) $amount,
                'currency' => 'IDR',
                'payment_type' => $method,
                'transaction_time' => now()->toDateTimeString(),
                'transaction_status' => 'pending',
                'va_number' => $vaNumber,
            ],
        ];
    }

    /**
     * Check transaction status directly from gateway API.
     */
    public function getStatus(string $orderId): array
    {
        if (app()->environment('testing') || str_contains($this->serverKey, 'mock')) {
            return [
                'status_code' => '200',
                'transaction_status' => 'settlement',
                'order_id' => $orderId,
            ];
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->get("{$this->baseUrl}/{$orderId}/status");

        return $response->json() ?? [];
    }

    /**
     * Verify incoming webhook SHA512 signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        // Format of Midtrans signature: SHA512(order_id + status_code + gross_amount + ServerKey)
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
