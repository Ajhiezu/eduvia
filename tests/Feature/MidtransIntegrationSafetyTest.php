<?php

namespace Tests\Feature;

use App\Services\Payment\MidtransPaymentDriver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class MidtransIntegrationSafetyTest extends TestCase
{
    public function test_production_rejects_mock_credentials_without_creating_payment_instructions(): void
    {
        $this->app->instance('env', 'production');
        Http::preventStrayRequests();

        try {
            (new MidtransPaymentDriver('SB-Mid-server-eduvia-mock-key', false))->charge($this->chargeParams());
            $this->fail('Production must reject simulated gateway credentials.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Konfigurasi Midtrans produksi belum valid.', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_sandbox_connection_failure_is_not_replaced_with_fabricated_instructions(): void
    {
        $this->app->instance('env', 'staging');
        Http::preventStrayRequests();
        Http::fake(['https://api.sandbox.midtrans.com/v2/charge' => Http::failedConnection()]);

        try {
            (new MidtransPaymentDriver('sandbox-test-key', false))->charge($this->chargeParams());
            $this->fail('A connection failure must be propagated.');
        } catch (ConnectionException) {
            Http::assertSentCount(1);
        }
    }

    public function test_missing_webhook_credentials_cannot_validate_a_forged_empty_key_signature(): void
    {
        $driver = new MidtransPaymentDriver('', false);

        $this->assertFalse($driver->verifySignature('order-1', '200', '500000', hash('sha512', 'order-1'.'200'.'500000')));
    }

    /**
     * @return array{order_id: string, gross_amount: int, payment_method: string}
     */
    private function chargeParams(): array
    {
        return ['order_id' => 'test-order', 'gross_amount' => 500000, 'payment_method' => 'bca_va'];
    }
}
