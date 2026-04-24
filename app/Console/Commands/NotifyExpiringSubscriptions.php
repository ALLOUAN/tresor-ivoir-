<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringSoonMail;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:notify-expiring';

    protected $description = 'Send daily emails for subscriptions expiring soon';

    public function handle(): int
    {
        $subscriptions = Subscription::query()
            ->with(['provider.user', 'plan'])
            ->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $email = $subscription->provider?->user?->email;
            if (! $email) {
                continue;
            }

            Mail::to($email)->send(new SubscriptionExpiringSoonMail($subscription));
            $sent++;
        }

        $this->info("Notifications envoyées: {$sent}");

        return self::SUCCESS;
    }
}
