<?php

namespace App\Services\Notification\Drivers;

use App\Models\DeviceToken;
use App\Services\Notification\Contracts\NotificationDriverInterface;
use App\Services\Notification\FirebaseAccessTokenProvider;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Http;
use Throwable;

class FcmNotificationDriver implements NotificationDriverInterface
{
    public function __construct(protected FirebaseAccessTokenProvider $tokenProvider) {}

    public function send(NotificationPayload $payload): array
    {
        $tokens = DeviceToken::where('user_id', $payload->userId)
            ->where('school_id', $payload->schoolId)
            ->where('is_active', true)->get();
        $recipient = $tokens->count().' devices';

        if ($tokens->isEmpty()) {
            return ['success' => false, 'recipient' => 'none', 'error' => 'Tidak ada token perangkat aktif yang terdaftar.'];
        }

        if (config('services.notifications.simulate') && ! app()->isProduction()) {
            return ['success' => false, 'recipient' => $recipient, 'status' => 'simulated', 'error' => 'Simulasi: pesan tidak dikirim ke Firebase.'];
        }

        $projectId = (string) config('services.fcm.project_id');

        if ($projectId === '' || ! config('services.fcm.client_email') || ! config('services.fcm.private_key')) {
            return ['success' => false, 'recipient' => $recipient, 'status' => 'configuration_error', 'error' => 'Konfigurasi Firebase HTTP v1 belum lengkap.'];
        }

        try {
            $accessToken = $this->tokenProvider->accessToken();
            $accepted = 0;
            $data = array_map(fn (mixed $value): string => is_string($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR), $payload->data);
            $data['event_key'] = $payload->eventKey;

            foreach ($tokens as $deviceToken) {
                $response = Http::withToken($accessToken)->connectTimeout(3)->timeout(10)
                    ->post('https://fcm.googleapis.com/v1/projects/'.rawurlencode($projectId).'/messages:send', [
                        'message' => [
                            'token' => $deviceToken->token,
                            'notification' => ['title' => $payload->title, 'body' => $payload->body],
                            'data' => $data,
                        ],
                    ]);

                if ($response->successful() && is_string($response->json('name'))) {
                    $accepted++;
                } elseif (collect($response->json('error.details', []))->contains('errorCode', 'UNREGISTERED')) {
                    $deviceToken->update(['is_active' => false]);
                }
            }

            return [
                'success' => $accepted === $tokens->count(),
                'recipient' => $recipient,
                'status' => $accepted === $tokens->count() ? 'accepted' : 'failed',
                'error' => $accepted === $tokens->count() ? null : "Firebase menerima {$accepted} dari {$tokens->count()} pesan.",
            ];
        } catch (Throwable $exception) {
            report($exception);

            return ['success' => false, 'recipient' => $recipient, 'error' => 'Pengiriman ke Firebase gagal.'];
        }
    }
}
