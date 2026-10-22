<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\ActivityWaitlist;
use App\Models\LeisurePackage;
use App\Models\LeisurePassPurchase;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Règles des réservations de loisirs : créneau et places vérifiés sous verrou, carte multi-séances,
 * décharge, formules avec acompte, annulation gratuite, report, liste d'attente.
 */
class LeisureBookingService
{
    public function __construct(
        private readonly LeisureAvailability $availability,
        private readonly LeisurePaymentService $payments,
    ) {}

    /**
     * @param  array{date: string, time: string, tiers: array, addons: array, payment_method: string, pass_id: ?int, waiver_names: array, waiver_accept: bool, full_name: string, email: string, phone: ?string, message: ?string, ip: ?string}  $d
     */
    public function bookActivity(Activity $activity, User $user, array $d): ActivityBooking
    {
        if (! $activity->bookableOnline()) {
            throw new \InvalidArgumentException(__('Cette activité ne se réserve pas en ligne.'));
        }
        $time = \App\Models\TouristVisitType::normalizeTime((string) $d['time']);
        if (! $time || ! in_array($time, $activity->slots(), true)) {
            throw new \InvalidArgumentException(__('Choisissez un créneau horaire.'));
        }
        $day = Carbon::parse($d['date'], 'Africa/Abidjan')->startOfDay();
        if (! $activity->isOpenOn($day)) {
            throw new \InvalidArgumentException(__('L\'activité n\'a pas lieu ce jour-là.'));
        }
        // Avec une carte, la séance est prépayée : les options payantes ne sont pas proposées.
        $quote = $activity->quote($d['tiers'], $d['payment_method'] === 'pass' ? [] : $d['addons'], $day, $time);
        $n = $quote['participants'];
        if ($n < 1) {
            throw new \InvalidArgumentException(__('Indiquez au moins un participant.'));
        }
        if ($activity->max_participants && $n > $activity->max_participants) {
            throw new \InvalidArgumentException(__('Cette activité accueille au maximum :n participants par réservation.', ['n' => $activity->max_participants]));
        }
        $waiver = null;
        if ($activity->requires_waiver) {
            $names = collect($d['waiver_names'] ?? [])->map(fn ($x) => trim((string) $x))->filter()->values();
            if (! $d['waiver_accept'] || $names->count() < $n) {
                throw new \InvalidArgumentException(__('Indiquez le nom de chaque participant et acceptez la décharge de responsabilité.'));
            }
            $waiver = ['names' => $names->take($n)->all(), 'accepted_at' => now()->toIso8601String(), 'ip' => $d['ip'] ?? null, 'text_hash' => sha1((string) $activity->waiver_text)];
        }

        $usePass = $d['payment_method'] === 'pass';
        $start = $day->copy()->setTimeFromTimeString($time);
        if ($start->lte(now('Africa/Abidjan'))) {
            throw new \InvalidArgumentException(__('Ce créneau est déjà passé. Choisissez-en un autre.'));
        }

        return DB::transaction(function () use ($activity, $user, $d, $quote, $n, $time, $day, $start, $waiver, $usePass) {
            Activity::whereKey($activity->id)->lockForUpdate()->first();
            $remaining = $this->availability->remaining($activity->load('resources'), $day->toDateString(), $time);
            if ($remaining !== null && $remaining < $n) {
                throw new \InvalidArgumentException($remaining > 0
                    ? __('Il ne reste que :n place(s) sur ce créneau.', ['n' => $remaining])
                    : __('Ce créneau est complet. Choisissez-en un autre ou inscrivez-vous sur la liste d\'attente.'));
            }

            $pass = null;
            if ($usePass) {
                $pass = LeisurePassPurchase::whereKey($d['pass_id'] ?? 0)->where('user_id', $user->id)->lockForUpdate()->first();
                if (! $pass || ! $pass->usableFor($activity, $day, $n)) {
                    throw new \InvalidArgumentException(__('Cette carte n\'est pas valable pour cette réservation (activité, date ou séances restantes).'));
                }
                if ($pass->sessions_total !== null) {
                    $pass->increment('sessions_used', $n);
                }
            }

            $booking = ActivityBooking::create([
                'activity_id' => $activity->id, 'provider_id' => $activity->provider_id, 'user_id' => $user->id,
                'leisure_pass_purchase_id' => $pass?->id, 'item_name' => $activity->name,
                'booking_date' => $day->toDateString(), 'booking_time' => $time, 'duration_minutes' => $activity->duration_minutes ?: 60,
                'participants' => $n, 'tier_breakdown' => $quote['breakdown'], 'addons' => $quote['addons'] ?: null,
                'price_adjust_percent' => $quote['adjust'] ?: null,
                'amount_total_xof' => $usePass ? 0 : $quote['total'], 'amount_paid_xof' => $usePass ? 0 : $quote['total'],
                'commission_percent' => $this->payments->commissionPercent(),
                'status' => $usePass || $quote['total'] <= 0 ? ActivityBooking::STATUS_PAID : ActivityBooking::STATUS_PENDING_PAYMENT,
                'gateway' => $usePass ? 'pass' : null, 'paid_at' => $usePass || $quote['total'] <= 0 ? now() : null,
                'cancellable_until' => $activity->free_cancellation_hours !== null ? $start->copy()->subHours($activity->free_cancellation_hours)->utc() : null,
                'waiver' => $waiver, 'full_name' => $d['full_name'], 'email' => $d['email'], 'phone' => $d['phone'] ?? null, 'message' => $d['message'] ?? null,
            ]);
            ActivityWaitlist::where('activity_id', $activity->id)->where('user_id', $user->id)->whereDate('booking_date', $day->toDateString())
                ->where('booking_time', $time)->whereIn('status', [ActivityWaitlist::STATUS_WAITING, ActivityWaitlist::STATUS_NOTIFIED])
                ->update(['status' => ActivityWaitlist::STATUS_BOOKED]);

            return $booking;
        });
    }

