<?php

namespace App\Notifications;

use App\Models\ConversationReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewConversationReportNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ConversationReport $report,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'title' => 'Nouveau signalement messagerie',
            'message' => ConversationReport::REASON_LABELS[$this->report->reason] ?? $this->report->reason,
            'url' => route('admin.conversation-reports.show', $this->report),
            'report_id' => $this->report->id,
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau signalement dans la messagerie client-prestataire')
            ->line('Un utilisateur a été signalé dans une conversation.')
            ->line('Motif: '.(ConversationReport::REASON_LABELS[$this->report->reason] ?? $this->report->reason))
            ->action('Examiner le signalement', route('admin.conversation-reports.show', $this->report));
    }
}
