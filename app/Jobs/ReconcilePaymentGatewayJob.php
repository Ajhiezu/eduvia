<?php

namespace App\Jobs;

use App\Services\Payment\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ReconcilePaymentGatewayJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(PaymentService $paymentService): void
    {
        Log::info('ReconcilePaymentGatewayJob started.');

        $result = $paymentService->reconcile();

        Log::info('ReconcilePaymentGatewayJob completed.', $result);
    }
}
