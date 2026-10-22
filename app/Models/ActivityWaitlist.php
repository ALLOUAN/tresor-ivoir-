<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Inscription sur liste d'attente d'un créneau complet ; le premier inscrit est prévenu quand une place se libère. */
class ActivityWaitlist extends Model
{
    protected $table = 'activity_waitlist';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_NOTIFIED = 'notified';

    public const STATUS_BOOKED = 'booked';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    /** Délai laissé à la personne prévenue pour réserver avant de passer à la suivante. */
    public const HOLD_MINUTES = 120;

    protected $fillable = ['activity_id', 'user_id', 'booking_date', 'booking_time', 'participants', 'status', 'notified_at', 'expires_at'];

    protected function casts(): array
    {
        return ['booking_date' => 'date', 'participants' => 'integer', 'notified_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
