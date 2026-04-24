<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid', 'subscription_id', 'provider_id', 'amount', 'currency',
        'method', 'gateway', 'gateway_txn_id', 'status',
        'paid_at', 'failed_at', 'failure_reason', 'ip_address', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'created_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Payment $p) => $p->uuid ??= (string) Str::uuid());
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
