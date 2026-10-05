<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationResumeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Terminez votre réservation — '.SiteSetting::branding()['site_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-resume',
            with: [
                'reservation' => $this->reservation,
                'branding' => SiteSetting::branding(),
                'guestRegistrationUrl' => $this->reservation->guestRegistrationUrl(),
            ],
        );
    }
}
