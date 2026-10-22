<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Véhicule de la flotte d'une compagnie ; indisponible pendant sa période d'entretien. */
class TransportVehicle extends Model
{
    protected $fillable = ['provider_id', 'name', 'category', 'plate', 'seats', 'luggage', 'has_ac', 'photo', 'unavailable_from', 'unavailable_to', 'is_active'];

    protected function casts(): array
    {
        return ['seats' => 'integer', 'luggage' => 'integer', 'has_ac' => 'boolean', 'is_active' => 'boolean', 'unavailable_from' => 'date', 'unavailable_to' => 'date'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function categoryLabel(): string
    {
        return __(TransportOffer::VEHICLE_CATEGORIES[$this->category] ?? $this->category);
    }

    /** En entretien sur tout ou partie de [début, fin] ? */
    public function inMaintenance(\Carbon\CarbonInterface $start, \Carbon\CarbonInterface $end): bool
    {
        return $this->unavailable_from && $this->unavailable_to
            && $this->unavailable_from->startOfDay()->lte($end) && $this->unavailable_to->copy()->endOfDay()->gte($start);
    }
}
