<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDocument extends Model
{
    public const TYPE_IDENTITY = 'identity';

    public const TYPE_BUSINESS_REGISTER = 'business_register';

    public const TYPE_OTHER = 'other';

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_IDENTITY => 'Pièce d\'identité du gérant',
        self::TYPE_BUSINESS_REGISTER => 'Registre du commerce (RCCM) ou autorisation d\'exploitation',
        self::TYPE_OTHER => 'Autre justificatif',
    ];

    /** Pièces exigées pour que le compte soit vérifié. */
    public const REQUIRED_TYPES = [self::TYPE_IDENTITY, self::TYPE_BUSINESS_REGISTER];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'En cours d\'examen',
        self::STATUS_APPROVED => 'Validé',
        self::STATUS_REJECTED => 'Refusé',
    ];

    protected $fillable = [
        'provider_id', 'type', 'file_path', 'original_name', 'status',
        'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function typeLabel(): string
    {
        return __(self::TYPE_LABELS[$this->type] ?? $this->type);
    }

    public function statusLabel(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    /** Toutes les pièces exigées ont-elles au moins une version validée ? */
    public static function providerHasAllRequired(Provider $provider): bool
    {
        $approved = static::where('provider_id', $provider->id)
            ->where('status', self::STATUS_APPROVED)
            ->pluck('type')
            ->unique();

        return collect(self::REQUIRED_TYPES)->every(fn ($t) => $approved->contains($t));
    }
}
