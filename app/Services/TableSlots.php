<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\ProviderHour;
use App\Models\Restaurant;
use Carbon\CarbonImmutable;

/**
 * Créneaux de table d'un restaurant : couverts restants par créneau, calculés à partir des
 * demandes non annulées (en attente ou confirmées). Les créneaux suivent les horaires du
 * prestataire ; sans horaires, services du midi (12h–14h30) et du soir (19h–22h30).
 */
class TableSlots
{
    private const DEFAULT_SERVICES = [['12:00', '14:30'], ['19:00', '22:30']];

    public function enabled(Restaurant $r): bool
    {
        return (int) $r->table_capacity > 0;
    }

    /** Début du créneau contenant cette heure (ex. 20:10 → 20:00 pour des créneaux de 30 min). */
    public function slotStart(Restaurant $r, string $time): string
    {
        $len = max(15, (int) $r->slot_minutes ?: 30);
        [$h, $m] = array_map('intval', explode(':', substr($time, 0, 5)) + [0, 0]);
        $min = intdiv($h * 60 + $m, $len) * $len;

        return sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
    }

    /** Couverts déjà pris sur le créneau. */
    public function used(Restaurant $r, string $date, string $slot, ?int $ignoreId = null): int
    {
        $len = max(15, (int) $r->slot_minutes ?: 30);
        $end = CarbonImmutable::parse($date.' '.$slot)->addMinutes($len)->format('H:i');

        return (int) BookingRequest::query()
            ->where('bookable_type', BookingRequest::TYPE_RESTAURANT)
            ->where('bookable_id', $r->id)
            ->whereDate('booking_date', $date)
            ->whereIn('status', [BookingRequest::STATUS_NEW, BookingRequest::STATUS_CONFIRMED])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('booking_time', '>=', $slot)
            ->where('booking_time', '<', $end)
            ->sum('party_size');
    }

    public function remaining(Restaurant $r, string $date, string $time): int
    {
        return max(0, (int) $r->table_capacity - $this->used($r, $date, $this->slotStart($r, $time)));
    }

    /**
     * Créneaux du jour avec places restantes (les créneaux passés du jour même sont exclus).
     *
     * @return array<int, array{time: string, remaining: int}>
     */
    public function day(Restaurant $r, string $date): array
    {
        $len = max(15, (int) $r->slot_minutes ?: 30);
        $day = CarbonImmutable::parse($date, 'Africa/Abidjan');
        $services = $this->services($r, (int) $day->dayOfWeek);
        $now = CarbonImmutable::now('Africa/Abidjan');
        $slots = [];
        foreach ($services as [$open, $close]) {
            // Dernière réservation possible une heure avant la fermeture.
            $t = CarbonImmutable::parse($date.' '.$open);
            $last = CarbonImmutable::parse($date.' '.$close)->subMinutes(60);
            if ($last < $t) $last = $t;
            for (; $t <= $last; $t = $t->addMinutes($len)) {
                if ($day->isSameDay($now) && $t->format('H:i') <= $now->format('H:i')) continue;
                $slots[] = ['time' => $t->format('H:i'), 'remaining' => $this->remaining($r, $date, $t->format('H:i'))];
            }
        }

        return $slots;
    }

    /** Créneaux les plus proches de l'heure demandée ayant assez de places. */
    public function suggestions(Restaurant $r, string $date, string $time, int $party, int $max = 3): array
    {
        return collect($this->day($r, $date))
            ->filter(fn ($s) => $s['remaining'] >= $party)
            ->sortBy(fn ($s) => abs(strtotime($date.' '.$s['time']) - strtotime($date.' '.$time)))
            ->take($max)
            ->pluck('time')
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Une table peut-elle être réservée à cette heure ? Mêmes règles que les créneaux proposés
     * (day()) : pendant le service, et au plus tard une heure avant la fermeture.
     */
    public function isOpenAt(Restaurant $r, string $date, string $time): bool
    {
        $time = substr($time, 0, 5);
        foreach ($this->services($r, (int) CarbonImmutable::parse($date)->dayOfWeek) as [$open, $close]) {
            $last = CarbonImmutable::parse($date.' '.$close)->subMinutes(60)->format('H:i');
            if ($last < $open) $last = $open;
            if ($time >= $open && $time <= $last) return true;
        }

        return false;
    }

    /* ── Plan de salle : tables précises ─────────────────────────────────── */

    /** Durée d'occupation d'une table réservée (minutes). */
    public function tableDuration(Restaurant $r): int
    {
        return max(30, (int) ($r->table_duration_minutes ?: 90));
    }

    /**
     * La table est-elle libre à cette heure ? Elle est occupée si une réservation (en attente ou
     * confirmée) commence moins d'une « durée d'occupation » avant ou après l'heure demandée.
     */
    public function isTableFree(\App\Models\RestaurantTable $table, string $date, string $time, ?int $ignoreId = null): bool
    {
        $duration = $this->tableDuration($table->restaurant);
        $at = $this->minutes($time);

        return ! BookingRequest::query()
            ->where('bookable_type', BookingRequest::TYPE_RESTAURANT)
            ->where('restaurant_table_id', $table->id)
            ->whereDate('booking_date', $date)
            ->whereIn('status', [BookingRequest::STATUS_NEW, BookingRequest::STATUS_CONFIRMED])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereNotNull('booking_time')
            ->pluck('booking_time')
            ->contains(fn ($t) => abs($this->minutes((string) $t) - $at) < $duration);
    }

    /**
     * Plan de salle pour une date, une heure et un nombre de couverts :
     * chaque table active avec son état — libre, réservée (grisée) ou trop petite.
     *
     * @return list<array{id:int, label:string, seats:int, zone:string, shape:string, status:string}>
     */
    public function tablePlan(Restaurant $r, string $date, ?string $time, int $party): array
    {
        $tables = $r->tables()->active()->get()->each->setRelation('restaurant', $r);

        return $tables->map(fn ($t) => [
            'id' => $t->id,
            'label' => $t->label,
            'seats' => $t->seats,
            'zone' => $t->zone,
            'shape' => $t->shape,
            'status' => match (true) {
                $time && ! $this->isTableFree($t, $date, $time) => 'booked',
                $t->seats < $party => 'small',
                default => 'free',
            },
        ])->values()->all();
    }

    private function minutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', substr($time, 0, 5)) + [0, 0]);

        return $h * 60 + $m;
    }

    /** Le restaurant est-il ouvert à cette heure (commande à emporter ou en livraison) ? */
    public function isServing(Restaurant $r, string $date, string $time): bool
    {
        $time = substr($time, 0, 5);
        foreach ($this->services($r, (int) CarbonImmutable::parse($date)->dayOfWeek) as [$open, $close]) {
            if ($time >= $open && $time < $close) return true;
        }

        return false;
    }

    /** @return array<int, array{0: string, 1: string}> plages d'ouverture du jour (0 = dimanche) */
    private function services(Restaurant $r, int $dow): array
    {
        if (! $r->provider_id) return self::DEFAULT_SERVICES;
        $hours = ProviderHour::where('provider_id', $r->provider_id)->get();
        if ($hours->isEmpty()) return self::DEFAULT_SERVICES;
        $h = $hours->firstWhere('day_of_week', $dow);
        if (! $h || $h->is_closed || ! $h->open_time || ! $h->close_time) return [];
        $close = substr((string) $h->close_time, 0, 5);

        return [[substr((string) $h->open_time, 0, 5), $close <= substr((string) $h->open_time, 0, 5) ? '23:59' : $close]];
    }
}
