<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ArtworkOrder extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    /**
     * Paiement effectivement encaissé par CinetPay, mais le stock de la pièce était
     * déjà épuisé au moment de la confirmation (survente — deux acheteurs simultanés
     * sur une pièce unique). Distinct de STATUS_PAID : ne doit jamais être présenté
     * comme une vente normale à expédier, et nécessite un remboursement manuel admin.
     */
    public const STATUS_OVERSOLD = 'oversold';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Paiement en attente',
        self::STATUS_PAID => 'Payée',
        self::STATUS_SHIPPED => 'Expédiée',
        self::STATUS_DELIVERED => 'Livrée',
        self::STATUS_CANCELLED => 'Annulée',
        self::STATUS_REFUNDED => 'Remboursée',
        self::STATUS_OVERSOLD => 'Survente — remboursement dû',
    ];

    protected $fillable = [
        'artwork_id', 'provider_id', 'buyer_user_id',
        'unit_price_xof', 'amount_total_xof',
        'commission_percent', 'commission_amount_xof', 'artist_net_amount_xof',
        'currency', 'status', 'gateway', 'gateway_txn_id',
        'paid_at', 'shipped_at', 'delivered_at',
        'buyer_name', 'buyer_email', 'buyer_phone',
        'ip_address', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_xof' => 'integer',
            'amount_total_xof' => 'integer',
            'commission_percent' => 'float',
            'commission_amount_xof' => 'integer',
            'artist_net_amount_xof' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (ArtworkOrder $order) => $order->uuid ??= (string) Str::uuid());
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    /** Référence lisible affichée à l'acheteur (ex: ART-000042). */
    public function getReferenceAttribute(): string
    {
        return 'ART-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function labelForStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
