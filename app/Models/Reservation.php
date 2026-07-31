<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_NEW => 'Nouvelle',
        self::STATUS_CONFIRMED => 'Confirmée',
        self::STATUS_CANCELLED => 'Annulée',
    ];

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_DEPOSIT_PAID = 'deposit_paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_REFUNDED = 'refunded';

    /** @var array<string, string> */
    public const PAYMENT_STATUS_LABELS = [
        self::PAYMENT_UNPAID => 'Sans paiement',
        self::PAYMENT_PENDING => 'Paiement en attente',
        self::PAYMENT_DEPOSIT_PAID => 'Acompte payé',
        self::PAYMENT_FAILED => 'Paiement échoué',
        self::PAYMENT_REFUNDED => 'Remboursée',
    ];

    protected $fillable = [
        'accommodation_id',
        'accommodation_name',
        'provider_id',
        'room_name',
        'room_price_xof',
        'check_in',
        'check_out',
        'nights',
        'rooms_count',
        'guests_count',
        'total_xof',
        'full_name',
        'email',
        'phone',
        'message',
        'status',
        'payment_status',
        'deposit_amount_xof',
        'commission_rate_percent',
        'commission_amount_xof',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
        ];
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ReservationPayment::class);
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function paymentStatusOptions(): array
    {
        return self::PAYMENT_STATUS_LABELS;
    }

    public function labelForStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function labelForPaymentStatus(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('full_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('accommodation_name', 'like', $like)
                ->orWhere('room_name', 'like', $like);
        });
    }

    public function scopeStatusFilter(Builder $query, ?string $status): Builder
    {
        if ($status === null || $status === '' || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeCreatedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }
}