    /** @param array{date: string, time: string, participants: int, celebrant_name: ?string, full_name: string, email: string, phone: ?string, message: ?string} $d */
    public function bookPackage(LeisurePackage $package, User $user, array $d): ActivityBooking
    {
        $time = \App\Models\TouristVisitType::normalizeTime((string) $d['time']);
        if (! $package->is_active || ! $time || ! in_array($time, $package->slots(), true)) {
            throw new \InvalidArgumentException(__('Choisissez un horaire proposé.'));
        }
        $n = (int) $d['participants'];
        if ($n < $package->min_participants || ($package->max_participants && $n > $package->max_participants)) {
            throw new \InvalidArgumentException(__('Cette formule accueille de :min à :max participants.', ['min' => $package->min_participants, 'max' => $package->max_participants ?: '∞']));
        }
        $day = Carbon::parse($d['date'], 'Africa/Abidjan')->startOfDay();
        $start = $day->copy()->setTimeFromTimeString($time);
        if ($start->lte(now('Africa/Abidjan')->addDay())) {
            throw new \InvalidArgumentException(__('Réservez une formule au moins 24 h à l\'avance.'));
        }
        $total = $package->priceFor($day, $n);
        $deposit = $package->deposit_xof > 0 ? min($package->deposit_xof, $total) : $total;

        return DB::transaction(function () use ($package, $user, $d, $n, $time, $day, $start, $total, $deposit) {
            LeisurePackage::whereKey($package->id)->lockForUpdate()->first();
            $slot = collect($this->availability->packageSlotsFor($package->load('resource'), $day->toDateString()))->firstWhere('time', $time);
            if (! $slot || ! $slot['available']) {
                throw new \InvalidArgumentException(__('Cet horaire n\'est plus disponible pour cette formule. Choisissez-en un autre.'));
            }

            return ActivityBooking::create([
                'leisure_package_id' => $package->id, 'provider_id' => $package->provider_id, 'user_id' => $user->id,
                'item_name' => $package->name, 'booking_date' => $day->toDateString(), 'booking_time' => $time,
                'duration_minutes' => $package->duration_minutes, 'participants' => $n,
                'tier_breakdown' => [['label' => __('Forfait :n participants', ['n' => min($n, $package->included_participants)]), 'qty' => 1, 'unit_price_xof' => $package->basePriceFor($day)]]
                    + ($n > $package->included_participants ? [1 => ['label' => __('Participant supplémentaire'), 'qty' => $n - $package->included_participants, 'unit_price_xof' => (int) $package->extra_participant_xof]] : []),
                'amount_total_xof' => $total, 'amount_paid_xof' => $deposit, 'commission_percent' => $this->payments->commissionPercent(),
                'status' => ActivityBooking::STATUS_PENDING_PAYMENT,
                'cancellable_until' => $start->copy()->subDays((int) $package->free_cancellation_days)->utc(),
                'full_name' => $d['full_name'], 'email' => $d['email'], 'phone' => $d['phone'] ?? null, 'message' => $d['message'] ?? null,
                'celebrant_name' => $d['celebrant_name'] ?? null,
            ]);
        });
    }

