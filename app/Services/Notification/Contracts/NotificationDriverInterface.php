<?php

namespace App\Services\Notification\Contracts;

use App\Services\Notification\NotificationPayload;

interface NotificationDriverInterface
{
    /**
     * Send notification via this specific driver.
     *
     * @return array{success: bool, recipient: string, status?: string, error?: string|null}
     */
    public function send(NotificationPayload $payload): array;
}
