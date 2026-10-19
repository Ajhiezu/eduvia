<?php

namespace App\Services\Notification;

class NotificationPayload
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public int $userId,
        public ?int $schoolId,
        public string $eventKey,
        public string $title,
        public string $body,
        public array $data = [],
        public bool $isUrgent = false
    ) {}
}
