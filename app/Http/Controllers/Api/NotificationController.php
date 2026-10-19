<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Register or update mobile FCM device token.
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
        ]);

        $user = $request->user();

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $validated['token'],
            ],
            [
                'school_id' => $user->school_id,
                'platform' => $validated['platform'] ?? 'android',
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return $this->apiSuccess($deviceToken, 'Token perangkat berhasil didaftarkan.', 201);
    }

    /**
     * Remove / revoke mobile device token on logout.
     */
    public function removeDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $deleted = DeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();

        return $this->apiSuccess(['removed' => (bool) $deleted], 'Token perangkat berhasil dicabut.');
    }

    /**
     * Get user notification preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $preferences = NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'school_id' => $user->school_id,
                'channel_push' => true,
                'channel_whatsapp' => false,
                'channel_email' => false,
                'quiet_hours_enabled' => true,
                'quiet_hours_start' => '21:00',
                'quiet_hours_end' => '06:00',
                'whatsapp_opt_in' => false,
                'whatsapp_number' => $user->phone,
            ]
        );

        return $this->apiSuccess($preferences, 'Pengaturan notifikasi berhasil diambil.');
    }

    /**
     * Update user notification preferences (including quiet hours and WhatsApp opt-in).
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_push' => ['nullable', 'boolean'],
            'channel_whatsapp' => ['nullable', 'boolean'],
            'channel_email' => ['nullable', 'boolean'],
            'quiet_hours_enabled' => ['nullable', 'boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'whatsapp_opt_in' => ['nullable', 'boolean'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $request->user();

        $preferences = NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            ['school_id' => $user->school_id]
        );

        if (isset($validated['whatsapp_opt_in']) && $validated['whatsapp_opt_in'] && ! $preferences->whatsapp_opt_in) {
            $validated['whatsapp_opt_in_at'] = now();
        }

        $preferences->update($validated);

        return $this->apiSuccess($preferences, 'Pengaturan notifikasi berhasil disimpan.');
    }

    /**
     * Get recent notifications log for the authenticated user.
     */
    public function getLogs(Request $request): JsonResponse
    {
        $user = $request->user();

        $logs = NotificationLog::where('user_id', $user->id)
            ->whereIn('status', ['sent', 'suppressed_quiet_hours'])
            ->latest()
            ->paginate(15);

        return $this->apiPaginated($logs, 'Riwayat notifikasi berhasil diambil.');
    }
}
