<?php

namespace App\Console\Commands;

use App\Mail\ReviewRequestMail;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\TouristVisit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Demande d'avis automatique, une fois la prestation passée (séjour terminé, table ou activité
 * honorée, visite effectuée). Une seule demande par réservation, jamais si le client a déjà
 * laissé un avis sur l'établissement, et seulement dans les 7 jours qui suivent.
 */
class SendReviewRequests extends Command
{
    protected $signature = 'reviews:request';

    protected $description = 'Envoie les demandes d\'avis après les séjours, repas, activités et visites';

    private const WINDOW_DAYS = 7;

    public function handle(): int
    {
        $sent = 0;
        $today = now()->startOfDay();
        $from = $today->copy()->subDays(self::WINDOW_DAYS);

        // Séjours terminés (hier au plus tard), acompte payé, non annulés.
        Reservation::query()
            ->whereNull('review_requested_at')
            ->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->whereIn('payment_status', [Reservation::PAYMENT_DEPOSIT_PAID, Reservation::PAYMENT_UNPAID])
            ->where('status', Reservation::STATUS_CONFIRMED)
            ->whereDate('check_out', '<', $today)
            ->whereDate('check_out', '>=', $from)
            ->get()
            ->each(function (Reservation $r) use (&$sent) {
                $sent += $this->request($r, $r->provider_id, $r->email, $r->full_name, $r->accommodation_name, 'du '.$r->check_in->format('d/m').' au '.$r->check_out->format('d/m'), $r->user_id);
            });

        // Tables, activités, circuits, transports, offres groupées confirmés et passés.
        BookingRequest::query()
            ->whereNull('review_requested_at')
            ->where('status', BookingRequest::STATUS_CONFIRMED)
            ->whereDate('booking_date', '<', $today)
            ->whereDate('booking_date', '>=', $from)
            ->get()
            ->each(function (BookingRequest $b) use (&$sent) {
                $sent += $this->request($b, $b->provider_id, $b->email, $b->full_name, $b->bookable_name, 'le '.$b->booking_date->format('d/m'), $b->user_id);
            });

        // Visites de sites payées et passées.
        TouristVisit::query()
            ->whereNull('review_requested_at')
            ->where('status', TouristVisit::STATUS_PAID)
            ->where(fn ($q) => $q->whereDate('session_date', '<', $today)->whereDate('session_date', '>=', $from)
                ->orWhere(fn ($q2) => $q2->whereNull('session_date')->whereDate('desired_date', '<', $today)->whereDate('desired_date', '>=', $from)))
            ->get()
            ->each(function (TouristVisit $v) use (&$sent) {
                $date = $v->session_date ?? $v->desired_date;
                $sent += $this->request($v, $v->provider_id, $v->email, $v->full_name, $v->experience_name, $date ? 'le '.$date->format('d/m') : 'récemment', $v->user_id);
            });

        $this->info("Demandes d'avis envoyées : {$sent}");

        return self::SUCCESS;
    }

    /** Envoie la demande (au plus une fois) et retourne 1 si un e-mail est parti. */
    private function request($record, ?int $providerId, ?string $email, ?string $name, ?string $place, string $when, ?int $userId): int
    {
        // Marquée dans tous les cas : on ne reconsidère jamais deux fois la même réservation.
        $record->forceFill(['review_requested_at' => now()])->save();

        $provider = $providerId ? Provider::find($providerId) : null;
        if (! $provider || ! $provider->slug || ! $email) {
            return 0;
        }

        $alreadyReviewed = Review::where('provider_id', $provider->id)
            ->when($userId, fn ($q) => $q->where('user_id', $userId), fn ($q) => $q->whereRaw('0 = 1'))
            ->exists();
        if ($alreadyReviewed) {
            return 0;
        }

        $url = route('providers.show', $provider->slug).'?avis=1';
        Mail::to($email)->queue(new ReviewRequestMail((string) ($name ?: 'voyageur'), (string) ($place ?: $provider->name), $when, $url));

        return 1;
    }
}
