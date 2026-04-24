<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'bronze',
                'name_fr' => 'Bronze',
                'name_en' => 'Bronze',
                'price_monthly' => 9900.00,
                'price_yearly' => 99000.00,
                'photos_limit' => 5,
                'description_chars' => 500,
                'has_video' => false,
                'has_newsletter' => false,
                'has_homepage' => false,
                'has_social_posts' => false,
                'has_verified_badge' => false,
                'stats_level' => 'basic',
                'support_level' => 'email',
                'min_duration_months' => 0,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'silver',
                'name_fr' => 'Silver',
                'name_en' => 'Silver',
                'price_monthly' => 24900.00,
                'price_yearly' => 249000.00,
                'photos_limit' => 10,
                'description_chars' => 2000,
                'has_video' => true,
                'has_newsletter' => true,
                'has_homepage' => false,
                'has_social_posts' => false,
                'has_verified_badge' => false,
                'stats_level' => 'advanced',
                'support_level' => 'chat',
                'min_duration_months' => 3,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'gold',
                'name_fr' => 'Gold',
                'name_en' => 'Gold',
                'price_monthly' => 49900.00,
                'price_yearly' => 499000.00,
                'photos_limit' => 20,
                'description_chars' => 0,
                'has_video' => true,
                'has_newsletter' => true,
                'has_homepage' => true,
                'has_social_posts' => true,
                'has_verified_badge' => true,
                'stats_level' => 'full',
                'support_level' => 'dedicated',
                'min_duration_months' => 6,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
