<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Départ programmé d'une ligne régulière : date/heure, sièges numérotés (rangées de `seats_per_row`), prix. */
class TransportTrip extends Model
{
    protected $fillable = ['transport_offer_id', 'transport_vehicle_id', 'departs_at', 'seats_total', 'seats_per_row', 'price_xof', 'is_active'];

    protected function casts(): array
    {
        return ['departs_at' => 'datetime', 'seats_total' => 'integer', 'seats_per_row' => 'integer', 'price_xof' => 'integer', 'is_active' => 'boolean'];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(TransportOffer::class, 'transport_offer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TransportVehicle::class, 'transport_vehicle_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(TransportBooking::class);
    }

    public function price(): int
    {
        return (int) ($this->price_xof ?: $this->offer?->price_xof);
    }

    /** @return array<int, int> Sièges pris (réservations payées, ou en cours de paiement depuis moins de 30 min). */
    public function takenSeats(?int $ignoreBookingId = null): array
    {
        return $this->bookings()
            ->where(fn ($q) => $q->where('status', TransportBooking::STATUS_PAID)
                ->orWhere(fn ($p) => $p->where('status', TransportBooking::STATUS_PENDING_PAYMENT)->where('created_at', '>=', now()->subMinutes(30))))
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->pluck('seats')->flatten()->map(fn ($s) => (int) $s)->unique()->values()->all();
    }

    public function seatsLeft(): int
    {
        return max(0, $this->seats_total - count($this->takenSeats()));
    }
}
