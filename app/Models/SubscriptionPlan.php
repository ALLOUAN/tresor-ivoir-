<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'code', 'name_fr', 'name_en',
        'price_monthly', 'price_quarterly', 'price_semiannual', 'price_yearly',
        'benefits_text', 'covered_levels',
        'photos_limit', 'description_chars',
        'is_unlimited_features',
        'has_video', 'has_newsletter', 'has_homepage',
        'has_social_posts', 'has_verified_badge',
        'stats_level', 'support_level',
        'min_duration_months', 'is_active', 'is_promotional',
        'promo_starts_at', 'promo_ends_at', 'group_target', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'has_video' => 'boolean',
            'has_newsletter' => 'boolean',
            'has_homepage' => 'boolean',
            'has_social_posts' => 'boolean',
            'has_verified_badge' => 'boolean',
            'is_active' => 'boolean',
            'is_unlimited_features' => 'boolean',
            'is_promotional' => 'boolean',
            'price_monthly' => 'decimal:2',
            'price_quarterly' => 'decimal:2',
            'price_semiannual' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'promo_starts_at' => 'datetime',
            'promo_ends_at' => 'datetime',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
