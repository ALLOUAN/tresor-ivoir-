<?php

namespace App\Services;

use App\Models\AccountSecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;

class AccountSecurityEventLogger
{
    public static function log(User $user, string $eventType, ?Request $request = null, array $metadata = []): void
    {
        $request ??= request();

        AccountSecurityEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'ip_address' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 255),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
