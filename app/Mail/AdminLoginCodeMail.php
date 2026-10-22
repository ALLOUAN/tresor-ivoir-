<?php

namespace App\Mail;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Code de connexion à usage unique pour l'administration (double authentification). */
class AdminLoginCodeMail extends Mailable
{
    public function __construct(
        public User $user,
        public string $code,
        public int $minutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre code de connexion : '.$this->code.' — '.SiteSetting::branding()['site_name']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-login-code', with: [
            'user' => $this->user,
            'code' => $this->code,
            'minutes' => $this->minutes,
            'branding' => SiteSetting::branding(),
        ]);
    }
}
