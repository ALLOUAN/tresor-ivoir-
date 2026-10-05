<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    public const TYPE_DEPOSIT_COLLECTED = 'deposit_collected';

    public const TYPE_COMMISSION = 'commission';

    public const TYPE_TRANSFER_TO_PROVIDER = 'transfer_to_provider';

    public const TYPE_PROVIDER_CREDIT_PENDING = 'provider_credit_pending';

    public const TYPE_PROVIDER_CREDIT_AVAILABLE = 'provider_credit_available';

    public const TYPE_PAYOUT = 'payout';

    public const TYPE_REFUND_DEBIT = 'refund_debit';

    public const TYPE_REFUND_CREDIT = 'refund_credit';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_WALLET_TOPUP = 'wallet_topup';

    public const TYPE_WALLET_PAYMENT = 'wallet_payment';

    public const TYPE_ART_SALE_COLLECTED = 'art_sale_collected';

    public const TYPE_ART_COMMISSION = 'art_commission';

    public const TYPE_ART_TRANSFER_TO_ARTIST = 'art_transfer_to_artist';

    public const TYPE_ART_CREDIT_PENDING = 'art_credit_pending';

    public const TYPE_ART_CREDIT_AVAILABLE = 'art_credit_available';

    public const TYPE_VISIT_SALE_COLLECTED = 'visit_sale_collected';

    public const TYPE_VISIT_COMMISSION = 'visit_commission';

    public const TYPE_VISIT_TRANSFER_TO_PROVIDER = 'visit_transfer_to_provider';

    public const TYPE_VISIT_CREDIT_AVAILABLE = 'visit_credit_available';

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_DEPOSIT_COLLECTED => 'Acompte encaissé',
        self::TYPE_COMMISSION => 'Commission plateforme',
        self::TYPE_TRANSFER_TO_PROVIDER => 'Transfert vers prestataire',
        self::TYPE_PROVIDER_CREDIT_PENDING => 'Crédit prestataire (en attente)',
        self::TYPE_PROVIDER_CREDIT_AVAILABLE => 'Crédit prestataire (disponible)',
        self::TYPE_PAYOUT => 'Retrait',
        self::TYPE_REFUND_DEBIT => 'Remboursement (débit)',
        self::TYPE_REFUND_CREDIT => 'Remboursement (crédit)',
        self::TYPE_ADJUSTMENT => 'Ajustement manuel',
        self::TYPE_WALLET_TOPUP => 'Recharge du solde',
        self::TYPE_WALLET_PAYMENT => 'Paiement depuis le solde',
        self::TYPE_ART_SALE_COLLECTED => 'Vente d\'œuvre encaissée',
        self::TYPE_ART_COMMISSION => 'Commission plateforme (œuvre)',
        self::TYPE_ART_TRANSFER_TO_ARTIST => 'Transfert vers artiste',
        self::TYPE_ART_CREDIT_PENDING => 'Crédit artiste (en attente)',
        self::TYPE_ART_CREDIT_AVAILABLE => 'Crédit artiste (disponible)',
        self::TYPE_VISIT_SALE_COLLECTED => 'Visite encaissée',
        self::TYPE_VISIT_COMMISSION => 'Commission plateforme (visite)',
        self::TYPE_VISIT_TRANSFER_TO_PROVIDER => 'Transfert vers prestataire (visite)',
        self::TYPE_VISIT_CREDIT_AVAILABLE => 'Crédit prestataire (visite)',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REVERSED = 'reversed';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'En attente',
        self::STATUS_COMPLETED => 'Terminée',
        self::STATUS_REVERSED => 'Annulée',
    ];

    protected $fillable = [
        'uuid',
        'wallet_id',
        'type',
        'status',
        'amount_xof',
        'balance_before_xof',
        'balance_after_xof',
        'reservation_id',
        'payout_request_id',
        'wallet_topup_id',
        'artwork_order_id',
        'tourist_visit_id',
        'reversed_by_transaction_id',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'integer',
            'balance_before_xof' => 'integer',
            'balance_after_xof' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (WalletTransaction $t) => $t->uuid ??= (string) Str::uuid());
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }

    public function walletTopup(): BelongsTo
    {
        return $this->belongsTo(WalletTopup::class);
    }

    public function artworkOrder(): BelongsTo
    {
        return $this->belongsTo(ArtworkOrder::class);
    }

    public function touristVisit(): BelongsTo
    {
        return $this->belongsTo(TouristVisit::class);
    }

    public function reversedByTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_by_transaction_id');
    }

    public function labelForType(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function labelForStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
