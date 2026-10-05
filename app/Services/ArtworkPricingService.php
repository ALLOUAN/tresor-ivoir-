<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Schema;

class ArtworkPricingService
{
    protected const DEFAULT_COMMISSION_PERCENT = 5.0;

    /**
     * @return array{amount_total_xof:int, commission_percent:float, commission_amount_xof:int, artist_net_amount_xof:int}
     */
    public function compute(int $priceXof): array
    {
        $commissionRate = $this->commissionPercent();
        $commissionXof = (int) round($priceXof * $commissionRate / 100);

        return [
            'amount_total_xof' => $priceXof,
            'commission_percent' => $commissionRate,
            'commission_amount_xof' => $commissionXof,
            'artist_net_amount_xof' => max(0, $priceXof - $commissionXof),
        ];
    }

    public function commissionPercent(): float
    {
        $value = $this->setting('art_commission_percent');

        return $value !== null ? (float) $value : self::DEFAULT_COMMISSION_PERCENT;
    }

    protected function setting(string $key): ?string
    {
        if (! Schema::hasTable('payment_settings')) {
            return null;
        }

        return PaymentSetting::where('key', $key)->value('value');
    }
}
