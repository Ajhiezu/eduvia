<?php

namespace App\Services\Notification\Drivers;

use App\Models\User;
use App\Services\Notification\Contracts\NotificationDriverInterface;
use App\Services\Notification\NotificationPayload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailNotificationDriver implements NotificationDriverInterface
{
    public function send(NotificationPayload $payload): array
    {
        $user = User::find($payload->userId);

        if (! $user || empty($user->email)) {
            return [
                'success' => false,
                'recipient' => 'none',
                'error' => 'Email pengguna tidak ditemukan.',
            ];
        }

        if (app()->environment('testing', 'local')) {
            Log::info('[EMAIL NOTIFICATION MOCK SENT]', [
                'recipient' => $user->email,
                'title' => $payload->title,
                'body' => $payload->body,
            ]);

            return [
                'success' => true,
                'recipient' => $user->email,
            ];
        }

        try {
            Mail::raw($payload->body, function ($message) use ($user, $payload) {
                $message->to($user->email)
                    ->subject($payload->title);
            });

            return [
                'success' => true,
                'recipient' => $user->email,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'recipient' => $user->email,
                'error' => $e->getMessage(),
            ];
        }
    }
}
