<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Offre d'un acheteur sur une œuvre : acceptée ou contre-proposée par l'artiste, payable 48 h au prix convenu. */
class ArtworkOffer extends Model
{
    public const VALID_HOURS = 48;

    public const STATUS_LABELS = [
        'pending' => 'En attente de l\'artiste',
        'accepted' => 'Acceptée — à payer',
        'countered' => 'Contre-proposition de l\'artiste',
        'declined' => 'Refusée',
        'expired' => 'Expirée',
        'paid' => 'Payée',
        'withdrawn' => 'Retirée',
    ];

    protected $fillable = ['artwork_id', 'provider_id', 'user_id', 'amount_xof', 'counter_amount_xof', 'message', 'status', 'expires_at', 'responded_at'];

    protected function casts(): array
    {
        return ['amount_xof' => 'integer', 'counter_amount_xof' => 'integer', 'expires_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Prix à payer si l'offre est payable maintenant (acceptée, ou contre-offre acceptée par l'acheteur). */
    public function payablePrice(): ?int
    {
        if ($this->status !== 'accepted' || ($this->expires_at && $this->expires_at->isPast())) {
            return null;
        }

        return (int) ($this->counter_amount_xof ?: $this->amount_xof);
    }

    public function statusLabel(): string
    {
        if (in_array($this->status, ['accepted', 'countered'], true) && $this->expires_at?->isPast()) {
            return __(self::STATUS_LABELS['expired']);
        }

        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }
}
