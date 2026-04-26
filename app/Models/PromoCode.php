<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'plan_id',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'max_uses',
        'used_count',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isValid(?int $planId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }
        if ($this->plan_id && $planId && $this->plan_id !== $planId) {
            return false;
        }

        return true;
    }

    public function applyDiscount(float $amount): float
    {
        $discount = $this->discount_type === 'percent'
            ? round($amount * ((float) $this->discount_value / 100))
            : min((float) $this->discount_value, $amount);

        return max(0.0, $amount - $discount);
    }

    public function discountAmount(float $amount): float
    {
        return $this->discount_type === 'percent'
            ? round($amount * ((float) $this->discount_value / 100))
            : min((float) $this->discount_value, $amount);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
