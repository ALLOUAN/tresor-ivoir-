<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReservationReceivedProviderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle réservation confirmée — '.$this->reservation->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-received-provider',
            with: [
                'reservation' => $this->reservation,
                'branding' => SiteSetting::branding(),
            ],
        );
    }

    /**
     * Appelé par le worker de file d'attente si l'envoi échoue définitivement (après
     * épuisement des tentatives) — trace exploitable pour diagnostiquer un incident
     * d'envoi (cf. exigence « erreurs d'envoi journalisées » du cahier des charges).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Échec d\'envoi de l\'e-mail de nouvelle réservation au prestataire', [
            'reservation_id' => $this->reservation->id,
            'reservation_reference' => $this->reservation->reference,
            'provider_id' => $this->reservation->provider_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
