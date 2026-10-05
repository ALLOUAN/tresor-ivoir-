<?php

namespace App\Notifications;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutSuspendedAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PayoutRequest $payout,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'title' => 'Retrait suspendu — examen requis',
            'message' => $this->payout->holderName().' — '.number_format($this->payout->amount_xof, 0, ',', ' ').' XOF (score '.$this->payout->risk_score.')',
            'url' => route('admin.wallet.payouts', ['status' => PayoutRequest::STATUS_SUSPENDED]),
            'payout_request_id' => $this->payout->id,
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Retrait suspendu par le moteur de risque')
            ->line('Une demande de retrait a été automatiquement suspendue et nécessite votre examen.')
            ->line('Demandeur : '.$this->payout->holderName())
            ->line('Montant : '.number_format($this->payout->amount_xof, 0, ',', ' ').' XOF')
            ->line('Score de risque : '.$this->payout->risk_score.'/100')
            ->action('Examiner la demande', route('admin.wallet.payouts', ['status' => PayoutRequest::STATUS_SUSPENDED]));
    }
}
