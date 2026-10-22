<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ArtworkOrder extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    /** Paiement en plusieurs fois en cours : acompte versé, œuvre bloquée, remise une fois soldée. */
    public const STATUS_RESERVED = 'reserved';

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
        self::STATUS_RESERVED => 'Réservée — paiement en plusieurs fois',
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
        'artwork_edition_id', 'edition_label', 'edition_number', 'edition_size', 'artwork_offer_id',
        'delivery_mode', 'delivery_address', 'delivery_city', 'delivery_fee_xof', 'insurance_xof', 'carrier', 'tracking_number',
        'payment_plan', 'amount_paid_xof', 'installments', 'pending_installment', 'received_at',
        'return_status', 'return_reason', 'return_requested_at',
    ];

    public const DELIVERY_MODES = ['pickup' => 'Retrait à l\'atelier', 'abidjan' => 'Livraison à Abidjan', 'country' => 'Livraison en Côte d\'Ivoire (hors Abidjan)'];

    /** Délai de retour après réception confirmée (garantie « satisfait ou remboursé »). */
    public const RETURN_DAYS = 7;

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
            'delivery_fee_xof' => 'integer',
            'insurance_xof' => 'integer',
            'amount_paid_xof' => 'integer',
            'installments' => 'array',
            'received_at' => 'datetime',
            'return_requested_at' => 'datetime',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(ArtworkEdition::class, 'artwork_edition_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(ArtworkOffer::class, 'artwork_offer_id');
    }

    public function deliveryLabel(): ?string
    {
        return $this->delivery_mode ? __(self::DELIVERY_MODES[$this->delivery_mode] ?? $this->delivery_mode) : null;
    }

    /** Prochaine échéance non réglée d'un paiement en plusieurs fois. */
    public function nextInstallment(): ?array
    {
        return collect($this->installments ?? [])->first(fn ($i) => empty($i['paid_at']));
    }

    /** L'acheteur peut demander un retour : reçue depuis moins de 7 jours, artiste qui accepte les retours. */
    public function canRequestReturn(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED], true) && $this->received_at && ! $this->return_status
            && $this->received_at->copy()->addDays(self::RETURN_DAYS)->isFuture()
            && (ArtistProfile::where('provider_id', $this->provider_id)->value('returns_accepted') ?? true);
    }

    /** Fin du délai de retour : la part de l'artiste peut être libérée. */
    public function returnWindowEndsAt(): ?\Carbon\Carbon
    {
        return $this->received_at?->copy()->addDays(self::RETURN_DAYS);
    }

    protected static function booted(): void
    {
        static::creating(fn (ArtworkOrder $order) => $order->uuid ??= (string) Str::uuid());
    }

    /** Œuvre réellement vendue : un certificat d'authenticité peut être délivré. */
    public function hasCertificate(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_SHIPPED, self::STATUS_DELIVERED], true);
    }

    /** Code de vérification du certificat (non devinable, stable pour la commande). */
    public function certificateCode(): string
    {
        return strtoupper(substr(hash_hmac('sha256', 'art-cert|'.$this->id, (string) config('app.key')), 0, 12));
    }

    public function certificateNumber(): string
    {
        return 'TI-ART-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function certificatePdfUrl(): string
    {
        return route('art.certificate.pdf', [$this->id, $this->certificateCode()]);
    }

    public function certificateVerifyUrl(): string
    {
        return route('art.certificate.verify', [$this->id, $this->certificateCode()]);
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
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }
}
