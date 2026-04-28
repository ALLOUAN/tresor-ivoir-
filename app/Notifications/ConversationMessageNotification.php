<?php

namespace App\Notifications;

use App\Models\ProviderConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConversationMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ProviderConversation $conversation,
        private readonly string $messagePreview,
        private readonly string $url,
        private readonly string $title,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->messagePreview,
            'url' => $this->url,
            'conversation_id' => $this->conversation->id,
            'priority' => $this->conversation->priority,
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line('Vous avez reçu un nouveau message dans la messagerie interne.')
            ->line('Sujet: '.($this->conversation->subject ?: 'Conversation sans sujet'))
            ->line('Aperçu: '.$this->messagePreview)
            ->action('Ouvrir la conversation', url($this->url));
    }
}

