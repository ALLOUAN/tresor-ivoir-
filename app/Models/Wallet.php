<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $fillable = [
        'provider_id',
        'user_id',
        'balance_available_xof',
        'balance_pending_xof',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'balance_available_xof' => 'integer',
            'balance_pending_xof' => 'integer',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function scopePlatform(Builder $query): Builder
    {
        return $query->whereNull('provider_id')->whereNull('user_id');
    }

    public static function platform(): self
    {
        return static::query()->platform()->firstOrCreate([], [
            'balance_available_xof' => 0,
            'balance_pending_xof' => 0,
            'currency' => 'XOF',
        ]);
    }

    public static function forProvider(Provider $provider): self
    {
        return static::query()->firstOrCreate(
            ['provider_id' => $provider->id],
            ['balance_available_xof' => 0, 'balance_pending_xof' => 0, 'currency' => 'XOF']
        );
    }

    public static function forUser(User $user): self
    {
        return static::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance_available_xof' => 0, 'balance_pending_xof' => 0, 'currency' => 'XOF']
        );
    }

    /**
     * Incrément SQL atomique — jamais de lecture-puis-écriture PHP sur les soldes.
     * Sûr sans verrou explicite pour l'incrément lui-même (atomique au niveau ligne InnoDB).
     */
    public function incrementAvailable(int $amountXof): void
    {
        DB::table('wallets')->where('id', $this->id)->increment('balance_available_xof', $amountXof);
        $this->refresh();
    }

    public function incrementPending(int $amountXof): void
    {
        DB::table('wallets')->where('id', $this->id)->increment('balance_pending_xof', $amountXof);
        $this->refresh();
    }

    public function decrementAvailable(int $amountXof): void
    {
        DB::table('wallets')->where('id', $this->id)->decrement('balance_available_xof', $amountXof);
        $this->refresh();
    }

    public function decrementPending(int $amountXof): void
    {
        DB::table('wallets')->where('id', $this->id)->decrement('balance_pending_xof', $amountXof);
        $this->refresh();
    }

    /** Relit la ligne avec verrou — à utiliser uniquement à l'intérieur d'une DB::transaction(). */
    public function lockedFresh(): self
    {
        return static::query()->whereKey($this->id)->lockForUpdate()->firstOrFail();
    }
}
