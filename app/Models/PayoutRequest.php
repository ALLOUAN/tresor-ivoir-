<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PayoutRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PENDING_VERIFICATION = 'pending_verification';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PAID = 'paid';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'En attente',
        self::STATUS_PENDING_VERIFICATION => 'Vérification en cours',
        self::STATUS_SUSPENDED => 'Suspendue',
        self::STATUS_APPROVED => 'Approuvée',
        self::STATUS_REJECTED => 'Refusée',
        self::STATUS_PAID => 'Payée',
    ];

    public const DECISION_AUTO_APPROVED = 'auto_approved';

    public const DECISION_STEP_UP_REQUIRED = 'step_up_required';

    public const DECISION_SUSPENDED = 'suspended';

    public const DECISION_MANUAL_REVIEW = 'manual_review';

    /** @var array<string, string> */
    public const DECISION_LABELS = [
        self::DECISION_AUTO_APPROVED => 'Auto-approuvé',
        self::DECISION_STEP_UP_REQUIRED => 'Vérification demandée',
        self::DECISION_SUSPENDED => 'Suspendu par le moteur',
        self::DECISION_MANUAL_REVIEW => 'Examen manuel',
    ];

    public const RISK_LOW = 'low';

    public const RISK_MEDIUM = 'medium';

    public const RISK_HIGH = 'high';

    public const METHOD_ORANGE_MONEY = 'orange_money';

    public const METHOD_MTN_MOMO = 'mtn_momo';

    public const METHOD_MOOV_MONEY = 'moov_money';

    public const METHOD_WAVE = 'wave';

    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    /** @var array<string, string> */
    public const METHOD_LABELS = [
        self::METHOD_ORANGE_MONEY => 'Orange Money',
        self::METHOD_MTN_MOMO => 'MTN Mobile Money',
        self::METHOD_MOOV_MONEY => 'Moov Money',
        self::METHOD_WAVE => 'Wave',
        self::METHOD_BANK_TRANSFER => 'Virement bancaire',
    ];

    /** Réseaux mobile money — tous sauf le virement bancaire (le champ "numéro" y devient un compte/IBAN). */
    public const MOBILE_MONEY_METHODS = [
        self::METHOD_ORANGE_MONEY,
        self::METHOD_MTN_MOMO,
        self::METHOD_MOOV_MONEY,
        self::METHOD_WAVE,
    ];

    protected $fillable = [
        'uuid',
        'provider_id',
        'user_id',
        'wallet_id',
        'amount_xof',
        'method',
        'payout_destination',
        'status',
        'risk_score',
        'risk_level',
        'risk_flags',
        'decision',
        'ip_address',
        'user_agent',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
        'paid_at',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'integer',
            'risk_score' => 'integer',
            'risk_flags' => 'array',
            'reviewed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (PayoutRequest $r) => $r->uuid ??= (string) Str::uuid());
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(PayoutAuditLog::class)->orderBy('created_at');
    }

    public function labelForStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function labelForDecision(): ?string
    {
        return $this->decision ? (self::DECISION_LABELS[$this->decision] ?? $this->decision) : null;
    }

    public function labelForMethod(): string
    {
        return self::METHOD_LABELS[$this->method] ?? $this->method;
    }

    public function holderName(): string
    {
        return $this->provider_id ? (string) $this->provider?->name : (string) $this->user?->full_name;
    }

    /** L'utilisateur (compte de connexion) propriétaire de la demande — prestataire ou client. */
    public function holderUser(): ?User
    {
        return $this->provider_id ? $this->provider?->user : $this->user;
    }

    public function isForClient(): bool
    {
        return $this->user_id !== null;
    }

    public function isMobileMoney(): bool
    {
        return in_array($this->method, self::MOBILE_MONEY_METHODS, true);
    }
}
