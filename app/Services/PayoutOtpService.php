<?php

namespace App\Services;

use App\Models\PayoutRequest;
use App\Notifications\PayoutVerificationCodeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Vérification complémentaire par code à usage unique — même patron que
 * AuthController::sendEmailVerificationCode()/verifyEmailCode(), généralisé pour porter
 * un contexte (l'identifiant du retrait) au lieu d'être codé en dur sur "vérifier l'email".
 */
class PayoutOtpService
{
    private const TTL_MINUTES = 10;

    public function send(PayoutRequest $payout): void
    {
        $holderUser = $payout->holderUser();
        if (! $holderUser) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($payout), [
            'hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->timestamp,
        ], now()->addMinutes(self::TTL_MINUTES));

        $holderUser->notify(new PayoutVerificationCodeNotification($code, self::TTL_MINUTES, $payout->amount_xof));
    }

    /** @return true|string true si validé, sinon un message d'erreur */
    public function verify(PayoutRequest $payout, string $submittedCode): bool|string
    {
        $cached = Cache::get($this->cacheKey($payout));

        if (! is_array($cached) || empty($cached['hash'])) {
            return 'Code expiré ou introuvable, demandez un nouvel envoi.';
        }

        if ((int) ($cached['expires_at'] ?? 0) < now()->timestamp) {
            Cache::forget($this->cacheKey($payout));

            return 'Code expiré, demandez un nouvel envoi.';
        }

        if (! Hash::check($submittedCode, $cached['hash'])) {
            return 'Code incorrect.';
        }

        Cache::forget($this->cacheKey($payout));

        return true;
    }

    private function cacheKey(PayoutRequest $payout): string
    {
        return 'payout_otp:'.$payout->id;
    }
}
