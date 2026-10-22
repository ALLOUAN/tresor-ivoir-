<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\LeisurePackage;
use App\Models\LeisureResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Disponibilités des activités : places du créneau (slot_capacity) et ressources partagées.
 * Une ressource (salle, terrain, lot de kayaks) est occupée par toute réservation — de n'importe quelle
 * activité ou formule qui l'utilise — dont l'horaire chevauche le créneau demandé.
 */
class LeisureAvailability
{
    /** Réservations qui occupent des places : confirmées, ou en cours de paiement depuis moins de 30 min. */
    public static function holding(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->where('status', ActivityBooking::STATUS_PAID)
            ->orWhere(fn ($p) => $p->where('status', ActivityBooking::STATUS_PENDING_PAYMENT)->where('created_at', '>=', now()->subMinutes(30))));
    }

    public function seatsTaken(Activity $activity, string $date, string $time, ?int $ignoreId = null): int
    {
        return (int) self::holding(ActivityBooking::query())
            ->where('activity_id', $activity->id)->whereDate('booking_date', $date)->where('booking_time', $time)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->sum('participants');
    }

    /** Exemplaires d'une ressource encore libres sur [début, début + durée). */
    public function resourceFree(LeisureResource $resource, string $date, string $time, int $minutes, ?int $ignoreId = null): int
    {
        $start = $this->minutes($time);
        $end = $start + max(1, $minutes);
        $activityIds = $resource->activities()->pluck('activities.id');
        $packageIds = LeisurePackage::where('leisure_resource_id', $resource->id)->pluck('id');

        $used = self::holding(ActivityBooking::query())
            ->whereDate('booking_date', $date)->whereNotNull('booking_time')
            ->where(fn ($q) => $q->whereIn('activity_id', $activityIds)->orWhereIn('leisure_package_id', $packageIds))
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->with('activity.resources')
            ->get()
            ->filter(function (ActivityBooking $b) use ($start, $end) {
                $s = $this->minutes($b->booking_time);
                $e = $s + max(1, (int) ($b->duration_minutes ?: 60));

                return $s < $end && $start < $e;
            })
            ->sum(function (ActivityBooking $b) use ($resource) {
                if ($b->leisure_package_id) {
                    return $resource->units; // une formule privatise la ressource
                }
                $pivot = $b->activity?->resources->firstWhere('id', $resource->id)?->pivot;

                return $pivot ? ($pivot->per_participant ? $b->participants * $pivot->units : $pivot->units) : 0;
            });

        return max(0, $resource->units - (int) $used);
    }

    /**
     * Participants maximum encore acceptés sur un créneau (places et ressources) ; null = illimité.
     */
    public function remaining(Activity $activity, string $date, string $time, ?int $ignoreId = null): ?int
    {
        $limits = [];
        if ($activity->slot_capacity) {
            $limits[] = max(0, $activity->slot_capacity - $this->seatsTaken($activity, $date, $time, $ignoreId));
        }
        foreach ($activity->resources->where('is_active', true) as $resource) {
            $free = $this->resourceFree($resource, $date, $time, (int) ($activity->duration_minutes ?: 60), $ignoreId);
            $units = max(1, (int) $resource->pivot->units);
            $limits[] = $resource->pivot->per_participant ? intdiv($free, $units) : ($free >= $units ? PHP_INT_MAX : 0);
        }
        $limits = array_filter($limits, fn ($l) => $l !== PHP_INT_MAX);

        return $limits === [] ? null : min($limits);
    }

    /**
     * Créneaux d'une date : places restantes, passé, jour fermé, ajustement de prix.
     *
     * @return array<int, array{time: string, remaining: ?int, available: bool, past: bool, adjust_percent: int}>
     */
    public function slotsFor(Activity $activity, string $date): array
    {
        $day = Carbon::parse($date, 'Africa/Abidjan')->startOfDay();
        $now = now('Africa/Abidjan');
        $open = $activity->isOpenOn($day);
        $activity->loadMissing('resources');

        return collect($activity->slots())->map(function (string $time) use ($activity, $date, $day, $now, $open) {
            $past = $day->copy()->setTimeFromTimeString($time)->lte($now);
            $remaining = $open ? $this->remaining($activity, $date, $time) : 0;

            return [
                'time' => $time,
                'remaining' => $remaining,
                'available' => $open && ! $past && ($remaining === null || $remaining > 0),
                'past' => $past,
                'closed' => ! $open,
                'adjust_percent' => $activity->adjustPercentFor($day, $time),
            ];
        })->values()->all();
    }

    /** Créneaux d'une formule : la ressource (salle) doit être libre pendant toute la durée. */
    public function packageSlotsFor(LeisurePackage $package, string $date): array
    {
        $day = Carbon::parse($date, 'Africa/Abidjan')->startOfDay();
        $now = now('Africa/Abidjan');

        return collect($package->slots())->map(function (string $time) use ($package, $date, $day, $now) {
            $past = $day->copy()->setTimeFromTimeString($time)->lte($now);
            $free = $package->resource ? $this->resourceFree($package->resource, $date, $time, $package->duration_minutes) >= $package->resource->units : true;
            $taken = ActivityBooking::query()->where('leisure_package_id', $package->id)->whereDate('booking_date', $date)->where('booking_time', $time)
                ->where(fn ($q) => self::holding($q))->exists();

            return ['time' => $time, 'available' => ! $past && $free && ! $taken, 'past' => $past];
        })->values()->all();
    }

    private function minutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time) + [0, 0]);

        return $h * 60 + $m;
    }
}
