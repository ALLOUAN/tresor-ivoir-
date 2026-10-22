<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Réservation en ligne d'une activité (créneau) ou d'une formule (anniversaire, groupe, entreprise).
 * Billet à QR code, décharge éventuelle, annulation gratuite et report jusqu'à `cancellable_until`.
 */
class ActivityBooking extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_LABELS = [
        self::STATUS_PENDING_PAYMENT => 'Paiement en attente',
        self::STATUS_PAID => 'Confirmée',
        self::STATUS_CANCELLED => 'Annulée',
        self::STATUS_REFUNDED => 'Annulée et remboursée',
        self::STATUS_FAILED => 'Paiement échoué',
    ];

    /** Nombre maximum de reports par réservation. */
    public const MAX_RESCHEDULES = 2;

    protected $fillable = [
        'activity_id', 'leisure_package_id', 'provider_id', 'user_id', 'leisure_pass_purchase_id', 'item_name',
        'booking_date', 'booking_time', 'duration_minutes', 'participants', 'tier_breakdown', 'addons',
        'price_adjust_percent', 'amount_total_xof', 'amount_paid_xof', 'commission_percent', 'commission_amount_xof',
        'status', 'gateway', 'gateway_txn_id', 'paid_at', 'cancellable_until', 'cancelled_at', 'refund_xof',
        'reschedule_count', 'waiver', 'ticket_code', 'checked_in_at', 'checked_in_by', 'full_name', 'email', 'phone',
        'message', 'celebrant_name',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date', 'duration_minutes' => 'integer', 'participants' => 'integer',
            'tier_breakdown' => 'array', 'addons' => 'array', 'waiver' => 'array', 'price_adjust_percent' => 'integer',
            'amount_total_xof' => 'integer', 'amount_paid_xof' => 'integer', 'commission_percent' => 'float',
            'commission_amount_xof' => 'integer', 'refund_xof' => 'integer', 'reschedule_count' => 'integer',
            'paid_at' => 'datetime', 'cancellable_until' => 'datetime', 'cancelled_at' => 'datetime', 'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (ActivityBooking $b) => $b->uuid ??= (string) Str::uuid());
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class)->withTrashed();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(LeisurePackage::class, 'leisure_package_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passPurchase(): BelongsTo
    {
        return $this->belongsTo(LeisurePassPurchase::class, 'leisure_pass_purchase_id');
    }

    public function getReferenceAttribute(): string
    {
        return 'ACT-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function isPackage(): bool
    {
        return $this->leisure_package_id !== null;
    }

    public function balanceOnSite(): int
    {
        return max(0, $this->amount_total_xof - $this->amount_paid_xof);
    }

    public function startsAt(): Carbon
    {
        $start = Carbon::parse($this->booking_date->toDateString(), 'Africa/Abidjan');

        return $this->booking_time ? $start->setTimeFromTimeString($this->booking_time) : $start;
    }

    public function isCancellableByClient(): bool
    {
        return $this->status === self::STATUS_PAID && ! $this->checked_in_at
            && $this->cancellable_until && now()->lt($this->cancellable_until);
    }

    public function canReschedule(): bool
    {
        return $this->isCancellableByClient() && ! $this->isPackage() && $this->reschedule_count < self::MAX_RESCHEDULES;
    }

    public function needsWaiver(): bool
    {
        return (bool) $this->activity?->requires_waiver && empty($this->waiver['accepted_at']);
    }

    /** Code du billet (QR), créé à la première demande pour une réservation confirmée. */
    public function ticketCode(): ?string
    {
        if ($this->status !== self::STATUS_PAID) {
            return null;
        }
        if (! $this->ticket_code) {
            do {
                $code = strtoupper(Str::random(10));
            } while (static::where('ticket_code', $code)->exists() || TouristVisit::where('ticket_code', $code)->exists());
            $this->forceFill(['ticket_code' => $code])->save();
        }

        return $this->ticket_code;
    }

    public function ticketCheckUrl(): ?string
    {
        return ($code = $this->ticketCode()) ? route('provider.tickets.show', $code) : null;
    }

    public function ticketUrl(): string
    {
        return URL::temporarySignedRoute('activity-booking.ticket', $this->booking_date->copy()->addDays(30)->max(now()->addDays(30)), ['activityBooking' => $this->id]);
    }

    public function tiersSummary(): ?string
    {
        $lines = collect($this->tier_breakdown ?? [])->filter(fn ($t) => ($t['qty'] ?? 0) > 0);

        return $lines->isEmpty() ? null : $lines->map(fn ($t) => $t['qty'].' × '.$t['label'])->join(', ');
    }
}
