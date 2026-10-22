<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    /** @var array<string, string> */
    public const REASONS = [
        'closed' => 'Établissement fermé ou introuvable à l\'arrivée',
        'not_as_described' => 'Chambre ou prestation non conforme à l\'annonce',
        'refused' => 'Réservation non honorée par l\'établissement',
        'overcharged' => 'Montant demandé sur place supérieur au prix affiché',
        'other' => 'Autre problème',
    ];

    public const STATUS_OPEN = 'open';

    public const STATUS_RESOLVED_CLIENT = 'resolved_client';

    public const STATUS_RESOLVED_PROVIDER = 'resolved_provider';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_OPEN => 'En cours d\'examen',
        self::STATUS_RESOLVED_CLIENT => 'Résolu en faveur du client',
        self::STATUS_RESOLVED_PROVIDER => 'Résolu en faveur de l\'établissement',
    ];

    /** Délai pour signaler un problème après le départ prévu. */
    public const OPEN_WINDOW_DAYS = 14;

    protected $fillable = [
        'reservation_id', 'user_id', 'provider_id', 'reason', 'description', 'status',
        'provider_response', 'provider_responded_at', 'resolution', 'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['provider_responded_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reasonLabel(): string
    {
        return __(self::REASONS[$this->reason] ?? $this->reason);
    }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /** Le client peut-il signaler un problème sur cette réservation ? (payée, séjour commencé, dans le délai, aucun litige) */
    public static function canOpenFor(Reservation $reservation): bool
    {
        return $reservation->payment_status === Reservation::PAYMENT_DEPOSIT_PAID
            && now()->startOfDay()->gte($reservation->check_in)
            && now()->startOfDay()->lte($reservation->check_out->copy()->addDays(self::OPEN_WINDOW_DAYS))
            && ! static::where('reservation_id', $reservation->id)->exists();
    }
}
