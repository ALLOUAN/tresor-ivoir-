<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Commande sur mesure : brief du client → devis de l'artiste → acompte → esquisse (retouches) →
 * esquisse validée → solde → œuvre livrée.
 */
class ArtCommission extends Model
{
    public const MAX_REVISIONS = 2;

    public const STATUS_LABELS = [
        'requested' => 'Demande envoyée — en attente du devis',
        'quoted' => 'Devis reçu — acompte à verser',
        'declined' => 'Refusée par l\'artiste',
        'deposit_paid' => 'Acompte versé — esquisse en préparation',
        'sketch_sent' => 'Esquisse à valider',
        'revision' => 'Retouches demandées',
        'sketch_approved' => 'Esquisse validée — solde à régler',
        'balance_paid' => 'Soldée — œuvre en réalisation',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
    ];

    protected $fillable = [
        'provider_id', 'user_id', 'subject', 'brief', 'format', 'medium', 'palette', 'budget_xof', 'wanted_by', 'references',
        'status', 'quote_xof', 'quote_days', 'quote_message', 'deposit_xof', 'amount_paid_xof', 'commission_percent',
        'sketches', 'sketch_note', 'revision_note', 'revisions_used', 'pending_stage', 'gateway_txn_id', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'budget_xof' => 'integer', 'wanted_by' => 'date', 'references' => 'array', 'quote_xof' => 'integer', 'quote_days' => 'integer',
            'deposit_xof' => 'integer', 'amount_paid_xof' => 'integer', 'commission_percent' => 'float', 'sketches' => 'array',
            'revisions_used' => 'integer', 'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (ArtCommission $c) => $c->uuid ??= (string) Str::uuid());
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReferenceAttribute(): string
    {
        return 'SUR-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function balanceXof(): int
    {
        return max(0, (int) $this->quote_xof - (int) $this->amount_paid_xof);
    }

    /** Étape de paiement attendue du client, ou null. */
    public function dueStage(): ?string
    {
        return match ($this->status) {
            'quoted' => 'deposit',
            'sketch_approved' => 'balance',
            default => null,
        };
    }

    public function dueAmount(): int
    {
        return match ($this->dueStage()) {
            'deposit' => (int) $this->deposit_xof,
            'balance' => $this->balanceXof(),
            default => 0,
        };
    }
}
