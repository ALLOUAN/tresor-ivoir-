<?php

namespace App\Services;

use App\Models\TourBooking;
use App\Models\TourDeparture;
use App\Models\TourPackage;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Support\Facades\DB;

/**
 * Règles des réservations de circuits : création sous verrou des places, annulation (barème du circuit),
 * départ garanti / annulé selon le minimum de voyageurs, relance et annulation pour solde impayé.
 */
class TourBookingService
{
    /** Jours de grâce après l'échéance du solde avant l'annulation automatique. */
    public const UNPAID_GRACE_DAYS = 2;

    public function __construct(private readonly TourBookingPaymentService $payments) {}

    /**
     * Crée la réservation (en attente de paiement) après vérification des places sous verrou.
     *
     * @param  array{adults: int, children: int, single_rooms: int, pickup_point: ?string, extras: array, payment_mode: string, full_name: string, email: string, phone: ?string, message: ?string}  $data
     */
    public function create(TourPackage $tour, TourDeparture $departure, User $user, array $data): TourBooking
    {
        $quote = $tour->quote($departure, $data['adults'], $data['children'], $data['single_rooms'], $data['extras']);
        if ($tour->max_participants && $quote['travelers'] > $tour->max_participants) {
            throw new \InvalidArgumentException(__('Ce circuit accueille au maximum :n voyageurs par réservation.', ['n' => $tour->max_participants]));
        }
        $pickups = collect($tour->pickups());
        if ($pickups->isNotEmpty() && ! $pickups->contains('label', $data['pickup_point'])) {
            throw new \InvalidArgumentException(__('Choisissez un lieu de prise en charge.'));
        }
        $withDeposit = $data['payment_mode'] === 'deposit' && $tour->depositAllowedFor($departure->departure_date);

        return DB::transaction(function () use ($tour, $departure, $user, $data, $quote, $withDeposit) {
            $locked = TourDeparture::whereKey($departure->id)->lockForUpdate()->firstOrFail();
            if (! $locked->isOpenForBooking()) {
                throw new \InvalidArgumentException(__('Ce départ n\'est plus ouvert aux réservations.'));
            }
            if ($locked->seatsLeft() < $quote['travelers']) {
                throw new \InvalidArgumentException($locked->seatsLeft() > 0
                    ? __('Il ne reste que :n place(s) sur ce départ.', ['n' => $locked->seatsLeft()])
                    : __('Ce départ est complet.'));
            }

            return TourBooking::create([
                'tour_package_id' => $tour->id, 'tour_departure_id' => $locked->id, 'provider_id' => $tour->provider_id,
                'user_id' => $user->id, 'tour_name' => $tour->name, 'departure_date' => $locked->departure_date,
                'adults' => $quote['adults'], 'children' => $quote['children'], 'travelers_count' => $quote['travelers'],
                'single_rooms' => $data['single_rooms'], 'pickup_point' => $data['pickup_point'] ?: null,
                'extras' => $quote['extras'] ?: null, 'price_lines' => $quote['lines'],
                'amount_total_xof' => $quote['total'],
                'deposit_xof' => $withDeposit ? $quote['deposit'] : 0,
                'balance_due_on' => $withDeposit ? $locked->departure_date->copy()->subDays((int) $tour->balance_due_days) : null,
                'commission_percent' => $this->payments->commissionPercent(),
                'cancellation_policy' => $tour->cancellationRules() ?: null,
                'status' => TourBooking::STATUS_PENDING_PAYMENT,
                'full_name' => $data['full_name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'message' => $data['message'] ?? null,
            ]);
        });
    }

    /** Étape du premier versement : acompte si prévu, sinon totalité. */
    public function firstStage(TourBooking $booking): string
    {
        return $booking->deposit_xof > 0 && $booking->deposit_xof < $booking->amount_total_xof ? 'deposit' : 'full';
    }

    /** Après un versement : prévenir les deux parties, et garantir le départ si le minimum est atteint. */
    public function afterPayment(TourBooking $booking): void
    {
        $booking->refresh();
        $paidAll = $booking->status === TourBooking::STATUS_PAID;
        $booking->user?->notify(new VisitorSystemNotification(
            $paidAll ? __('Circuit réglé') : __('Acompte reçu — place réservée'),
            '"'.$booking->tour_name.'" — '.$booking->departure_date->format('d/m/Y').' — '.$booking->reference
                .($paidAll ? '' : ' · '.__('Solde de :a FCFA à régler avant le :d.', ['a' => number_format($booking->balanceXof(), 0, ',', ' '), 'd' => $booking->balance_due_on?->format('d/m/Y')])),
            route('visitor.tour-bookings.index'),
        ));
        $booking->provider?->user?->notify(new VisitorSystemNotification(
            'Réservation de circuit',
            $booking->full_name.' — '.$booking->travelers_count.' voyageur(s) — "'.$booking->tour_name.'" du '.$booking->departure_date->format('d/m/Y').' ('.$booking->statusLabel().')',
            route('provider.tours.departures', $booking->tour_package_id),
        ));
        if ($booking->departure) {
            $this->guaranteeIfReached($booking->departure);
        }
    }

    /** Minimum atteint : départ garanti, les inscrits sont prévenus une seule fois. */
    public function guaranteeIfReached(TourDeparture $departure, bool $force = false): bool
    {
        $departure->refresh();
        if ($departure->guaranteed_at || $departure->cancelled_at) {
            return false;
        }
        $min = $departure->minimum();
        if (! $force && $min !== null && $departure->onlineTravelers() < $min) {
            return false;
        }
        if (! $force && $min === null) {
            return false; // pas de minimum : rien à annoncer
        }
        $departure->update(['guaranteed_at' => now()]);
        foreach ($departure->bookings()->whereIn('status', [TourBooking::STATUS_DEPOSIT_PAID, TourBooking::STATUS_PAID])->with('user')->get() as $b) {
            $b->user?->notify(new VisitorSystemNotification(
                __('Départ garanti'),
                __('Votre circuit ":t" du :d aura bien lieu.', ['t' => $b->tour_name, 'd' => $b->departure_date->format('d/m/Y')]),
                route('visitor.tour-bookings.index'),
            ));
        }

        return true;
    }

    /** Annulation par le voyageur : remboursement selon le barème figé à la réservation. */
    public function cancelByClient(TourBooking $booking): int
    {
        return DB::transaction(function () use ($booking) {
            $booking = TourBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if (! $booking->canBeCancelledByClient()) {
                throw new \RuntimeException(__('Cette réservation ne peut plus être annulée.'));
            }
            $refund = $booking->refundIfCancelledNow();
            $booking->update(['status' => TourBooking::STATUS_CANCELLED, 'cancelled_at' => now(), 'cancelled_by' => 'client']);
            if ($refund > 0) {
                $this->payments->refund($booking, $refund, __('Annulation de circuit'));
            }
            $booking->provider?->user?->notify(new VisitorSystemNotification(
                'Circuit annulé par le voyageur',
                $booking->full_name.' a annulé '.$booking->reference.' ("'.$booking->tour_name.'" du '.$booking->departure_date->format('d/m/Y').'). Remboursé : '.number_format($refund, 0, ',', ' ').' FCFA.',
                route('provider.tours.departures', $booking->tour_package_id),
            ));

            return $refund;
        });
    }

    /** Départ annulé (agence, ou minimum non atteint) : tous les inscrits sont remboursés à 100 % sur leur solde. */
    public function cancelDeparture(TourDeparture $departure, string $by, string $reason): int
    {
        return DB::transaction(function () use ($departure, $by, $reason) {
            $departure = TourDeparture::whereKey($departure->id)->lockForUpdate()->firstOrFail();
            if ($departure->cancelled_at) {
                return 0;
            }
            $departure->update(['cancelled_at' => now(), 'cancel_reason' => $reason, 'is_active' => false]);
            $count = 0;
            foreach ($departure->bookings()->whereIn('status', [TourBooking::STATUS_DEPOSIT_PAID, TourBooking::STATUS_PAID, TourBooking::STATUS_PENDING_PAYMENT])->get() as $b) {
                $b->update(['status' => TourBooking::STATUS_CANCELLED, 'cancelled_at' => now(), 'cancelled_by' => $by]);
                if ($b->amount_paid_xof > 0) {
                    $this->payments->refund($b, $b->amount_paid_xof, __('Départ annulé — remboursement intégral'));
                }
                $b->user?->notify(new VisitorSystemNotification(
                    __('Départ annulé — remboursement intégral'),
                    __('Le départ du :d de ":t" est annulé (:r). :a FCFA ont été crédités sur votre solde : vous pouvez réserver un autre départ.', [
                        'd' => $b->departure_date->format('d/m/Y'), 't' => $b->tour_name, 'r' => $reason, 'a' => number_format($b->amount_paid_xof, 0, ',', ' '),
                    ]),
                    $b->tour?->publicUrl() ?? route('visitor.tour-bookings.index'),
                ));
                $count++;
            }

            return $count;
        });
    }

    /**
     * Traitement quotidien : relances du solde, annulation pour solde impayé, décision des départs
     * (garanti ou annulé si le minimum n'est pas atteint à la date limite), libération des crédits.
     *
     * @return array<string, int>
     */
    public function processDaily(): array
    {
        $today = now('Africa/Abidjan')->startOfDay();
        $stats = ['reminded' => 0, 'cancelled_unpaid' => 0, 'guaranteed' => 0, 'cancelled_departures' => 0, 'released' => 0];

        // Relance 3 jours avant l'échéance du solde, puis le jour même.
        TourBooking::where('status', TourBooking::STATUS_DEPOSIT_PAID)->whereNotNull('balance_due_on')
            ->whereDate('balance_due_on', '<=', $today->copy()->addDays(3)->toDateString())
            ->whereDate('balance_due_on', '>=', $today->toDateString())
            ->where(fn ($q) => $q->whereNull('balance_reminded_at')->orWhereDate('balance_reminded_at', '<', $today->toDateString()))
            ->get()
            ->filter(fn ($b) => $b->balance_reminded_at === null || $b->balance_due_on->isSameDay($today))
            ->each(function (TourBooking $b) use (&$stats) {
                $b->user?->notify(new VisitorSystemNotification(
                    __('Solde de votre circuit à régler'),
                    __(':a FCFA à régler avant le :d pour ":t".', ['a' => number_format($b->balanceXof(), 0, ',', ' '), 'd' => $b->balance_due_on->format('d/m/Y'), 't' => $b->tour_name]),
                    route('visitor.tour-bookings.index'),
                ));
                $b->update(['balance_reminded_at' => now()]);
                $stats['reminded']++;
            });

        // Solde non réglé après le délai de grâce : annulée, remboursement selon le barème.
        TourBooking::where('status', TourBooking::STATUS_DEPOSIT_PAID)->whereNotNull('balance_due_on')
            ->whereDate('balance_due_on', '<', $today->copy()->subDays(self::UNPAID_GRACE_DAYS)->toDateString())
            ->get()->each(function (TourBooking $b) use (&$stats) {
                $refund = $b->refundIfCancelledNow();
                $b->update(['status' => TourBooking::STATUS_CANCELLED, 'cancelled_at' => now(), 'cancelled_by' => 'unpaid']);
                if ($refund > 0) {
                    $this->payments->refund($b, $refund, __('Annulation pour solde impayé'));
                }
                $b->user?->notify(new VisitorSystemNotification(__('Réservation de circuit annulée'), __('Le solde de ":t" n\'a pas été réglé à temps.', ['t' => $b->tour_name]), route('visitor.tour-bookings.index')));
                $stats['cancelled_unpaid']++;
            });

        // Décision des départs à la date limite.
        TourDeparture::with('tour')->whereNull('cancelled_at')->whereNull('guaranteed_at')
            ->whereDate('departure_date', '>=', $today->toDateString())->get()
            ->filter(fn (TourDeparture $d) => $d->minimum() !== null && $d->decisionDate()->lte($today))
            ->each(function (TourDeparture $d) use (&$stats) {
                if ($d->onlineTravelers() >= $d->minimum()) {
                    $stats['guaranteed'] += (int) $this->guaranteeIfReached($d);
                } elseif ($d->onlineTravelers() > 0 || $d->bookings()->exists()) {
                    $this->cancelDeparture($d, 'departure', __('minimum de :n voyageurs non atteint', ['n' => $d->minimum()]));
                    $d->tour?->provider?->user?->notify(new VisitorSystemNotification('Départ annulé automatiquement', '"'.$d->tour->name.'" du '.$d->departure_date->format('d/m/Y').' : minimum de '.$d->minimum().' voyageurs non atteint. Les inscrits ont été remboursés.', route('provider.tours.departures', $d->tour_package_id)));
                    $stats['cancelled_departures']++;
                } else {
                    // Personne d'inscrit : on ferme simplement les réservations.
                    $d->update(['is_active' => false]);
                }
            });

        $stats['released'] = $this->payments->releaseDueCredits();

        return $stats;
    }
}
