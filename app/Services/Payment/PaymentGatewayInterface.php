<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create bank virtual account or QRIS transaction via gateway.
     *
     * @param  array{
     *     order_id: string,
     *     gross_amount: int,
     *     payment_method: string,
     *     customer_details: array{name: string, email: string, phone?: string},
     *     item_details?: array<int, array{id: string, price: int, quantity: int, name: string}>
     * }  $params
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
    public function charge(array $params): array;

    /**
     * Check transaction status directly from gateway API.
     *
     * @return array<string, mixed>
     */
    public function getStatus(string $orderId): array;

    /**
     * Verify incoming webhook signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool;
}
