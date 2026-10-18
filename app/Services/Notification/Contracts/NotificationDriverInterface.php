<?php

namespace App\Services\Notification\Contracts;

use App\Services\Notification\NotificationPayload;

interface NotificationDriverInterface
{
    /**
     * Send notification via this specific driver.
     *
     * @return array{success: bool, recipient: string, error?: string}
     */
    public function send(NotificationPayload $payload): array;
}
