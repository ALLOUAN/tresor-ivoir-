<?php

namespace App\Notifications;

use App\Models\SiteSetting;
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
        // Nom du site pris dans les réglages (et non APP_NAME, qui peut valoir « Laravel »).
        $branding = SiteSetting::branding();
        $site = $branding['site_name'] ?? config('app.name');

        return (new MailMessage)
            ->subject(__(':code est votre code de vérification — :site', ['code' => $this->code, 'site' => $site]))
            ->view('emails.verification-code', [
                'code' => $this->code,
                'minutes' => $this->ttlMinutes,
                'name' => $notifiable->first_name ?? null,
                'branding' => $branding,
                'verifyUrl' => route('verification.notice'),
            ]);
    }
}
