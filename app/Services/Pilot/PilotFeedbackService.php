<?php

namespace App\Services\Pilot;

use App\Models\AuditLog;
use App\Models\Pilot\PilotFeedback;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PilotFeedbackService
{
    /**
     * Submit in-app feedback from mobile or web client.
     *
     * @param  array{
     *     category: string,
     *     severity?: string|null,
     *     title: string,
     *     description: string,
     *     app_version?: string|null,
     *     device_info?: array<string, mixed>|null,
     * }  $data
     */
    public function submitFeedback(
        array $data,
        User $reporter,
        ?UploadedFile $screenshot = null
    ): PilotFeedback {
        $screenshotPath = null;
        if ($screenshot) {
            $ext = $screenshot->getClientOriginalExtension();
            $filename = 'fb_'.Str::random(16).'.'.$ext;
            $screenshotPath = $screenshot->storeAs('pilot-feedbacks/'.$reporter->school_id, $filename, 'local');
        }

        // Clean device info to guarantee ZERO PII
        $cleanDeviceInfo = [];
        if (! empty($data['device_info']) && is_array($data['device_info'])) {
            $allowedKeys = ['os', 'os_version', 'device_model', 'screen_width', 'screen_height', 'network_type'];
            foreach ($allowedKeys as $key) {
                if (isset($data['device_info'][$key])) {
                    $cleanDeviceInfo[$key] = substr((string) $data['device_info'][$key], 0, 50);
                }
            }
        }

        $severity = $data['severity'] ?? 'p3';
        if ($data['category'] === 'bug' && ! isset($data['severity'])) {
            $severity = 'p2';
        }

        $feedback = PilotFeedback::create([
            'school_id' => $reporter->school_id,
            'user_id' => $reporter->id,
            'role' => $reporter->role instanceof \BackedEnum ? $reporter->role->value : (string) $reporter->role,
            'category' => $data['category'],
            'severity' => in_array($severity, ['p0', 'p1', 'p2', 'p3'], true) ? $severity : 'p3',
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'screenshot_path' => $screenshotPath,
            'app_version' => $data['app_version'] ?? '1.0.0',
            'device_info' => $cleanDeviceInfo,
            'status' => 'open',
        ]);

        AuditLog::record(
            action: 'pilot_feedback_submitted',
            auditable: $feedback,
            newValues: ['category' => $feedback->category, 'severity' => $feedback->severity],
            description: "Umpan balik pilot diajukan: {$feedback->title} ({$feedback->severity})",
            schoolId: $reporter->school_id
        );

        return $feedback;
    }

    /**
     * Triage feedback, update severity/status, and optionally send template response.
     *
     * @param  array{
     *     status: string,
     *     severity?: string|null,
     *     triage_notes?: string|null,
     *     response_template?: string|null,
     *     assigned_to?: int|null
     * }  $data
     */
    public function triageFeedback(PilotFeedback $feedback, array $data, User $operator): PilotFeedback
    {
        $updates = [
            'status' => $data['status'],
        ];

        if (! empty($data['severity'])) {
            $updates['severity'] = $data['severity'];
        }

        if (isset($data['triage_notes'])) {
            $updates['triage_notes'] = $data['triage_notes'];
        }

        if (isset($data['assigned_to'])) {
            $updates['assigned_to'] = $data['assigned_to'];
        }

        if (! empty($data['response_template'])) {
            $updates['response_template_sent'] = $data['response_template'];
        }

        if ($data['status'] === 'resolved') {
            $updates['resolved_at'] = now();
        }

        $feedback->update($updates);

        AuditLog::record(
            action: 'pilot_feedback_triaged',
            auditable: $feedback,
            newValues: $updates,
            description: "Triase umpan balik ID {$feedback->id}: status {$feedback->status}, tingkat {$feedback->severity}",
            schoolId: $feedback->school_id
        );

        return $feedback;
    }
}
