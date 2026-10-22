<?php

namespace App\Mail;

use App\Models\GiftCard;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Bon cadeau : envoi au bénéficiaire (code + QR) et confirmation à l'acheteur. */
class GiftCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public GiftCard $card, public string $audience) {}

    public function envelope(): Envelope
    {
        $place = $this->card->provider?->name ?? '';

        return new Envelope(subject: $this->audience === 'recipient'
            ? $this->card->buyer_name.' vous offre un bon cadeau chez '.$place.' 🎁'
            : 'Votre bon cadeau pour '.$this->card->recipient_name.' a été envoyé — '.SiteSetting::branding()['site_name']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.gift-card', with: [
            'card' => $this->card,
            'audience' => $this->audience,
            'branding' => SiteSetting::branding(),
            'qr' => \App\Support\Qr::dataUri($this->card->code, 220),
        ]);
    }
}
