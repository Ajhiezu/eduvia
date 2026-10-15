<?php

namespace App\Notifications;

use App\Models\ConversationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewConversationMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public ConversationMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;
        $sender = $this->message->sender;

        return [
            'type' => 'message',
            'conversation_id' => $conversation->id,
            'message_id' => $this->message->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'subject' => $conversation->subject,
            'message' => 'Pesan baru dari '.$sender->name.': '.str($this->message->body)->limit(60),
            'url' => route('conversations.show', $conversation),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
