<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Schema;

class ReservationPricingService
{
    protected const DEFAULT_DEPOSIT_PERCENT = 30.0;

    protected const DEFAULT_COMMISSION_PERCENT = 12.0;

    /**
     * Calcule nuits, total, acompte et commission pour une réservation.
     *
     * @return array{nights:int, total_xof:int|null, deposit_xof:int|null, commission_xof:int|null, commission_rate_percent:float, deposit_rate_percent:float}
     */
    public function compute(string $checkIn, string $checkOut, int $roomsCount, ?int $roomPriceXof): array
    {
        $checkInDate = new \DateTimeImmutable($checkIn);
        $checkOutDate = new \DateTimeImmutable($checkOut);
        $nights = max(1, $checkInDate->diff($checkOutDate)->days);

        $totalXof = $roomPriceXof !== null
            ? $roomPriceXof * $nights * $roomsCount
            : null;

        $depositRate = $this->depositPercent();
        $commissionRate = $this->commissionPercent();

        $depositXof = $totalXof !== null
            ? (int) round($totalXof * $depositRate / 100)
            : null;

        $commissionXof = $depositXof !== null
            ? (int) round($depositXof * $commissionRate / 100)
            : null;

        return [
            'nights' => $nights,
            'total_xof' => $totalXof,
            'deposit_xof' => $depositXof,
            'commission_xof' => $commissionXof,
            'commission_rate_percent' => $commissionRate,
            'deposit_rate_percent' => $depositRate,
        ];
    }

    public function depositPercent(): float
    {
        $value = $this->setting('reservation_deposit_percent');

        return $value !== null ? (float) $value : self::DEFAULT_DEPOSIT_PERCENT;
    }

    public function commissionPercent(): float
    {
        $value = $this->setting('reservation_commission_percent');

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
