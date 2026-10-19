<?php

namespace App\Services\Notification\Drivers;

use App\Models\DeviceToken;
use App\Services\Notification\Contracts\NotificationDriverInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationDriver implements NotificationDriverInterface
{
    public function send(NotificationPayload $payload): array
    {
        $tokens = DeviceToken::where('user_id', $payload->userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return [
                'success' => false,
                'recipient' => 'none',
                'error' => 'Tidak ada token perangkat aktif yang terdaftar.',
            ];
        }

        $fcmServerKey = config('services.fcm.key');

        // Sandbox / Local fallback logging when FCM key is not configured
        if (empty($fcmServerKey) || app()->environment('testing', 'local')) {
            Log::info('[FCM PUSH MOCK SENT]', [
                'user_id' => $payload->userId,
                'tokens_count' => count($tokens),
                'title' => $payload->title,
                'body' => $payload->body,
                'data' => $payload->data,
            ]);

            return [
                'success' => true,
                'recipient' => count($tokens).' devices',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key='.$fcmServerKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $payload->title,
                    'body' => $payload->body,
                    'sound' => 'default',
                ],
                'data' => array_merge($payload->data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'event_key' => $payload->eventKey,
                ]),
            ]);

            return [
                'success' => $response->successful(),
                'recipient' => count($tokens).' devices',
                'error' => $response->successful() ? null : $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'recipient' => count($tokens).' devices',
                'error' => $e->getMessage(),
            ];
        }
    }
}
