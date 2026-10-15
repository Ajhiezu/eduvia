<?php

namespace App\Notifications;

use App\Models\ParentStudentRelation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParentLinkVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(public ParentStudentRelation $relation) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $student = $this->relation->student;

        return [
            'type' => 'parent_link',
            'relation_id' => $this->relation->id,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'status' => $this->relation->status,
            'message' => 'Penautan akun anak atas nama '.$student->name.' telah '.$this->relation->statusLabel().'.',
            'url' => route('parent.dashboard'),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
