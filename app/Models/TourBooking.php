<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Réservation en ligne d'un départ programmé de circuit : confirmation immédiate, acompte puis solde
 * (ou paiement intégral), voyageurs, prise en charge, options, conditions d'annulation figées.
 */
class TourBooking extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_DEPOSIT_PAID = 'deposit_paid';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Paiement en attente',
        self::STATUS_DEPOSIT_PAID => 'Acompte versé — solde à régler',
        self::STATUS_PAID => 'Confirmée et soldée',
        self::STATUS_CANCELLED => 'Annulée',
        self::STATUS_FAILED => 'Paiement échoué',
    ];

    public const CANCELLED_BY_LABELS = [
        'client' => 'Annulée par le voyageur',
        'agency' => 'Annulée par l\'agence',
        'departure' => 'Départ annulé (minimum de voyageurs non atteint)',
        'unpaid' => 'Annulée : solde non réglé à temps',
    ];

    protected $fillable = [
        'tour_package_id', 'tour_departure_id', 'provider_id', 'user_id', 'tour_name', 'departure_date',
        'adults', 'children', 'travelers_count', 'single_rooms', 'pickup_point', 'extras', 'price_lines',
        'amount_total_xof', 'deposit_xof', 'balance_due_on', 'amount_paid_xof', 'commission_percent',
        'status', 'pending_stage', 'pending_amount_xof', 'gateway_txn_id', 'cancellation_policy', 'refund_xof',
        'cancelled_at', 'cancelled_by', 'balance_reminded_at', 'full_name', 'email', 'phone', 'message',
        'travelers', 'emergency_contact',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'balance_due_on' => 'date',
            'extras' => 'array',
            'price_lines' => 'array',
            'cancellation_policy' => 'array',
            'travelers' => 'array',
            'amount_total_xof' => 'integer',
            'deposit_xof' => 'integer',
            'amount_paid_xof' => 'integer',
            'pending_amount_xof' => 'integer',
            'refund_xof' => 'integer',
            'commission_percent' => 'float',
            'cancelled_at' => 'datetime',
            'balance_reminded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (TourBooking $b) => $b->uuid ??= (string) Str::uuid());
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id')->withTrashed();
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(TourDeparture::class, 'tour_departure_id');
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
        return 'CIR-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_CANCELLED && $this->cancelled_by) {
            return __(self::CANCELLED_BY_LABELS[$this->cancelled_by] ?? self::STATUS_LABELS[self::STATUS_CANCELLED]);
        }

        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_DEPOSIT_PAID, self::STATUS_PAID], true);
    }

    public function balanceXof(): int
    {
        return max(0, $this->amount_total_xof - $this->amount_paid_xof);
    }

    public function canPayBalance(): bool
    {
        return $this->status === self::STATUS_DEPOSIT_PAID && $this->balanceXof() > 0 && ! $this->departure_date->isPast();
    }

    /** Jours restant avant le départ (fuseau d'Abidjan). */
    public function daysBeforeDeparture(): int
    {
        return (int) now('Africa/Abidjan')->startOfDay()->diffInDays($this->departure_date->copy()->startOfDay(), false);
    }

    /** Pourcentage remboursé si le voyageur annule aujourd'hui, selon le barème figé à la réservation. */
    public function refundPercentNow(): int
    {
        $days = $this->daysBeforeDeparture();
        foreach (collect($this->cancellation_policy ?? [])->sortByDesc('days') as $rule) {
            if ($days >= (int) $rule['days']) {
                return (int) $rule['percent'];
            }
        }

        return 0;
    }

    public function refundIfCancelledNow(): int
    {
        return (int) floor($this->amount_paid_xof * $this->refundPercentNow() / 100);
    }

    public function canBeCancelledByClient(): bool
    {
        return $this->isActive() && $this->daysBeforeDeparture() >= 0;
    }

    /** Liste des voyageurs complète (un nom par voyageur). */
    public function travelersComplete(): bool
    {
        return collect($this->travelers ?? [])->filter(fn ($t) => trim((string) ($t['name'] ?? '')) !== '')->count() >= $this->travelers_count;
    }

    /** Feuille de route / confirmation, lien signé valable jusqu'à 30 jours après le départ. */
    public function confirmationUrl(): string
    {
        return URL::temporarySignedRoute('tour-booking.confirmation', $this->departure_date->copy()->addDays(30)->max(now()->addDays(30)), ['tourBooking' => $this->id]);
    }
}
