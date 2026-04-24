<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscription extends Model
{
    protected $fillable = [
        'uuid', 'provider_id', 'plan_id', 'status', 'billing_cycle',
        'payment_method', 'starts_at', 'ends_at', 'auto_renew',
        'cancelled_at', 'cancellation_reason', 'upgraded_from_id',
        'last_edited_by_user_id', 'last_edited_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_edited_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Subscription $s) => $s->uuid ??= (string) Str::uuid());
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upgradedFrom()
    {
        return $this->belongsTo(Subscription::class, 'upgraded_from_id');
    }

    public function lastEditedBy()
    {
        return $this->belongsTo(User::class, 'last_edited_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }
}
