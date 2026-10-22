<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Départ programmé d'un circuit : date, places, prix éventuel propre à ce départ, minimum de voyageurs
 * pour que le départ soit garanti, annulation du départ (minimum non atteint ou décision de l'agence).
 */
class TourDeparture extends Model
{
    protected $fillable = ['tour_package_id', 'departure_date', 'seats_total', 'min_participants', 'price_xof', 'is_active', 'guaranteed_at', 'cancelled_at', 'cancel_reason'];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date', 'is_active' => 'boolean', 'seats_total' => 'integer', 'price_xof' => 'integer',
            'min_participants' => 'integer', 'guaranteed_at' => 'datetime', 'cancelled_at' => 'datetime',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(TourBooking::class);
    }

    /**
     * Places déjà prises : demandes classiques en attente ou confirmées à cette date, plus les réservations
     * en ligne (payées, acompte versé, ou en cours de paiement depuis moins de 30 minutes).
     */
    public function seatsTaken(?int $ignoreId = null): int
    {
        $requests = (int) BookingRequest::query()
            ->where('bookable_type', BookingRequest::TYPE_TOUR)
            ->where('item_id', $this->tour_package_id)
            ->whereDate('booking_date', $this->departure_date)
            ->whereIn('status', [BookingRequest::STATUS_NEW, BookingRequest::STATUS_CONFIRMED])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->sum('party_size');

        return $requests + $this->onlineTravelers(includePending: true);
    }

    /** Voyageurs inscrits en ligne (acompte ou totalité versés) ; avec les paiements en cours si demandé. */
    public function onlineTravelers(bool $includePending = false): int
    {
        return (int) $this->bookings()
            ->where(fn ($q) => $q->whereIn('status', [TourBooking::STATUS_DEPOSIT_PAID, TourBooking::STATUS_PAID])
                ->when($includePending, fn ($q2) => $q2->orWhere(fn ($q3) => $q3->where('status', TourBooking::STATUS_PENDING_PAYMENT)->where('created_at', '>=', now()->subMinutes(30)))))
            ->sum('travelers_count');
    }

    public function seatsLeft(): int
    {
        return max(0, $this->seats_total - $this->seatsTaken());
    }

    public function pricePerPerson(): int
    {
        return (int) ($this->price_xof ?: $this->tour?->price_xof);
    }

    public function minimum(): ?int
    {
        return $this->min_participants ?: ($this->tour?->min_participants ?: null);
    }

    /** Garanti : confirmé par l'agence, ou minimum atteint (ou pas de minimum). */
    public function isGuaranteed(): bool
    {
        return ! $this->cancelled_at && ($this->guaranteed_at !== null || $this->minimum() === null || $this->onlineTravelers() >= $this->minimum());
    }

    /** Voyageurs qui manquent pour garantir le départ. */
    public function missingForGuarantee(): int
    {
        return $this->isGuaranteed() ? 0 : max(0, (int) $this->minimum() - $this->onlineTravelers());
    }

    /** Date limite à laquelle le départ est confirmé ou annulé si le minimum n'est pas atteint. */
    public function decisionDate(): \Carbon\Carbon
    {
        return $this->departure_date->copy()->subDays((int) ($this->tour?->guarantee_days ?? 7));
    }

    public function isOpenForBooking(): bool
    {
        return $this->is_active && ! $this->cancelled_at && $this->departure_date->gte(now()->startOfDay());
    }
}
