<?php

namespace App\Mail;

use App\Models\BookingRequest;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mails du cycle d'une demande de réservation (restaurant, loisir, circuit).
 * $event : received (à l'établissement), confirmed / declined (au client), cancelled (à l'établissement).
 */
class BookingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BookingRequest $booking,
        public string $event,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'received' => 'Nouvelle demande de réservation',
            'confirmed' => 'Votre réservation est confirmée',
            'declined' => 'Votre demande de réservation n\'a pas pu être acceptée',
            'cancelled' => 'Demande de réservation annulée par le client',
            default => 'Votre demande de réservation',
        };

        return new Envelope(subject: $subject.' — '.$this->booking->bookable_name.' — '.SiteSetting::branding()['site_name']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.booking-request', with: [
            'booking' => $this->booking,
            'event' => $this->event,
            'branding' => SiteSetting::branding(),
        ]);
    }
}
