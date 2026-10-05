<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Schema;

class TouristVisitPricingService
{
    protected const DEFAULT_COMMISSION_PERCENT = 10.0;

    /**
     * @return array{amount_total_xof:int, commission_percent:float, commission_amount_xof:int, provider_net_amount_xof:int}
     */
    public function compute(int $unitPriceXof, int $participants, bool $withGuide, int $guideSupplementXof): array
    {
        $amountTotal = ($unitPriceXof * $participants) + ($withGuide ? $guideSupplementXof : 0);
        $commissionRate = $this->commissionPercent();
        $commissionXof = (int) round($amountTotal * $commissionRate / 100);

        return [
            'amount_total_xof' => $amountTotal,
            'commission_percent' => $commissionRate,
            'commission_amount_xof' => $commissionXof,
            'provider_net_amount_xof' => max(0, $amountTotal - $commissionXof),
        ];
    }

    public function commissionPercent(): float
    {
        $value = $this->setting('visit_commission_percent');

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
