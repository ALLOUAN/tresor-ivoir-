<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;

class SubscriptionService
{
    public function createSubscription(
        Provider $provider,
        SubscriptionPlan $plan,
        string $billingCycle,
        string $method,
        string $transactionId
    ): Subscription {
        // Cancel any currently active subscription for this provider
        Subscription::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancellation_reason' => 'Remplacé par forfait ' . strtoupper($plan->code),
            ]);

        $months = $billingCycle === 'yearly' ? 12 : 1;

        return Subscription::create([
            'provider_id'    => $provider->id,
            'plan_id'        => $plan->id,
            'status'         => 'active',
            'billing_cycle'  => $billingCycle,
            'payment_method' => $method,
            'starts_at'      => now(),
            'ends_at'        => now()->addMonths($months),
            'auto_renew'     => true,
        ]);
    }

    public function activatePendingSubscription(Subscription $subscription): void
    {
        // Cancel any other active subscriptions first
        Subscription::where('provider_id', $subscription->provider_id)
            ->where('status', 'active')
            ->where('id', '!=', $subscription->id)
            ->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancellation_reason' => 'Remplacé par forfait ' . strtoupper($subscription->plan?->code ?? ''),
            ]);

        $subscription->update(['status' => 'active']);
    }
}
