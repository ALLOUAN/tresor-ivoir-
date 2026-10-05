<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDebt extends Model
{
    public const STATUS_OUTSTANDING = 'outstanding';

    public const STATUS_SETTLED = 'settled';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_OUTSTANDING => 'À régulariser',
        self::STATUS_SETTLED => 'Régularisée',
    ];

    protected $fillable = [
        'provider_id',
        'wallet_id',
        'amount_xof',
        'reason',
        'reservation_id',
        'status',
        'settled_at',
        'settled_via_payout_request_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'integer',
            'settled_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function settledViaPayoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class, 'settled_via_payout_request_id');
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OUTSTANDING);
    }

    public function labelForStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
