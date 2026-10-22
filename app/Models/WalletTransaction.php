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
        'gift_sale_collected' => 'Bon cadeau encaissé',
        'gift_commission' => 'Commission plateforme (bon cadeau)',
        'gift_transfer_to_provider' => 'Transfert vers prestataire (bon cadeau)',
        'gift_credit_pending' => 'Crédit bon cadeau (en attente d\'utilisation)',
        'gift_credit_available' => 'Crédit bon cadeau (utilisé)',
        'art_installment_1' => 'Acompte œuvre (paiement en plusieurs fois)',
        'art_installment_2' => 'Échéance 2 — œuvre',
        'art_installment_3' => 'Échéance 3 — œuvre',
        'art_wallet_payment' => 'Paiement d\'œuvre depuis le solde',
        'art_wallet_installment_1' => 'Acompte d\'œuvre depuis le solde',
        'art_wallet_installment_2' => 'Échéance 2 depuis le solde',
        'art_wallet_installment_3' => 'Échéance 3 depuis le solde',
        'art_refund_client' => 'Remboursement d\'œuvre crédité',
        'art_refund_platform' => 'Remboursement d\'œuvre (part plateforme)',
        'art_commission_deposit_collected' => 'Acompte sur mesure encaissé',
        'art_commission_balance_collected' => 'Solde sur mesure encaissé',
        'art_commission_deposit_transfer' => 'Transfert artiste (acompte sur mesure)',
        'art_commission_balance_transfer' => 'Transfert artiste (solde sur mesure)',
        'art_commission_deposit_credit' => 'Crédit artiste (acompte sur mesure)',
        'art_commission_balance_credit' => 'Crédit artiste (solde sur mesure)',
        'art_commission_deposit_wallet_payment' => 'Acompte sur mesure depuis le solde',
        'art_commission_balance_wallet_payment' => 'Solde sur mesure depuis le solde',
        'art_commission_refund_client' => 'Remboursement sur mesure crédité',
        'art_commission_refund_provider' => 'Remboursement sur mesure (part artiste)',
        'art_commission_refund_platform' => 'Remboursement sur mesure (part plateforme)',
        'transport_collected' => 'Transport encaissé',
        'transport_transfer' => 'Transfert vers prestataire (transport)',
        'transport_credit' => 'Crédit prestataire (transport)',
        'transport_wallet_payment' => 'Paiement de transport depuis le solde',
        'transport_refund_client' => 'Remboursement transport crédité',
        'transport_refund_provider' => 'Remboursement transport (part prestataire)',
        'transport_refund_platform' => 'Remboursement transport (part plateforme)',
        'leisure_collected' => 'Activité de loisirs encaissée',
        'leisure_transfer' => 'Transfert vers prestataire (loisirs)',
        'leisure_credit' => 'Crédit prestataire (loisirs)',
        'leisure_wallet_payment' => 'Paiement d\'activité depuis le solde',
        'leisure_refund_client' => 'Remboursement activité crédité',
        'leisure_refund_provider' => 'Remboursement activité (part prestataire)',
        'leisure_refund_platform' => 'Remboursement activité (part plateforme)',
        'leisure_pass_collected' => 'Carte multi-séances encaissée',
        'leisure_pass_transfer' => 'Transfert vers prestataire (carte)',
        'leisure_pass_credit' => 'Crédit prestataire (carte multi-séances)',
        'leisure_pass_wallet_payment' => 'Achat de carte depuis le solde',
        'tour_deposit_collected' => 'Acompte circuit encaissé',
        'tour_balance_collected' => 'Solde circuit encaissé',
        'tour_full_collected' => 'Circuit encaissé',
        'tour_deposit_commission' => 'Commission plateforme (acompte circuit)',
        'tour_balance_commission' => 'Commission plateforme (solde circuit)',
        'tour_full_commission' => 'Commission plateforme (circuit)',
        'tour_deposit_credit_pending' => 'Crédit agence — acompte circuit (en attente du départ)',
        'tour_balance_credit_pending' => 'Crédit agence — solde circuit (en attente du départ)',
        'tour_full_credit_pending' => 'Crédit agence — circuit (en attente du départ)',
        'tour_credit_available' => 'Crédit agence — circuit (disponible)',
        'tour_refund_client_credit' => 'Remboursement circuit crédité',
        'tour_refund_provider_debit' => 'Remboursement circuit (part agence)',
        'tour_refund_platform_debit' => 'Remboursement circuit (part plateforme)',
        'booking_sale_collected' => 'Réservation encaissée',
        'booking_commission' => 'Commission plateforme (réservation)',
        'booking_transfer_to_provider' => 'Transfert vers prestataire (réservation)',
        'booking_credit_pending' => 'Crédit prestataire réservation (en attente)',
        'booking_credit_available' => 'Crédit prestataire réservation (disponible)',
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
        'booking_request_id',
        'tour_booking_id',
        'activity_booking_id',
        'leisure_pass_purchase_id',
        'transport_booking_id',
        'art_commission_id',
        'gift_card_id',
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

    public function artCommission(): BelongsTo
    {
        return $this->belongsTo(ArtCommission::class);
    }

    public function tourBooking(): BelongsTo
    {
        return $this->belongsTo(TourBooking::class);
    }

    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
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
        return __(self::TYPE_LABELS[$this->type] ?? $this->type);
    }

    public function labelForStatus(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }
}
