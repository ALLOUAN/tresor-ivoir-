<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly int $ttlMinutes,
        private readonly int $amountXof,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Code de confirmation de votre retrait')
            ->line('Une demande de retrait de '.number_format($this->amountXof, 0, ',', ' ').' XOF nécessite une vérification complémentaire.')
            ->line('Votre code de confirmation : '.$this->code)
            ->line('Ce code est valable '.$this->ttlMinutes.' minutes.')
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, ne communiquez ce code à personne et contactez notre support immédiatement.');
    }
}
