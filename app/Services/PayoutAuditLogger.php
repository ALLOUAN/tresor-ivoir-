<?php

namespace App\Services;

use App\Models\PayoutAuditLog;
use App\Models\PayoutRequest;
use App\Models\User;

class PayoutAuditLogger
{
    public static function log(PayoutRequest $payout, string $eventType, string $actorType, ?User $actor = null, array $details = []): void
    {
        PayoutAuditLog::create([
            'payout_request_id' => $payout->id,
            'event_type' => $eventType,
            'actor_type' => $actorType,
            'actor_id' => $actor?->id,
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}
