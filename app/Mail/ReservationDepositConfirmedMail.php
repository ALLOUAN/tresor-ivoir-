<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\SiteSetting;
use App\Services\ReservationReceiptService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReservationDepositConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre acompte est confirmé — '.SiteSetting::branding()['site_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-deposit-confirmed',
            with: [
                'reservation' => $this->reservation,
                'branding' => SiteSetting::branding(),
                'confirmationUrl' => $this->reservation->confirmationUrl(),
                'receiptUrl' => $this->reservation->receiptUrl(),
                'guestRegistrationUrl' => $this->reservation->guestRegistrationUrl(),
            ],
        );
    }

    /** @return array<Attachment> */
    public function attachments(): array
    {
        $receipts = app(ReservationReceiptService::class);

        return [
            Attachment::fromData(
                fn () => $receipts->generatePdf($this->reservation)->output(),
                $receipts->pdfFilename($this->reservation)
            )->withMime('application/pdf'),
        ];
    }

    /**
     * Appelé par le worker de file d'attente si l'envoi échoue définitivement (après
     * épuisement des tentatives) — trace exploitable pour diagnostiquer un incident
     * d'envoi (cf. exigence « erreurs d'envoi journalisées » du cahier des charges).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Échec d\'envoi de l\'e-mail de confirmation d\'acompte au client', [
            'reservation_id' => $this->reservation->id,
            'reservation_reference' => $this->reservation->reference,
            'email' => $this->reservation->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
