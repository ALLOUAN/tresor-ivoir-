<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Code de vérification e-mail à 6 chiffres (valable 10 min), partagé par le site et l'API mobile.
 * Le code est gardé haché en cache sous la même clé pour les deux canaux.
 */
class EmailVerificationCodeService
{
    public const TTL_MINUTES = 10;

    public const OK = 'ok';

    public const EXPIRED = 'expired';

    public const INVALID = 'invalid';

    public static function cacheKey(User $user): string
    {
        return 'email_verification_code:'.$user->getKey();
    }

    /** Envoie un nouveau code ; false (erreur journalisée) si le serveur d'e-mails ne répond pas. */
    public function send(User $user): bool
    {
        $code = (string) random_int(100000, 999999);

        Cache::put(self::cacheKey($user), [
            'hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->timestamp,
        ], now()->addMinutes(self::TTL_MINUTES));

        try {
            $user->notify(new EmailVerificationCodeNotification($code, self::TTL_MINUTES));
        } catch (\Throwable $e) {
            report($e);
            // Pas de code « actif » non reçu : une nouvelle demande renverra un code.
            Cache::forget(self::cacheKey($user));

            return false;
        }

        return true;
    }

    /** Vérifie le code et valide l'adresse si c'est bon. Renvoie OK, EXPIRED ou INVALID. */
    public function verify(User $user, string $code): string
    {
        $cached = Cache::get(self::cacheKey($user));
        $expiresAt = is_array($cached) ? ($cached['expires_at'] ?? null) : null;

        if (! is_array($cached) || empty($cached['hash']) || ! is_numeric($expiresAt) || now()->timestamp > (int) $expiresAt) {
            Cache::forget(self::cacheKey($user));

            return self::EXPIRED;
        }

        if (! Hash::check(preg_replace('/\D/', '', $code), $cached['hash'])) {
            return self::INVALID;
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->forceFill(['is_verified' => true])->save();
            event(new Verified($user));
        }
        Cache::forget(self::cacheKey($user));

        return self::OK;
    }
}
