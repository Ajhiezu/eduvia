<?php

namespace App\Notifications;

use App\Models\SchoolAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSchoolAnnouncementNotification extends Notification
{
    use Queueable;

    public function __construct(public SchoolAnnouncement $announcement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'announcement',
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'category' => $this->announcement->category,
            'category_label' => $this->announcement->categoryLabel(),
            'message' => 'Pengumuman baru: '.$this->announcement->title,
            'url' => route('announcements.show', $this->announcement),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
