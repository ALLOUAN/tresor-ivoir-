<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Carte achetée par un client : séances restantes et date d'expiration figées à l'achat. */
class LeisurePassPurchase extends Model
{
    public const STATUS_PENDING = 'pending_payment';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'leisure_pass_id', 'provider_id', 'user_id', 'pass_name', 'sessions_total', 'sessions_used', 'activity_ids',
        'expires_on', 'amount_xof', 'commission_percent', 'status', 'gateway', 'gateway_txn_id', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'sessions_total' => 'integer', 'sessions_used' => 'integer', 'activity_ids' => 'array', 'expires_on' => 'date',
            'amount_xof' => 'integer', 'commission_percent' => 'float', 'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (LeisurePassPurchase $p) => $p->uuid ??= (string) Str::uuid());
    }

    public function pass(): BelongsTo
    {
        return $this->belongsTo(LeisurePass::class, 'leisure_pass_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ActivityBooking::class);
    }

    public function remaining(): ?int
    {
        return $this->sessions_total === null ? null : max(0, $this->sessions_total - $this->sessions_used);
    }

    /** Utilisable pour cette activité, à cette date, pour ce nombre de participants (une séance par participant). */
    public function usableFor(Activity $activity, \Carbon\CarbonInterface $date, int $participants): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && (int) $this->provider_id === (int) $activity->provider_id
            && (! $this->expires_on || $date->toDateString() <= $this->expires_on->toDateString())
            && (empty($this->activity_ids) || in_array($activity->id, array_map('intval', $this->activity_ids), true))
            && ($this->remaining() === null || $this->remaining() >= $participants);
    }

    public function getReferenceAttribute(): string
    {
        return 'CRT-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
