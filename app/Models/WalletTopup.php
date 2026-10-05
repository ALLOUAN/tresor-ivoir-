<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WalletTopup extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'user_id',
        'wallet_id',
        'amount_xof',
        'gateway',
        'gateway_txn_id',
        'status',
        'paid_at',
        'failed_at',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'integer',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (WalletTopup $t) => $t->uuid ??= (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
