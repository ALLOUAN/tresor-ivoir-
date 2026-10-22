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

/**
 * Prévient le client que l'établissement a annulé sa réservation. Si un acompte avait été payé,
 * le message annonce le remboursement : les fonds sont gelés côté prestataire jusqu'à ce que
 * l'administration l'enregistre (WalletService::recordManualRefund).
 */
class ReservationCancelledByProviderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre réservation a été annulée par l\'établissement — '.SiteSetting::branding()['site_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-cancelled-by-provider',
            with: [
                'reservation' => $this->reservation,
                'branding' => SiteSetting::branding(),
                'depositPaid' => $this->reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID,
            ],
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Échec d\'envoi de l\'e-mail d\'annulation par l\'établissement', [
            'reservation_id' => $this->reservation->id,
            'reservation_reference' => $this->reservation->reference,
            'email' => $this->reservation->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
