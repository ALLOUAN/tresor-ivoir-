<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Session de visite groupée — capacité indépendante par session (une réservation
 * sur une session ne touche jamais le stock d'une autre, même site, même jour).
 * Le nombre de places restantes n'est jamais mis en cache : toujours recalculé à
 * partir des visites actives, pour ne jamais dérailler entre deux réservations.
 */
class TouristVisitSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tourist_experience_id',
        'session_date',
        'period_label',
        'capacity',
        'price_per_person_xof',
        'conditions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'capacity' => 'integer',
            'price_per_person_xof' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(TouristExperience::class, 'tourist_experience_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(TouristVisit::class, 'tourist_visit_session_id');
    }

    /**
     * Places déjà occupées : une visite "pending_payment" bloque déjà la place (elle
     * est réservée dès la création, pas seulement au paiement confirmé — voir
     * TouristVisitController::initiateVisit()) ; failed/cancelled/refunded la libèrent.
     */
    public function bookedSeats(): int
    {
        return (int) $this->visits()
            ->whereIn('status', [TouristVisit::STATUS_PENDING_PAYMENT, TouristVisit::STATUS_PAID])
            ->sum('participants_count');
    }

    public function remainingSeats(): int
    {
        return max(0, $this->capacity - $this->bookedSeats());
    }

    public function isFull(): bool
    {
        return $this->remainingSeats() <= 0;
    }
}
