<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly int $ttlMinutes
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre code de vérification — '.config('app.name'))
            ->greeting('Bonjour !')
            ->line('Voici votre code de vérification e-mail :')
            ->line($this->code)
            ->line('Ce code est valable pendant '.$this->ttlMinutes.' minutes.')
            ->line('Saisissez ce code dans l\'écran de vérification pour finaliser votre compte.')
            ->salutation('Cordialement, l\'équipe '.config('app.name'));
    }
}