    public function afterPayment(ActivityBooking $booking): void
    {
        $booking->refresh();
        $booking->user?->notify(new VisitorSystemNotification(
            __('Réservation confirmée'),
            '"'.$booking->item_name.'" — '.$booking->booking_date->format('d/m/Y').($booking->booking_time ? ' à '.$booking->booking_time : '').' — '.$booking->reference,
            route('visitor.activity-bookings.index'),
        ));
        $booking->provider?->user?->notify(new VisitorSystemNotification(
            'Nouvelle réservation',
            $booking->full_name.' — '.$booking->participants.' pers. — "'.$booking->item_name.'" le '.$booking->booking_date->format('d/m/Y').' à '.$booking->booking_time,
            route('provider.activity-bookings.index'),
        ));
    }

    /** Annulation gratuite par le client : remboursement intégral sur le solde (ou séances recréditées). */
    public function cancel(ActivityBooking $booking): int
    {
        $refund = DB::transaction(function () use ($booking) {
            $booking = ActivityBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if (! $booking->isCancellableByClient()) {
                throw new \RuntimeException(__('Le délai d\'annulation gratuite est dépassé.'));
            }
            if ($booking->leisure_pass_purchase_id && $booking->passPurchase?->sessions_total !== null) {
                $booking->passPurchase->decrement('sessions_used', min($booking->participants, $booking->passPurchase->sessions_used));
            }
            $refund = (int) $booking->amount_paid_xof;
            $booking->update(['status' => $refund > 0 ? ActivityBooking::STATUS_REFUNDED : ActivityBooking::STATUS_CANCELLED, 'cancelled_at' => now(), 'refund_xof' => $refund ?: null]);
            if ($refund > 0) {
                $this->payments->refund($booking, $refund, __('Annulation d\'activité'));
            }

            return $refund;
        });
        $booking->provider?->user?->notify(new VisitorSystemNotification('Réservation annulée', $booking->full_name.' a annulé '.$booking->reference.' ("'.$booking->item_name.'" du '.$booking->booking_date->format('d/m/Y').').', route('provider.activity-bookings.index')));
        if ($booking->activity_id) {
            $this->releaseWaitlist($booking->activity, $booking->booking_date->toDateString(), $booking->booking_time);
        }

        return $refund;
    }

    /** Report vers un autre créneau libre de la même activité, sans frais, avant la fin du délai gratuit. */
    public function reschedule(ActivityBooking $booking, string $date, string $time): void
    {
        $old = [$booking->booking_date->toDateString(), $booking->booking_time];
        DB::transaction(function () use ($booking, $date, $time) {
            $booking = ActivityBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $activity = $booking->activity?->load('resources');
            if (! $booking->canReschedule() || ! $activity) {
                throw new \RuntimeException(__('Cette réservation ne peut plus être reportée.'));
            }
            $time = \App\Models\TouristVisitType::normalizeTime($time);
            $day = Carbon::parse($date, 'Africa/Abidjan')->startOfDay();
            $start = $time ? $day->copy()->setTimeFromTimeString($time) : null;
            if (! $time || ! in_array($time, $activity->slots(), true) || ! $activity->isOpenOn($day) || $start->lte(now('Africa/Abidjan'))) {
                throw new \RuntimeException(__('Choisissez un créneau à venir proposé par l\'activité.'));
            }
            Activity::whereKey($activity->id)->lockForUpdate()->first();
            $remaining = $this->availability->remaining($activity, $day->toDateString(), $time, $booking->id);
            if ($remaining !== null && $remaining < $booking->participants) {
                throw new \RuntimeException(__('Pas assez de places sur ce créneau.'));
            }
            $booking->update([
                'booking_date' => $day->toDateString(), 'booking_time' => $time, 'reschedule_count' => $booking->reschedule_count + 1,
                'cancellable_until' => $activity->free_cancellation_hours !== null ? $start->copy()->subHours($activity->free_cancellation_hours)->utc() : $booking->cancellable_until,
            ]);
        });
        $booking->refresh();
        $booking->provider?->user?->notify(new VisitorSystemNotification('Réservation reportée', $booking->full_name.' a reporté '.$booking->reference.' au '.$booking->booking_date->format('d/m/Y').' à '.$booking->booking_time.'.', route('provider.activity-bookings.index')));
        $this->releaseWaitlist($booking->activity, $old[0], $old[1]);
    }

