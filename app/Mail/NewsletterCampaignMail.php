<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class NewsletterCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->subscriber->locale === 'en'
            && filled($this->campaign->subject_en)
            ? $this->campaign->subject_en
            : $this->campaign->subject_fr;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $body = $this->subscriber->locale === 'en'
            && filled($this->campaign->content_en)
            ? $this->campaign->content_en
            : $this->campaign->content_fr;

        $unsubscribeUrl = URL::temporarySignedRoute(
            'newsletter.unsubscribe',
            now()->addYear(),
            ['subscriber' => $this->subscriber->id]
        );

        return new Content(
            view: 'emails.newsletter-campaign',
            with: [
                'bodyHtml' => $body,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
