<?php

namespace App\Mail;

use App\Models\SiteSetting;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringSoonMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre abonnement expire bientôt — '.SiteSetting::branding()['site_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expiring-soon',
            with: ['subscription' => $this->subscription],
        );
    }
}
