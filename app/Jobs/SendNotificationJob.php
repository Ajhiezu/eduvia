<?php

namespace App\Jobs;

use App\Jobs\Middleware\SetTenantContext;
use App\Services\Notification\NotificationPayload;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public NotificationPayload $payload
    ) {}

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new SetTenantContext($this->payload->schoolId),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $notificationService->send($this->payload);
    }
}
