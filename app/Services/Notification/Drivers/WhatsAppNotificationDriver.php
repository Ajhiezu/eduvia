<?php

namespace App\Services\Notification\Drivers;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\Notification\Contracts\NotificationDriverInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Http;

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
        if (! $pref?->whatsapp_opt_in) {
            return [
                'success' => false,
                'recipient' => $phoneNumber,
                'error' => 'Pengguna belum melakukan persetujuan (opt-in) notifikasi WhatsApp.',
            ];
        }

        $bspEndpoint = config('services.whatsapp.endpoint');
        $bspToken = config('services.whatsapp.token');

        // Sandbox / Local fallback logging when live WhatsApp credentials are not configured
        if (config('services.notifications.simulate') && ! app()->isProduction()) {
            return [
                'success' => false,
                'recipient' => $phoneNumber,
                'status' => 'simulated',
                'error' => 'Simulasi: pesan WhatsApp tidak dikirim.',
            ];
        }

        if (empty($bspEndpoint) || empty($bspToken)) {
            return ['success' => false, 'recipient' => $phoneNumber, 'status' => 'configuration_error', 'error' => 'Konfigurasi WhatsApp belum lengkap.'];
        }

        try {
            $response = Http::withToken($bspToken)->connectTimeout(3)->timeout(10)->post($bspEndpoint, [
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

            $accepted = $response->successful() && is_string($response->json('messages.0.id'));

            return [
                'success' => $accepted,
                'recipient' => $phoneNumber,
                'status' => $accepted ? 'accepted' : 'failed',
                'error' => $accepted ? null : 'Penyedia WhatsApp tidak menerima pesan.',
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
