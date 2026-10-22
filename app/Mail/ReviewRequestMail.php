<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Invitation à laisser un avis, envoyée une fois la prestation passée. */
class ReviewRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public string $placeName,
        public string $when,
        public string $reviewUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Comment s\'est passé votre moment chez '.$this->placeName.' ? — '.SiteSetting::branding()['site_name']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.review-request', with: [
            'clientName' => $this->clientName,
            'placeName' => $this->placeName,
            'when' => $this->when,
            'reviewUrl' => $this->reviewUrl,
            'branding' => SiteSetting::branding(),
        ]);
    }
}
