<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MediaPurchase extends Model
{
    protected $fillable = [
        'uuid', 'media_id', 'user_id', 'amount', 'currency',
        'gateway', 'gateway_txn_id', 'status', 'ip_address', 'metadata', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'paid_at'  => 'datetime',
            'amount'   => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->uuid ??= (string) Str::uuid());
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(SiteMediaItem::class, 'media_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