    /* ── Liste d'attente ── */

    public function joinWaitlist(Activity $activity, User $user, string $date, string $time, int $participants): ActivityWaitlist
    {
        $remaining = $this->availability->remaining($activity->load('resources'), $date, $time);
        if ($remaining === null || $remaining >= $participants) {
            throw new \InvalidArgumentException(__('Il reste des places sur ce créneau : réservez directement.'));
        }

        return ActivityWaitlist::updateOrCreate(
            ['activity_id' => $activity->id, 'user_id' => $user->id, 'booking_date' => $date, 'booking_time' => $time],
            ['participants' => $participants, 'status' => ActivityWaitlist::STATUS_WAITING, 'notified_at' => null, 'expires_at' => null],
        );
    }

    /**
     * Places libérées : la première personne en attente qui tient dans les places restantes est prévenue
     * et a HOLD_MINUTES pour réserver (une seule personne prévenue à la fois par créneau).
     */
    public function releaseWaitlist(?Activity $activity, string $date, ?string $time): int
    {
        if (! $activity || ! $time) {
            return 0;
        }
        $pending = ActivityWaitlist::where('activity_id', $activity->id)->whereDate('booking_date', $date)->where('booking_time', $time)
            ->where('status', ActivityWaitlist::STATUS_NOTIFIED)->where('expires_at', '>', now())->exists();
        if ($pending || Carbon::parse($date.' '.$time, 'Africa/Abidjan')->lte(now('Africa/Abidjan'))) {
            return 0;
        }
        $remaining = $this->availability->remaining($activity->load('resources'), $date, $time);
        $next = ActivityWaitlist::with('user')->where('activity_id', $activity->id)->whereDate('booking_date', $date)->where('booking_time', $time)
            ->where('status', ActivityWaitlist::STATUS_WAITING)->orderBy('created_at')->orderBy('id')->get()
            ->first(fn ($w) => $remaining === null || $w->participants <= $remaining);
        if (! $next) {
            return 0;
        }
        $next->update(['status' => ActivityWaitlist::STATUS_NOTIFIED, 'notified_at' => now(), 'expires_at' => now()->addMinutes(ActivityWaitlist::HOLD_MINUTES)]);
        $next->user?->notify(new VisitorSystemNotification(
            __('Une place s\'est libérée !'),
            __('":a" le :d à :t : réservez dans les :m minutes, ensuite la place est proposée à la personne suivante.', ['a' => $activity->name, 'd' => Carbon::parse($date)->format('d/m/Y'), 't' => $time, 'm' => ActivityWaitlist::HOLD_MINUTES]),
            route('activity.show', $activity->slug).'?date='.$date.'&heure='.$time,
        ));

        return 1;
    }

    /** Tâche planifiée : délais expirés → personne suivante. */
    public function processWaitlist(): int
    {
        $n = 0;
        ActivityWaitlist::with('activity')->where('status', ActivityWaitlist::STATUS_NOTIFIED)->where('expires_at', '<=', now())->get()
            ->each(function (ActivityWaitlist $w) use (&$n) {
                $w->update(['status' => ActivityWaitlist::STATUS_EXPIRED]);
                $n += $this->releaseWaitlist($w->activity, $w->booking_date->toDateString(), $w->booking_time);
            });
        ActivityWaitlist::where('status', ActivityWaitlist::STATUS_WAITING)->whereDate('booking_date', '<', now('Africa/Abidjan')->toDateString())
            ->update(['status' => ActivityWaitlist::STATUS_EXPIRED]);

        return $n;
    }
}
