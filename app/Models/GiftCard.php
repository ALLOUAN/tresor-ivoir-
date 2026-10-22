<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Bon cadeau valable dans un établissement. */
class GiftCard extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REDEEMED = 'redeemed';

    public const STATUS_EXPIRED = 'expired';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'En attente de paiement',
        self::STATUS_ACTIVE => 'Valable',
        self::STATUS_REDEEMED => 'Utilisé',
        self::STATUS_EXPIRED => 'Expiré',
    ];

    /** Montants proposés (FCFA) ; un montant libre est aussi possible. */
    public const AMOUNTS = [10000, 25000, 50000, 100000];

    public const VALIDITY_MONTHS = 12;

    protected $fillable = [
        'provider_id', 'code', 'amount_xof', 'commission_percent', 'commission_amount_xof',
        'buyer_user_id', 'buyer_name', 'buyer_email', 'recipient_name', 'recipient_email', 'message',
        'status', 'payment_method', 'gateway_txn_id', 'paid_at', 'expires_at', 'redeemed_at', 'redeemed_by',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'expires_at' => 'date', 'redeemed_at' => 'datetime', 'amount_xof' => 'integer', 'commission_amount_xof' => 'integer'];
    }

    public static function newCode(): string
    {
        do {
            // Sans caractères ambigus (0/O, 1/I) : le code est souvent recopié à la main.
            $code = collect(range(1, 12))->map(fn () => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'[random_int(0, 31)])->implode('');
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function formattedCode(): string
    {
        return implode('-', str_split($this->code, 4));
    }

    public function isUsable(): bool
    {
        return $this->status === self::STATUS_ACTIVE && (! $this->expires_at || ! $this->expires_at->isPast() || $this->expires_at->isToday());
    }
}
