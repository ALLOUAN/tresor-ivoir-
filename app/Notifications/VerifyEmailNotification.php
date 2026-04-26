<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Vérifiez votre adresse e-mail — ' . config('app.name'))
            ->greeting('Bonjour !')
            ->line('Cliquez sur le bouton ci-dessous pour vérifier votre adresse e-mail.')
            ->action('Vérifier mon adresse e-mail', $url)
            ->line('Si le bouton ne s\'ouvre pas, copiez-collez ce lien dans votre navigateur :')
            ->line($url)
            ->line('Si vous n\'avez pas créé de compte, ignorez cet e-mail.')
            ->salutation('Cordialement, l\'équipe ' . config('app.name'));
    }
}
