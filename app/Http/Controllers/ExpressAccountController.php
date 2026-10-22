<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use App\Services\ClientAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Compte express pendant une réservation : la personne saisit nom + e-mail dans le module de
 * réservation, reçoit un code à 6 chiffres et le saisit sur place. Le code prouve l'adresse :
 * on connecte le compte client existant ou on en crée un (déjà vérifié), sans quitter la page.
 */
class ExpressAccountController extends Controller
{
    private const TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly ClientAccountService $accounts) {}

    public function start(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user?->hasVerifiedEmail()) {
            return response()->json(['status' => 'ready']);
        }

        $data = $request->validate([
            'full_name' => [$user ? 'nullable' : 'required', 'string', 'max:160'],
            'email' => [$user ? 'nullable' : 'required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            ...ClientAccountService::honeypotRules(),
        ], [
            'full_name.required' => __('Indiquez votre nom complet.'),
            'email.required' => __('Indiquez votre adresse e-mail.'),
            'email.email' => __('Cette adresse e-mail ne semble pas valide.'),
        ]);

        // Connecté mais non vérifié : le code part à l'adresse du compte.
        $email = mb_strtolower(trim($user ? $user->email : $data['email']));

        if (! $user) {
            $existing = User::where('email', $email)->first();
            if ($existing && $existing->role !== 'visitor') {
                return $this->fail(__('Cette adresse est liée à un compte professionnel : connectez-vous depuis la page Connexion.'));
            }
            if ($existing && ! $existing->is_active) {
                return $this->fail(__('Ce compte est désactivé. Contactez-nous pour le réactiver.'));
            }
        }

        $limiterKey = 'express-code:'.sha1($email);
        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            return $this->fail(__('Trop de codes demandés. Réessayez dans :n minutes.', ['n' => max(1, (int) ceil(RateLimiter::availableIn($limiterKey) / 60))]), 429);
        }
        RateLimiter::hit($limiterKey, 600);

        [$first, $last] = ClientAccountService::splitFullName((string) ($data['full_name'] ?? ''));
        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($email), [
            'hash' => Hash::make($code),
            'attempts' => 0,
            'first_name' => $first,
            'last_name' => $last,
            'phone' => $data['phone'] ?? null,
        ], now()->addMinutes(self::TTL_MINUTES));
        $request->session()->put('express_email', $email);

        try {
            $notifiable = $user ?? Notification::route('mail', $email);
            $notifiable->notify(new EmailVerificationCodeNotification($code, self::TTL_MINUTES));
        } catch (\Throwable $e) {
            report($e);
            Cache::forget($this->cacheKey($email));

            return $this->fail(__('L\'e-mail n\'a pas pu être envoyé pour le moment. Réessayez dans quelques minutes.'), 503);
        }

        return response()->json(['status' => 'code_sent', 'email' => $this->mask($email), 'minutes' => self::TTL_MINUTES]);
    }

    public function verify(Request $request): JsonResponse
    {
        // Code copié depuis l'e-mail (« 860 431 ») : on ne garde que les chiffres.
        $request->merge(['code' => preg_replace('/\D/', '', (string) $request->input('code'))]);
        $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => __('Saisissez le code reçu.'),
            'code.digits' => __('Le code contient 6 chiffres.'),
        ]);

        $email = (string) $request->session()->get('express_email', '');
        $entry = $email !== '' ? Cache::get($this->cacheKey($email)) : null;
        if (! is_array($entry)) {
            return $this->fail(__('Le code a expiré. Demandez-en un nouveau.'));
        }

        if (! Hash::check((string) $request->input('code'), $entry['hash'])) {
            $entry['attempts']++;
            if ($entry['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($this->cacheKey($email));

                return $this->fail(__('Trop d\'essais. Demandez un nouveau code.'));
            }
            Cache::put($this->cacheKey($email), $entry, now()->addMinutes(self::TTL_MINUTES));

            return $this->fail(__('Code incorrect. Il vous reste :n essai(s).', ['n' => self::MAX_ATTEMPTS - $entry['attempts']]));
        }

        Cache::forget($this->cacheKey($email));
        $request->session()->forget('express_email');

        $user = Auth::user();
        if ($user) {
            if (mb_strtolower($user->email) !== $email) {
                return $this->fail(__('Ce code ne correspond pas à votre compte.'));
            }
            $user->forceFill(['email_verified_at' => now()])->save();
        } else {
            $user = User::where('email', $email)->first();
            if ($user) {
                if (! $user->hasVerifiedEmail()) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
                if (! $user->phone && ! empty($entry['phone'])) {
                    $user->forceFill(['phone' => $entry['phone']])->save();
                }
            } else {
                $user = $this->accounts->createVisitor([
                    'first_name' => $entry['first_name'] ?: __('Voyageur'),
                    'last_name' => $entry['last_name'] ?? '',
                    'email' => $email,
                    'phone' => $entry['phone'] ?? null,
                ], emailVerified: true);
            }
            Auth::login($user, remember: true);
            $request->session()->regenerate();
        }

        return response()->json([
            'status' => 'ok',
            'csrf' => csrf_token(),
            'name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }

    private function cacheKey(string $email): string
    {
        return 'express_code:'.sha1($email);
    }

    private function mask(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 2).str_repeat('•', max(1, mb_strlen($local) - 2)).'@'.$domain;
    }

    private function fail(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], $status);
    }
}
