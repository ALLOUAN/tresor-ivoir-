<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutAuditLog extends Model
{
    public const ACTOR_SYSTEM = 'system';

    public const ACTOR_USER = 'user';

    public const ACTOR_ADMIN = 'admin';

    public $timestamps = false;

    protected $fillable = [
        'payout_request_id',
        'event_type',
        'actor_type',
        'actor_id',
        'details',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
