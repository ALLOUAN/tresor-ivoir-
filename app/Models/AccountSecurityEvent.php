<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSecurityEvent extends Model
{
    public const TYPE_LOGIN = 'login';

    public const TYPE_PASSWORD_CHANGED = 'password_changed';

    public const TYPE_EMAIL_CHANGED = 'email_changed';

    public const TYPE_PHONE_CHANGED = 'phone_changed';

    public const TYPE_PAYOUT_DESTINATION_CHANGED = 'payout_destination_changed';

    /** Types considérés comme "changement sensible" pour le moteur de risque des retraits. */
    public const SENSITIVE_TYPES = [
        self::TYPE_PASSWORD_CHANGED,
        self::TYPE_EMAIL_CHANGED,
        self::TYPE_PHONE_CHANGED,
        self::TYPE_PAYOUT_DESTINATION_CHANGED,
    ];

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
