<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequest extends Model
{
    public const TYPE_RESTAURANT = 'restaurant';

    /** Commande de plats (à emporter ou en livraison), distincte de la réservation de table. */
    public const TYPE_RESTAURANT_ORDER = 'restaurant_order';

    public const FULFILLMENT_LABELS = ['pickup' => 'À emporter', 'delivery' => 'Livraison'];

    public const TYPE_LEISURE = 'leisure';

    public const TYPE_TOUR = 'tour';

    public const TYPE_BUNDLE = 'bundle';

    public const TYPE_TRANSPORT = 'transport';

    public const TYPE_ART = 'art';

    /** @var array<string, array{label: string, model: class-string<Model>, unit: string}> */
    public const TYPES = [
        self::TYPE_RESTAURANT => ['label' => 'Table au restaurant', 'model' => Restaurant::class, 'unit' => 'couverts'],
        self::TYPE_RESTAURANT_ORDER => ['label' => 'Commande au restaurant', 'model' => Restaurant::class, 'unit' => 'plats'],
        self::TYPE_LEISURE => ['label' => 'Activité de loisir', 'model' => LeisureVenue::class, 'unit' => 'participants'],
        self::TYPE_TOUR => ['label' => 'Circuit', 'model' => TravelAgency::class, 'unit' => 'voyageurs'],
        self::TYPE_BUNDLE => ['label' => 'Offre groupée', 'model' => Bundle::class, 'unit' => 'voyageurs'],
        self::TYPE_TRANSPORT => ['label' => 'Transport', 'model' => TransportCompany::class, 'unit' => 'passagers'],
        self::TYPE_ART => ['label' => 'Œuvre sur mesure', 'model' => Provider::class, 'unit' => 'œuvre(s)'],
    ];

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_REFUNDED = 'refunded';

    /** @var array<string, string> */
    public const PAYMENT_LABELS = [
        self::PAYMENT_UNPAID => 'À régler sur place',
        self::PAYMENT_PENDING => 'Paiement en cours',
        self::PAYMENT_PAID => 'Payée en ligne',
        self::PAYMENT_FAILED => 'Paiement échoué',
        self::PAYMENT_REFUNDED => 'Remboursée',
    ];

    public const STATUS_NEW = 'new';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_NEW => 'En attente de réponse',
        self::STATUS_CONFIRMED => 'Confirmée',
        self::STATUS_DECLINED => 'Refusée',
        self::STATUS_CANCELLED => 'Annulée',
        self::STATUS_EXPIRED => 'Expirée',
    ];

    protected $fillable = [
        'provider_id', 'user_id', 'bookable_type', 'bookable_id', 'bookable_name', 'item_name', 'item_id', 'restaurant_table_id', 'order_items', 'fulfillment', 'is_custom_quote', 'pickup_location', 'dropoff_location',
        'booking_date', 'booking_time', 'party_size', 'full_name', 'email', 'phone', 'message',
        'status', 'provider_note', 'answered_at',
        'amount_due_xof', 'commission_percent', 'commission_amount_xof', 'payment_status', 'payment_method',
        'gateway_txn_id', 'paid_at', 'refund_due_xof', 'cancelled_at', 'bundle_id',
        'promo_code', 'promo_discount_xof',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'answered_at' => 'datetime',
            'party_size' => 'integer',
            'is_custom_quote' => 'boolean',
            'order_items' => 'array',
            'amount_due_xof' => 'integer',
            'commission_amount_xof' => 'integer',
            'refund_due_xof' => 'integer',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReferenceAttribute(): string
    {
        return 'DEM-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function typeLabel(): string
    {
        return __(self::TYPES[$this->bookable_type]['label'] ?? $this->bookable_type);
    }

    public function unitLabel(): string
    {
        return __(self::TYPES[$this->bookable_type]['unit'] ?? 'personnes');
    }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function paymentLabel(): string
    {
        return __(self::PAYMENT_LABELS[$this->payment_status] ?? $this->payment_status);
    }

    /** Le client peut-il payer en ligne maintenant ? (confirmée, montant fixé, pas encore payée, date à venir) */
    public function canBePaidOnline(): bool
    {
        return $this->status === self::STATUS_CONFIRMED
            && (int) $this->amount_due_xof > 0
            && in_array($this->payment_status, [self::PAYMENT_UNPAID, self::PAYMENT_PENDING, self::PAYMENT_FAILED], true)
            && now()->startOfDay()->lte($this->booking_date);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_CONFIRMED], true)
            && now()->startOfDay()->lte($this->booking_date);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}
