<?php

namespace App\Services\Notification;

use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\Notification\Drivers\FcmNotificationDriver;
use App\Services\Notification\Drivers\MailNotificationDriver;
use App\Services\Notification\Drivers\WhatsAppNotificationDriver;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    public function __construct(
        protected FcmNotificationDriver $fcmDriver,
        protected WhatsAppNotificationDriver $whatsappDriver,
        protected MailNotificationDriver $mailDriver
    ) {}

    /**
     * Send notification payload across permitted channels.
     *
     * @return array<string, array{success: bool, recipient: string, error?: string|null}>
     */
    public function send(NotificationPayload $payload): array
    {
        $results = [];

        // 1. Deduplication Check (throttle identical events for the same user within 60s)
        $dedupKey = sprintf('notif_dedup:%s:%s:%s', $payload->userId, $payload->eventKey, md5($payload->title.$payload->body));
        if (Cache::has($dedupKey) && ! $payload->isUrgent) {
            NotificationLog::create([
                'school_id' => $payload->schoolId,
                'user_id' => $payload->userId,
                'event_key' => $payload->eventKey,
                'channel' => 'all',
                'recipient' => 'throttled',
                'title' => $payload->title,
                'body_preview' => mb_substr($payload->body, 0, 500),
                'status' => 'deduplicated',
                'error_message' => 'Notifikasi identik ditolak karena throttle deduplikasi (60s).',
            ]);

            return ['deduplicated' => ['success' => false, 'recipient' => 'throttled', 'error' => 'Deduplicated']];
        }
        Cache::put($dedupKey, true, 60);

        // 2. Fetch User Notification Preferences
        $pref = NotificationPreference::where('user_id', $payload->userId)->first();
        $isQuiet = $pref ? $pref->isInQuietHours() : false;

        // If in quiet hours and not urgent, suppress noisy channels
        if ($isQuiet && ! $payload->isUrgent) {
            NotificationLog::create([
                'school_id' => $payload->schoolId,
                'user_id' => $payload->userId,
                'event_key' => $payload->eventKey,
                'channel' => 'all',
                'recipient' => (string) $payload->userId,
                'title' => $payload->title,
                'body_preview' => mb_substr($payload->body, 0, 500),
                'status' => 'suppressed_quiet_hours',
                'error_message' => 'Pengiriman diredam selama jam tenang (quiet hours).',
            ]);

            return ['quiet_hours' => ['success' => false, 'recipient' => (string) $payload->userId, 'error' => 'Suppressed by quiet hours']];
        }

        // 3. Channel: Push (FCM)
        $shouldPush = $pref ? $pref->channel_push : true;
        if ($shouldPush) {
            $fcmRes = $this->fcmDriver->send($payload);
            $results['push'] = $fcmRes;

            NotificationLog::create([
                'school_id' => $payload->schoolId,
                'user_id' => $payload->userId,
                'event_key' => $payload->eventKey,
                'channel' => 'push',
                'recipient' => $fcmRes['recipient'],
                'title' => $payload->title,
                'body_preview' => mb_substr($payload->body, 0, 500),
                'status' => $fcmRes['success'] ? 'sent' : 'failed',
                'error_message' => $fcmRes['error'] ?? null,
                'sent_at' => $fcmRes['success'] ? now() : null,
            ]);
        }

        // 4. Channel: WhatsApp
        $shouldWa = $pref ? $pref->channel_whatsapp : false;
        if ($shouldWa) {
            $waRes = $this->whatsappDriver->send($payload);
            $results['whatsapp'] = $waRes;

            NotificationLog::create([
                'school_id' => $payload->schoolId,
                'user_id' => $payload->userId,
                'event_key' => $payload->eventKey,
                'channel' => 'whatsapp',
                'recipient' => $waRes['recipient'],
                'title' => $payload->title,
                'body_preview' => mb_substr($payload->body, 0, 500),
                'status' => $waRes['success'] ? 'sent' : 'failed',
                'error_message' => $waRes['error'] ?? null,
                'sent_at' => $waRes['success'] ? now() : null,
            ]);
        }

        // 5. Channel: Email
        $shouldEmail = $pref ? $pref->channel_email : false;
        if ($shouldEmail) {
            $mailRes = $this->mailDriver->send($payload);
            $results['email'] = $mailRes;

            NotificationLog::create([
                'school_id' => $payload->schoolId,
                'user_id' => $payload->userId,
                'event_key' => $payload->eventKey,
                'channel' => 'email',
                'recipient' => $mailRes['recipient'],
                'title' => $payload->title,
                'body_preview' => mb_substr($payload->body, 0, 500),
                'status' => $mailRes['success'] ? 'sent' : 'failed',
                'error_message' => $mailRes['error'] ?? null,
                'sent_at' => $mailRes['success'] ? now() : null,
            ]);
        }

        return $results;
    }

    /**
     * Dispatch notification for attendance status update (Generic lock-screen text per Rule 6)
     */
    public function notifyAttendance(User $user, string $studentName, string $status, ?string $time = null): array
    {
        $statusLabels = [
            'present' => 'Hadir',
            'sick' => 'Sakit',
            'permit' => 'Izin',
            'absent' => 'Alpa',
        ];

        $label = $statusLabels[$status] ?? ucfirst($status);
        $payload = new NotificationPayload(
            userId: $user->id,
            schoolId: $user->school_id,
            eventKey: 'attendance.recorded',
            title: 'Pembaruan Presensi Siswa',
            body: sprintf('Pembaruan presensi %s: Status %s (%s). Buka aplikasi untuk melihat detail.', $studentName, $label, $time ?: now()->format('H:i')),
            data: [
                'type' => 'attendance',
                'student_name' => $studentName,
                'status' => $status,
            ],
            isUrgent: false
        );

        return $this->send($payload);
    }

    /**
     * Dispatch notification for invoice issuance or payment confirmation (Generic text per Rule 6)
     */
    public function notifyInvoice(User $user, string $invoiceTitle, string $dueDate): array
    {
        $payload = new NotificationPayload(
            userId: $user->id,
            schoolId: $user->school_id,
            eventKey: 'finance.invoice_issued',
            title: 'Pemberitahuan Tagihan Administrasi',
            body: sprintf('Tagihan %s telah diterbitkan dengan jatuh tempo %s. Buka aplikasi untuk rincian pembayaran.', $invoiceTitle, $dueDate),
            data: [
                'type' => 'finance_invoice',
            ],
            isUrgent: false
        );

        return $this->send($payload);
    }

    /**
     * Dispatch notification for chat message (No sensitive text per Rule 6)
     */
    public function notifyChatMessage(User $recipient, string $senderName): array
    {
        $payload = new NotificationPayload(
            userId: $recipient->id,
            schoolId: $recipient->school_id,
            eventKey: 'chat.message_received',
            title: 'Pesan Baru Diterima',
            body: sprintf('Anda menerima pesan baru dari %s. Buka aplikasi Eduvia untuk membalas.', $senderName),
            data: [
                'type' => 'chat_message',
            ],
            isUrgent: false
        );

        return $this->send($payload);
    }
}
