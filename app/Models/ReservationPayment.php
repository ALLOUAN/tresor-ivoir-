<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReservationPayment extends Model
{
    protected $fillable = [
        'uuid',
        'reservation_id',
        'amount',
        'currency',
        'gateway',
        'gateway_txn_id',
        'status',
        'paid_at',
        'failed_at',
        'failure_reason',
        'ip_address',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (ReservationPayment $p) => $p->uuid ??= (string) Str::uuid());
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
