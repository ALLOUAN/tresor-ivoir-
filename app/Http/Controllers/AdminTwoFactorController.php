<?php

namespace App\Http\Controllers;

use App\Mail\AdminLoginCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Double authentification des administrateurs : après le mot de passe, un code à 6 chiffres
 * envoyé par e-mail est exigé. L'appareil est ensuite reconnu pendant 30 jours.
 * Activée par ADMIN_2FA_ENABLED=true.
 */
class AdminTwoFactorController extends Controller
{
    public const SESSION_KEY = 'admin_2fa_pending';

    private const COOKIE = 'ti_admin_device';

    private const CODE_TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    private const TRUST_DAYS = 30;

    public static function enabled(): bool
    {
        return (bool) config('auth.admin_two_factor');
    }

    /** Appareil déjà validé pour cet admin ? */
    public static function deviceTrusted(Request $request, User $user): bool
    {
        $value = (string) $request->cookie(self::COOKIE);

        return $value !== '' && hash_equals(self::deviceToken($user), $value);
    }

    private static function deviceToken(User $user): string
    {
        // Lié au mot de passe : un changement de mot de passe révoque tous les appareils reconnus.
        return hash_hmac('sha256', $user->id.'|'.$user->password_hash, (string) config('app.key'));
    }

    /** Démarre le défi : déconnecte la session, mémorise l'admin en attente et envoie le code. */
    public static function begin(Request $request, User $user, bool $remember): RedirectResponse
    {
        Auth::logout();
        $request->session()->put(self::SESSION_KEY, ['user_id' => $user->id, 'remember' => $remember]);
        self::sendCode($user);

        return redirect()->route('admin.two-factor.show');
    }

    private static function sendCode(User $user): void
    {
        $code = (string) random_int(100000, 999999);
        Cache::put('admin_2fa:'.$user->id, ['hash' => Hash::make($code), 'attempts' => 0], now()->addMinutes(self::CODE_TTL_MINUTES));
        Mail::to($user->email)->send(new AdminLoginCodeMail($user, $code, self::CODE_TTL_MINUTES));
    }

    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        return $pending ? User::find($pending['user_id']) : null;
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.admin-two-factor', ['email' => $user->email]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate(['code' => ['required', 'digits:6']], [], ['code' => 'code']);

        $key = 'admin_2fa:'.$user->id;
        $entry = Cache::get($key);
        if (! $entry) {
            return back()->withErrors(['code' => __('Code expiré. Demandez un nouveau code.')]);
        }
        if ($entry['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($key);

            return back()->withErrors(['code' => __('Trop d\'essais. Demandez un nouveau code.')]);
        }
        if (! Hash::check($request->input('code'), $entry['hash'])) {
            $entry['attempts']++;
            Cache::put($key, $entry, now()->addMinutes(self::CODE_TTL_MINUTES));

            return back()->withErrors(['code' => __('Code incorrect.')]);
        }

        Cache::forget($key);
        $remember = (bool) ($request->session()->get(self::SESSION_KEY)['remember'] ?? false);
        $request->session()->forget(self::SESSION_KEY);

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
        \App\Services\AccountSecurityEventLogger::log($user, \App\Models\AccountSecurityEvent::TYPE_LOGIN, $request);

        Cookie::queue(Cookie::make(self::COOKIE, self::deviceToken($user), self::TRUST_DAYS * 24 * 60, null, null, null, true, false, 'lax'));

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        self::sendCode($user);

        return back()->with('status', __('Un nouveau code vient d\'être envoyé.'));
    }
}
