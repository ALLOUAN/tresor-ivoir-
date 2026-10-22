<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Code promo créé par un établissement, valable sur ses séjours et réservations. */
class ProviderPromoCode extends Model
{
    protected $fillable = [
        'provider_id', 'code', 'percent_off', 'amount_off_xof', 'min_amount_xof',
        'starts_on', 'ends_on', 'max_uses', 'uses_count', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'percent_off' => 'integer',
            'amount_off_xof' => 'integer',
            'min_amount_xof' => 'integer',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'is_active' => 'boolean',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public static function normalize(?string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $code));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /** Raison pour laquelle le code n'est pas applicable à ce montant, ou null s'il l'est. */
    public function problemFor(int $amountXof): ?string
    {
        $today = now()->startOfDay();

        return match (true) {
            ! $this->is_active => __('Ce code promo n\'est plus actif.'),
            $this->starts_on && $today->lt($this->starts_on) => __('Ce code promo n\'est valable qu\'à partir du :d.', ['d' => $this->starts_on->format('d/m/Y')]),
            $this->ends_on && $today->gt($this->ends_on) => __('Ce code promo a expiré.'),
            $this->max_uses !== null && $this->uses_count >= $this->max_uses => __('Ce code promo a atteint son nombre maximal d\'utilisations.'),
            $this->min_amount_xof && $amountXof > 0 && $amountXof < $this->min_amount_xof => __('Ce code promo s\'applique à partir de :n FCFA.', ['n' => number_format($this->min_amount_xof, 0, ',', ' ')]),
            default => null,
        };
    }

    public function discountFor(int $amountXof): int
    {
        $off = $this->percent_off ? (int) round($amountXof * $this->percent_off / 100) : (int) $this->amount_off_xof;

        return max(0, min($off, $amountXof));
    }

    public function label(): string
    {
        return $this->percent_off ? '−'.$this->percent_off.' %' : '−'.number_format((int) $this->amount_off_xof, 0, ',', ' ').' FCFA';
    }
}
