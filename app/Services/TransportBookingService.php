<?php

namespace App\Services;

use App\Models\TransportBooking;
use App\Models\TransportOffer;
use App\Models\TransportTrip;
use App\Models\TransportVehicle;
use App\Models\User;
use App\Notifications\VisitorSystemNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Règles des réservations de transport : devis selon le mode, véhicule libre (pas de double réservation,
 * entretien respecté), sièges d'une ligne, annulation gratuite, modification, statut du trajet.
 */
class TransportBookingService
{
    /** Facteur route / vol d'oiseau pour estimer la distance sans service de cartographie. */
    public const ROAD_FACTOR = 1.3;

    /** Vitesse moyenne retenue pour estimer la durée d'un trajet en ville et en périphérie (km/h). */
    public const AVG_SPEED_KMH = 35;

    public function __construct(private readonly TransportPaymentService $payments) {}

    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round(2 * $r * asin(min(1, sqrt($a))) * self::ROAD_FACTOR, 1);
    }

    private function isNight(Carbon $t): bool
    {
        return $t->hour >= 22 || $t->hour < 6;
    }

    /**
     * Devis complet. Lève \InvalidArgumentException si la demande n'est pas valable.
     *
     * @param  array<string, mixed>  $in
     * @return array{lines: array, total: int, starts_at: Carbon, ends_at: Carbon, return_at: ?Carbon, distance_km: ?float, extras: array, pickup: ?string, dropoff: ?string, deposit_on_site: ?int, seats: ?array, trip: ?TransportTrip}
     */
    public function quote(TransportOffer $offer, array $in): array
    {
        $tz = 'Africa/Abidjan';
        $lines = [];
        $returnAt = null;
        $distance = null;
        $pickup = $in['pickup_label'] ?? null;
        $dropoff = $in['dropoff_label'] ?? null;
        $depositOnSite = null;
        $seats = null;
        $trip = null;
        $passengers = max(1, (int) ($in['passengers'] ?? 1));
        $days = 1;

        if ($offer->mode === 'line') {
            $trip = $offer->upcomingTrips()->find($in['trip_id'] ?? 0);
            if (! $trip) {
                throw new \InvalidArgumentException(__('Choisissez un départ proposé.'));
            }
            $seats = collect($in['seats'] ?? [])->map(fn ($s) => (int) $s)->filter(fn ($s) => $s >= 1 && $s <= $trip->seats_total)->unique()->sort()->values()->all();
            if (! $seats) {
                throw new \InvalidArgumentException(__('Choisissez au moins un siège.'));
            }
            $passengers = count($seats);
            $start = $trip->departs_at->copy()->timezone($tz);
            $end = $start->copy()->addMinutes($offer->trip_minutes ?: 120);
            $lines[] = ['label' => $passengers.' × '.__('Siège').' ('.implode(', ', $seats).')', 'amount_xof' => $passengers * $trip->price()];
            $pickup = $offer->route_from;
            $dropoff = $offer->route_to;
        } else {
            try {
                $start = Carbon::parse((string) ($in['starts_at'] ?? ''), $tz);
            } catch (\Throwable) {
                throw new \InvalidArgumentException(__('Indiquez la date et l\'heure.'));
            }
            if ($start->lte(now($tz)->addHours(2))) {
                throw new \InvalidArgumentException(__('Réservez au moins 2 heures à l\'avance.'));
            }
            if ($offer->max_passengers && $passengers > $offer->max_passengers) {
                throw new \InvalidArgumentException(__('Ce véhicule accueille au maximum :n passagers.', ['n' => $offer->max_passengers]));
            }

            if ($offer->mode === 'transfer') {
                $zones = $offer->zones();
                $zone = $zones[(int) ($in['zone'] ?? -1)] ?? null;
                if (! $zone) {
                    throw new \InvalidArgumentException(__('Choisissez une destination.'));
                }
                $toOrigin = ($in['direction'] ?? 'from') === 'to';
                $address = trim((string) ($in['address'] ?? '')) ?: $zone['label'];
                $pickup = $toOrigin ? $address : $offer->origin_label;
                $dropoff = $toOrigin ? $offer->origin_label : $address;
                $base = $zone['price_xof'];
                $lines[] = ['label' => __('Transfert').' '.$pickup.' → '.$dropoff, 'amount_xof' => $base];
                if ($offer->night_surcharge_percent && $this->isNight($start)) {
                    $lines[] = ['label' => __('Supplément de nuit (22 h – 6 h)').' +'.$offer->night_surcharge_percent.' %', 'amount_xof' => (int) round($base * $offer->night_surcharge_percent / 100)];
                }
                $end = $start->copy()->addMinutes(($offer->trip_minutes ?: 60) + (int) ($offer->free_waiting_minutes ?? 0));
                if (! empty($in['round_trip'])) {
                    try {
                        $returnAt = Carbon::parse((string) ($in['return_at'] ?? ''), $tz);
                    } catch (\Throwable) {
                        throw new \InvalidArgumentException(__('Indiquez la date et l\'heure du retour.'));
                    }
                    if ($returnAt->lte($end)) {
                        throw new \InvalidArgumentException(__('Le retour doit être après l\'aller.'));
                    }
                    $back = $base;
                    if ($offer->night_surcharge_percent && $this->isNight($returnAt)) {
                        $back += (int) round($base * $offer->night_surcharge_percent / 100);
                    }
                    $lines[] = ['label' => __('Retour').' '.$dropoff.' → '.$pickup, 'amount_xof' => $back];
                    if ($offer->round_trip_discount_percent) {
                        $lines[] = ['label' => __('Remise aller-retour').' −'.$offer->round_trip_discount_percent.' %', 'amount_xof' => -(int) round(($base + $back) * $offer->round_trip_discount_percent / 100)];
                    }
                }
            } elseif ($offer->mode === 'chauffeur') {
                $package = $in['package'] ?? 'trip';
                if ($package === 'trip') {
                    foreach (['pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng'] as $k) {
                        if (! is_numeric($in[$k] ?? null)) {
                            throw new \InvalidArgumentException(__('Placez le départ et l\'arrivée sur la carte.'));
                        }
                    }
                    if (! $offer->per_km_xof) {
                        throw new \InvalidArgumentException(__('Cette offre se réserve au forfait.'));
                    }
                    $distance = self::distanceKm((float) $in['pickup_lat'], (float) $in['pickup_lng'], (float) $in['dropoff_lat'], (float) $in['dropoff_lng']);
                    $minutes = (int) ceil($distance / self::AVG_SPEED_KMH * 60) + 15;
                    $price = (int) $offer->base_fare_xof + (int) round($distance * $offer->per_km_xof) + (int) round($minutes / 60 * (int) $offer->per_hour_xof);
                    $lines[] = ['label' => __('Trajet estimé : :km km, environ :m min', ['km' => number_format($distance, 1, ',', ' '), 'm' => $minutes]), 'amount_xof' => max($price, (int) $offer->minimum_xof)];
                    if ($offer->minimum_xof && $price < $offer->minimum_xof) {
                        $lines[0]['label'] .= ' — '.__('tarif minimum');
                    }
                    $end = $start->copy()->addMinutes($minutes + 30);
                } else {
                    $amount = $package === 'full_day' ? (int) $offer->full_day_xof : (int) $offer->half_day_xof;
                    if ($amount <= 0) {
                        throw new \InvalidArgumentException(__('Ce forfait n\'est pas proposé.'));
                    }
                    $hours = $package === 'full_day' ? 10 : 5;
                    $lines[] = ['label' => ($package === 'full_day' ? __('Forfait journée (10 h)') : __('Forfait demi-journée (5 h)')).($offer->included_km ? ' · '.__(':n km inclus', ['n' => $offer->included_km]) : ''), 'amount_xof' => $amount];
                    $end = $start->copy()->addHours($hours);
                    $dropoff = $dropoff ?: __('Mise à disposition');
                }
                if ($offer->night_surcharge_percent && $this->isNight($start)) {
                    $lines[] = ['label' => __('Supplément de nuit (22 h – 6 h)').' +'.$offer->night_surcharge_percent.' %', 'amount_xof' => (int) round($lines[0]['amount_xof'] * $offer->night_surcharge_percent / 100)];
                }
            } elseif ($offer->mode === 'rental') {
                try {
                    $end = Carbon::parse((string) ($in['return_at'] ?? ''), $tz);
                } catch (\Throwable) {
                    throw new \InvalidArgumentException(__('Indiquez la date de retour.'));
                }
                if ($end->lte($start)) {
                    throw new \InvalidArgumentException(__('La date de retour doit être après le retrait.'));
                }
                $days = max(1, (int) ceil($start->diffInMinutes($end) / 1440));
                $daily = $days >= 7 && $offer->weekly_daily_xof ? (int) $offer->weekly_daily_xof : (int) $offer->price_xof;
                $lines[] = ['label' => trans_choice(':count jour|:count jours', $days).' × '.number_format($daily, 0, ',', ' ').' FCFA'.($days >= 7 && $offer->weekly_daily_xof ? ' ('.__('tarif semaine').')' : ''), 'amount_xof' => $days * $daily];
                $depositOnSite = $offer->deposit_xof ?: null;
                $pickup = $pickup ?: $offer->company()?->adresse;
            } else {
                throw new \InvalidArgumentException(__('Cette offre se réserve sur demande.'));
            }
        }

        $offered = $offer->extrasList();
        $extras = collect($in['extras'] ?? [])->map(fn ($i) => (int) $i)->unique()->filter(fn ($i) => isset($offered[$i]))
            ->map(fn ($i) => ['label' => $offered[$i]['label'], 'qty' => match ($offered[$i]['per']) { 'day' => $days, 'passenger' => $passengers, default => 1 }, 'unit_price_xof' => $offered[$i]['price_xof']])
            ->values()->all();
        foreach ($extras as $e) {
            $lines[] = ['label' => ($e['qty'] > 1 ? $e['qty'].' × ' : '').$e['label'], 'amount_xof' => $e['qty'] * $e['unit_price_xof']];
        }

        return [
            'lines' => $lines, 'total' => max(0, (int) collect($lines)->sum('amount_xof')), 'starts_at' => $start, 'ends_at' => $end,
            'return_at' => $returnAt, 'distance_km' => $distance, 'extras' => $extras, 'pickup' => $pickup, 'dropoff' => $dropoff,
            'deposit_on_site' => $depositOnSite, 'seats' => $seats, 'trip' => $trip, 'passengers' => $passengers, 'days' => $days,
        ];
    }

    /**
     * Premier véhicule libre de la catégorie de l'offre sur [début, fin] (assez de places, hors entretien,
     * sans autre réservation qui chevauche). null si la compagnie n'a déclaré aucun véhicule de cette catégorie.
     *
     * @throws \InvalidArgumentException si la flotte existe mais qu'aucun véhicule n'est libre.
     */
    public function freeVehicle(TransportOffer $offer, Carbon $start, Carbon $end, int $passengers, ?int $ignoreBookingId = null): ?TransportVehicle
    {
        $fleet = TransportVehicle::where('provider_id', $offer->provider_id)->where('is_active', true)
            ->when($offer->vehicle_category, fn ($q) => $q->where('category', $offer->vehicle_category))->orderBy('seats')->get();
        if ($fleet->isEmpty()) {
            return null;
        }
        $busy = TransportBooking::whereIn('transport_vehicle_id', $fleet->pluck('id'))
            ->where(fn ($q) => $q->where('status', TransportBooking::STATUS_PAID)
                ->orWhere(fn ($p) => $p->where('status', TransportBooking::STATUS_PENDING_PAYMENT)->where('created_at', '>=', now()->subMinutes(30))))
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->where('starts_at', '<', $end->copy()->utc())->where('ends_at', '>', $start->copy()->utc())
            ->pluck('transport_vehicle_id')->all();
        $vehicle = $fleet->first(fn (TransportVehicle $v) => $v->seats >= $passengers && ! in_array($v->id, $busy, true) && ! $v->inMaintenance($start, $end));
        if (! $vehicle) {
            throw new \InvalidArgumentException(__('Aucun véhicule disponible sur cette période. Essayez un autre horaire.'));
        }

        return $vehicle;
    }

    /** @param array<string, mixed> $in */
    public function create(TransportOffer $offer, User $user, array $in): TransportBooking
    {
        if (! $offer->bookableOnline()) {
            throw new \InvalidArgumentException(__('Cette offre se réserve sur demande.'));
        }
        $q = $this->quote($offer, $in);

        return DB::transaction(function () use ($offer, $user, $in, $q) {
            $vehicle = null;
            if ($offer->mode === 'line') {
                $trip = TransportTrip::whereKey($q['trip']->id)->lockForUpdate()->firstOrFail();
                $taken = array_intersect($q['seats'], $trip->takenSeats());
                if ($taken) {
                    throw new \InvalidArgumentException(__('Siège(s) déjà pris : :s. Choisissez-en d\'autres.', ['s' => implode(', ', $taken)]));
                }
                $vehicle = $trip->vehicle;
            } else {
                TransportOffer::whereKey($offer->id)->lockForUpdate()->first();
                $vehicle = $this->freeVehicle($offer, $q['starts_at'], $q['ends_at'], $q['passengers']);
            }
            $start = $q['starts_at'];
            $hours = $offer->free_cancellation_hours;

            return TransportBooking::create([
                'transport_offer_id' => $offer->id, 'provider_id' => $offer->provider_id, 'user_id' => $user->id,
                'transport_vehicle_id' => $vehicle?->id, 'transport_trip_id' => $q['trip']?->id, 'mode' => $offer->mode, 'offer_name' => $offer->name,
                'pickup_label' => $q['pickup'], 'pickup_lat' => $in['pickup_lat'] ?? null, 'pickup_lng' => $in['pickup_lng'] ?? null,
                'dropoff_label' => $q['dropoff'], 'dropoff_lat' => $in['dropoff_lat'] ?? null, 'dropoff_lng' => $in['dropoff_lng'] ?? null,
                'distance_km' => $q['distance_km'], 'starts_at' => $start->copy()->utc(), 'ends_at' => $q['ends_at']->copy()->utc(),
                'round_trip' => (bool) $q['return_at'], 'return_at' => $q['return_at']?->copy()->utc(),
                'flight_number' => $offer->mode === 'transfer' ? (strtoupper(preg_replace('/\s+/', '', (string) ($in['flight_number'] ?? ''))) ?: null) : null,
                'passengers' => $q['passengers'], 'luggage' => (int) ($in['luggage'] ?? 0), 'seats' => $q['seats'], 'extras' => $q['extras'] ?: null,
                'price_lines' => $q['lines'], 'amount_total_xof' => $q['total'], 'deposit_on_site_xof' => $q['deposit_on_site'],
                'commission_percent' => $this->payments->commissionPercent(), 'status' => TransportBooking::STATUS_PENDING_PAYMENT,
                'cancellable_until' => $hours !== null ? $start->copy()->subHours($hours)->utc() : null,
                'full_name' => $in['full_name'], 'email' => $in['email'], 'phone' => $in['phone'] ?? null, 'message' => $in['message'] ?? null,
            ]);
        });
    }

    public function afterPayment(TransportBooking $b): void
    {
        $b->refresh();
        $when = $b->starts_at->timezone('Africa/Abidjan')->format('d/m/Y H:i');
        $b->user?->notify(new VisitorSystemNotification(__('Transport confirmé'), '"'.$b->offer_name.'" — '.$when.' — '.$b->reference, route('visitor.transport-bookings.index')));
        $b->provider?->user?->notify(new VisitorSystemNotification('Nouvelle réservation de transport', $b->full_name.' — '.$b->offer_name.' — '.$when.($b->flight_number ? ' — vol '.$b->flight_number : '').' ('.$b->passengers.' pers.)', route('provider.transport-bookings.index')));
    }

    /** Annulation gratuite : remboursement intégral sur le solde, véhicule et sièges libérés. */
    public function cancel(TransportBooking $booking): int
    {
        $refund = DB::transaction(function () use ($booking) {
            $b = TransportBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if (! $b->isCancellableByClient()) {
                throw new \RuntimeException(__('Le délai d\'annulation gratuite est dépassé.'));
            }
            $refund = (int) $b->amount_total_xof;
            $b->update(['status' => $refund > 0 ? TransportBooking::STATUS_REFUNDED : TransportBooking::STATUS_CANCELLED, 'cancelled_at' => now(), 'refund_xof' => $refund ?: null]);
            if ($refund > 0) {
                $this->payments->refund($b, $refund, __('Annulation de transport'));
            }

            return $refund;
        });
        $booking->provider?->user?->notify(new VisitorSystemNotification('Transport annulé', $booking->full_name.' a annulé '.$booking->reference.' ('.$booking->offer_name.', '.$booking->starts_at->timezone('Africa/Abidjan')->format('d/m/Y H:i').').', route('provider.transport-bookings.index')));

        return $refund;
    }

    /** Modification gratuite de l'heure (transfert, chauffeur) et du numéro de vol, avant le délai. */
    public function modify(TransportBooking $booking, string $startsAt, ?string $flight): void
    {
        DB::transaction(function () use ($booking, $startsAt, $flight) {
            $b = TransportBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if (! $b->canBeModified()) {
                throw new \RuntimeException(__('Cette réservation ne peut plus être modifiée.'));
            }
            $tz = 'Africa/Abidjan';
            $new = Carbon::parse($startsAt, $tz);
            if ($new->lte(now($tz)->addHours(2))) {
                throw new \RuntimeException(__('Réservez au moins 2 heures à l\'avance.'));
            }
            $duration = $b->starts_at->diffInMinutes($b->ends_at);
            $end = $new->copy()->addMinutes($duration);
            $offer = $b->offer;
            $vehicleId = $b->transport_vehicle_id;
            if ($offer) {
                try {
                    $vehicleId = $this->freeVehicle($offer, $new, $end, $b->passengers, $b->id)?->id;
                } catch (\InvalidArgumentException $e) {
                    throw new \RuntimeException($e->getMessage());
                }
            }
            $b->update([
                'starts_at' => $new->copy()->utc(), 'ends_at' => $end->copy()->utc(), 'transport_vehicle_id' => $vehicleId,
                'flight_number' => $b->mode === 'transfer' ? (strtoupper(preg_replace('/\s+/', '', (string) $flight)) ?: $b->flight_number) : null,
                'cancellable_until' => $offer?->free_cancellation_hours !== null ? $new->copy()->subHours($offer->free_cancellation_hours)->utc() : $b->cancellable_until,
            ]);
        });
        $booking->refresh();
        $booking->provider?->user?->notify(new VisitorSystemNotification('Transport modifié', $booking->full_name.' a modifié '.$booking->reference.' : '.$booking->starts_at->timezone('Africa/Abidjan')->format('d/m/Y H:i').($booking->flight_number ? ' — vol '.$booking->flight_number : ''), route('provider.transport-bookings.index')));
    }

    /** Statut du trajet mis à jour par la compagnie : le client est prévenu à chaque étape. */
    public function updateTripStatus(TransportBooking $b, string $status): void
    {
        $b->update(['trip_status' => $status]);
        $driver = $b->driver;
        $b->user?->notify(new VisitorSystemNotification(
            $b->tripStatusLabel(),
            $b->offer_name.' — '.$b->reference.($driver ? ' · '.$driver->name.' ('.$driver->phone.')' : '').($b->vehicle ? ' · '.$b->vehicle->name.($b->vehicle->plate ? ' '.$b->vehicle->plate : '') : ''),
            route('visitor.transport-bookings.index'),
        ));
    }
}
