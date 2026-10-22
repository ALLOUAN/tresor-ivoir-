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

    /** Type de visite personnalisé (TouristVisitType) ; son nom est figé dans visit_type_name. */
    public const TYPE_CUSTOM = 'custom';

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_INDIVIDUAL => 'Visite individuelle',
        self::TYPE_GUIDED => 'Visite avec guide',
        self::TYPE_GROUP => 'Visite groupée',
        self::TYPE_CUSTOM => 'Type de visite',
    ];

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    /** Réservée, à régler sur place (« Réserver maintenant, payer plus tard ») : billet émis, rien d'encaissé. */
    public const STATUS_BOOKED = 'booked';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_FAILED = 'failed';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Paiement en attente',
        self::STATUS_PAID => 'Confirmée',
        self::STATUS_BOOKED => 'Réservée — à régler sur place',
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
        'ticket_code', 'checked_in_at', 'checked_in_by',
        'children_count', 'residents_count',
        'tourist_visit_type_id', 'visit_type_name',
        'visit_time', 'tier_breakdown', 'addons', 'language', 'pay_on_site', 'price_adjust_percent', 'cancellable_until',
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
            'checked_in_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
            'tier_breakdown' => 'array',
            'addons' => 'array',
            'pay_on_site' => 'boolean',
            'price_adjust_percent' => 'integer',
            'cancellable_until' => 'datetime',
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

    public function visitType(): BelongsTo
    {
        return $this->belongsTo(TouristVisitType::class, 'tourist_visit_type_id');
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
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function labelForType(): string
    {
        if ($this->visit_type_name) {
            return $this->visit_type_name;
        }

        return __(self::TYPE_LABELS[$this->visit_type] ?? $this->visit_type);
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    /** Billet valable : payée, ou réservée à régler sur place. */
    public function hasValidTicket(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_BOOKED], true);
    }

    /** Date et heure de début de la visite (fuseau d'Abidjan), si connues. */
    public function startsAt(): ?\Carbon\Carbon
    {
        $date = $this->session_date ?? $this->desired_date;
        if (! $date) {
            return null;
        }
        $start = \Carbon\Carbon::parse($date->toDateString(), 'Africa/Abidjan');

        return $this->visit_time ? $start->setTimeFromTimeString($this->visit_time) : $start;
    }

    /** Annulable par le client : billet valable, délai d'annulation gratuite non dépassé, pas encore entré. */
    public function isCancellableByClient(): bool
    {
        return $this->hasValidTicket() && ! $this->checked_in_at
            && $this->cancellable_until && now()->lt($this->cancellable_until);
    }

    /** Résumé des tarifs : « 2 × Adulte, 1 × Enfant ». */
    public function tiersSummary(): ?string
    {
        $lines = collect($this->tier_breakdown ?? [])->filter(fn ($t) => ($t['qty'] ?? 0) > 0);

        return $lines->isEmpty() ? null : $lines->map(fn ($t) => $t['qty'].' × '.$t['label'])->join(', ');
    }

    /** Code du billet (QR), créé à la première demande pour une visite payée ou réservée. */
    public function ticketCode(): ?string
    {
        if (! $this->hasValidTicket()) {
            return null;
        }
        if (! $this->ticket_code) {
            do {
                $code = strtoupper(\Illuminate\Support\Str::random(10));
            } while (static::where('ticket_code', $code)->exists());
            $this->forceFill(['ticket_code' => $code])->save();
        }

        return $this->ticket_code;
    }

    /** Adresse encodée dans le QR : ouverte par le prestataire, elle affiche le billet et permet de valider l'entrée. */
    public function ticketCheckUrl(): ?string
    {
        return ($code = $this->ticketCode()) ? route('provider.tickets.show', $code) : null;
    }

    public function confirmationUrl(): string
    {
        return URL::temporarySignedRoute('tourist-visit.confirmation', now()->addDays(30), ['touristVisit' => $this->id]);
    }
}
