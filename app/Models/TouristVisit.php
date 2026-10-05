<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

/**
 * Réservation de visite d'un site touristique (individuelle, guidée ou groupée).
 * Calquée sur ArtworkOrder (paiement intégral immédiat, pas d'acompte/solde sur
 * place, pas de sous-table de paiements) plutôt que sur Reservation (hôtels).
 */
class TouristVisit extends Model
{
    public const TYPE_INDIVIDUAL = 'individual';

    public const TYPE_GUIDED = 'guided';

    public const TYPE_GROUP = 'group';

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_INDIVIDUAL => 'Visite individuelle',
        self::TYPE_GUIDED => 'Visite avec guide',
        self::TYPE_GROUP => 'Visite groupée',
    ];

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_FAILED = 'failed';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Paiement en attente',
        self::STATUS_PAID => 'Confirmée',
        self::STATUS_CANCELLED => 'Annulée',
        self::STATUS_REFUNDED => 'Remboursée',
        self::STATUS_FAILED => 'Échouée',
    ];

    protected $fillable = [
        'tourist_experience_id', 'provider_id', 'tourist_visit_session_id', 'user_id',
        'experience_name', 'visit_type', 'with_guide', 'participants_count',
        'desired_date', 'session_date', 'session_time_label',
        'unit_price_xof', 'guide_supplement_xof', 'amount_total_xof',
        'commission_percent', 'commission_amount_xof', 'provider_net_amount_xof', 'currency',
        'status', 'gateway', 'gateway_txn_id', 'paid_at', 'cancelled_at',
        'full_name', 'email', 'phone', 'message', 'ip_address', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'with_guide' => 'boolean',
            'participants_count' => 'integer',
            'desired_date' => 'date',
            'session_date' => 'date',
            'unit_price_xof' => 'integer',
            'guide_supplement_xof' => 'integer',
            'amount_total_xof' => 'integer',
            'commission_percent' => 'float',
            'commission_amount_xof' => 'integer',
            'provider_net_amount_xof' => 'integer',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (TouristVisit $visit) => $visit->uuid ??= (string) Str::uuid());
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(TouristExperience::class, 'tourist_experience_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TouristVisitSession::class, 'tourist_visit_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReferenceAttribute(): string
    {
        return 'VIS-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function labelForStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function labelForType(): string
    {
        return self::TYPE_LABELS[$this->visit_type] ?? $this->visit_type;
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public function confirmationUrl(): string
    {
        return URL::temporarySignedRoute('tourist-visit.confirmation', now()->addDays(30), ['touristVisit' => $this->id]);
    }
}
