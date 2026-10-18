<?php

namespace App\Services\Notification\Drivers;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\Notification\Contracts\NotificationDriverInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationDriver implements NotificationDriverInterface
{
    public function send(NotificationPayload $payload): array
    {
        $user = User::find($payload->userId);
        $pref = NotificationPreference::where('user_id', $payload->userId)->first();

        $phoneNumber = $pref?->whatsapp_number ?: $user?->phone;

        if (empty($phoneNumber)) {
            return [
                'success' => false,
                'recipient' => 'none',
                'error' => 'Nomor WhatsApp tidak terdaftar.',
            ];
        }

        // Check if user has opted in (UU PDP & Meta Business Policy)
        if ($pref && ! $pref->whatsapp_opt_in && ! $payload->isUrgent) {
            return [
                'success' => false,
                'recipient' => $phoneNumber,
                'error' => 'Pengguna belum melakukan persetujuan (opt-in) notifikasi WhatsApp.',
            ];
        }

        $bspEndpoint = config('services.whatsapp.endpoint');
        $bspToken = config('services.whatsapp.token');

        // Sandbox / Local fallback logging when live WhatsApp credentials are not configured
        if (empty($bspEndpoint) || empty($bspToken) || app()->environment('testing', 'local')) {
            Log::info('[WHATSAPP BSP MOCK SENT]', [
                'recipient' => $phoneNumber,
                'title' => $payload->title,
                'body' => $payload->body,
            ]);

            return [
                'success' => true,
                'recipient' => $phoneNumber,
            ];
        }

        try {
            $response = Http::withToken($bspToken)->post($bspEndpoint, [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'template',
                'template' => [
                    'name' => 'eduvia_general_notice',
                    'language' => ['code' => 'id'],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $payload->title],
                                ['type' => 'text', 'text' => $payload->body],
                            ],
                        ],
                    ],
                ],
            ]);

            return [
                'success' => $response->successful(),
                'recipient' => $phoneNumber,
                'error' => $response->successful() ? null : $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'recipient' => $phoneNumber,
                'error' => $e->getMessage(),
            ];
        }
    }
}
