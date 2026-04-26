<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — ' . config('app.name'))
            ->greeting('Bonjour !')
            ->line('Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation du mot de passe de votre compte.')
            ->action('Réinitialiser le mot de passe', $url)
            ->line('Ce lien expirera dans **' . $expire . ' minutes**.')
            ->line('Si le bouton ne s\'ouvre pas, copiez-collez ce lien dans votre navigateur :')
            ->line($url)
            ->line('Si vous n\'avez pas demandé de réinitialisation, ignorez cet e-mail.')
            ->salutation('Cordialement, l\'équipe ' . config('app.name'));
    }
}
