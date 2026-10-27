<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ClientAccountService;
use App\Support\PlanBundle;
use App\Support\ProviderProfileBootstrap;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $this->rememberIntendedRedirect($request);

        return view('auth.login');
    }

    public function showRegister(Request $request)
    {
        $this->rememberIntendedRedirect($request);

        if ($request->query('role') === 'visitor') {
            return view('auth.register-visitor');
        }

        $planId = (int) old('plan_id', $request->query('plan', 0));
        $selectedPlan = $planId
            ? SubscriptionPlan::query()->whereKey($planId)->where('is_active', true)->with('providerCategory')->first()
            : null;

        // Repli minimal si aucun forfait n'a été choisi en amont sur /abonnements : ne proposer
        // que les forfaits génériques (jamais un forfait réservé à une catégorie précise) — voir
        // resources/views/public/plans.blade.php pour le parcours "catégorie d'abord" canonique.
        $plans = $selectedPlan
            ? collect()
            : SubscriptionPlan::query()->where('is_active', true)->whereNull('provider_category_id')->orderBy('sort_order')->orderBy('id')->get();

        return view('auth.register', [
            'plans' => $plans,
            'selectedPlan' => $selectedPlan,
            // Secteurs supplémentaires choisis sur /abonnements (un forfait par secteur, payés ensemble).
            'bundleItems' => $selectedPlan
                ? PlanBundle::items($selectedPlan, old('category_slug', $request->query('categorie')), old('avec', $request->query('avec')))
                : collect(),
            'selectedPlanId' => $planId,
            'selectedCategorySlug' => old('category_slug', $request->query('categorie')),
        ]);
    }

    /**
     * Mémorise l'URL d'origine (ex: page de réservation) via le mécanisme
     * standard `redirect()->intended()`, déjà utilisé après login/vérification e-mail.
     * N'accepte qu'un chemin relatif local (jamais une URL absolue/externe) pour éviter
     * tout open-redirect via ce paramètre.
     */
    private function rememberIntendedRedirect(Request $request): void
    {
        $redirect = $request->query('redirect');

        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            session(['url.intended' => url($redirect)]);
        }
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ClientAccountService::uniqueEmailRule(),
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', ClientAccountService::passwordRule()],
            'role' => ['required', 'in:visitor,provider'],
            'terms' => ['accepted'],
            'newsletter_opt_in' => ['nullable', 'boolean'],
            ...ClientAccountService::honeypotRules(),
        ], [
            'website.max' => 'Envoi refusé.',
            'password.uncompromised' => 'Ce mot de passe figure dans une fuite de données connue : choisissez-en un autre.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'role.required' => 'Veuillez choisir un type de compte.',
            'role.in' => 'Type de compte invalide.',
            'terms.accepted' => 'Vous devez accepter les conditions générales d\'utilisation.',
        ]);

        // Libérer l'email si un compte soft-deleted l'occupe encore (évite la contrainte unique MySQL)
        ClientAccountService::freeTrashedEmail($data['email']);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => true,
            'is_verified' => false,
            'terms_accepted_at' => now(),
        ]);

        $planId = $request->input('plan_id') ?: ($request->query('plan') ?? session()->pull('selected_plan_id'));
        $plan = $planId
            ? SubscriptionPlan::query()->whereKey($planId)->where('is_active', true)->with('providerCategory')->first()
            : null;

        if ($data['role'] === 'provider') {
            // Le forfait choisi fait autorité s'il est déjà rattaché à une catégorie précise (ex.
            // Art & Créations) ; sinon on utilise la catégorie choisie sur /abonnements (forfait générique).
            $rawCategorySlug = $request->input('category_slug');
            $categorySlug = $plan?->providerCategory?->slug ?? (is_string($rawCategorySlug) ? $rawCategorySlug : null);

            $provider = ProviderProfileBootstrap::ensure($user, $categorySlug);
            // Secteurs supplémentaires déclarés à l'inscription : l'espace de chacun est ouvert dès maintenant.
            $extraItems = $plan ? PlanBundle::extras($plan, $categorySlug, $request->input('avec')) : collect();
            foreach ($extraItems as $item) {
                $provider?->addSector($item['sector']->slug);
            }
        }

        if ($user->role === 'visitor' && $request->boolean('newsletter_opt_in')) {
            app(ClientAccountService::class)->subscribeNewsletter($user, 'register');
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Une panne du serveur d'e-mails ne doit pas faire échouer l'inscription : le compte est créé, le code se renvoie ensuite.
        if (! $this->sendEmailVerificationCode($user)) {
            session()->flash('mail_warning', __('L\'e-mail de vérification n\'a pas pu être envoyé pour le moment. Réessayez dans quelques minutes avec « Renvoyer le code ».'));
        }

        if ($plan && $user->role === 'provider') {
            $avec = PlanBundle::encode($extraItems ?? collect());
            $sectorSlug = $plan->providerCategory ? null : ($categorySlug ?? null);
            session([
                'post_email_verification_subscription_plan_id' => $plan->id,
                'post_email_verification_subscription_extra_ids' => $avec,
                'post_email_verification_subscription_sector' => $sectorSlug,
            ]);

            return redirect()
                ->route('subscriptions.checkout', array_filter(['plan' => $plan, 'categorie' => $sectorSlug, 'avec' => $avec ?: null]))
                ->with('status', __('Bienvenue ! Finalisez votre abonnement ci-dessous.'));
        }

        // Client : l'adresse e-mail se valide d'abord (code à 6 chiffres) ; l'espace client n'est ouvert
        // qu'ensuite, puis retour à la page d'origine (ex. réservation) ou au tableau de bord.
        if ($user->role === 'visitor') {
            return redirect()
                ->route('verification.notice')
                ->with('status', session()->has('mail_warning')
                    ? __('Bienvenue ! Votre compte est créé : il reste à confirmer votre adresse e-mail.')
                    : __('Bienvenue ! Saisissez le code à 6 chiffres envoyé à votre adresse e-mail pour activer votre compte.'));
        }

        return redirect()
            ->route('plans.public')
            ->with('status', __('Bienvenue ! Choisissez un forfait pour activer votre espace prestataire.'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email ou mot de passe incorrect.',
            ])->onlyInput('email');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Compte désactivé (par l'administration, ou membre retiré d'une équipe prestataire).
        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => __('Ce compte est désactivé. Contactez-nous si vous pensez qu\'il s\'agit d\'une erreur.')])->onlyInput('email');
        }

        // Administrateurs : second facteur (code par e-mail) sauf appareil déjà reconnu.
        if ($user->role === 'admin' && AdminTwoFactorController::enabled() && ! AdminTwoFactorController::deviceTrusted($request, $user)) {
            return AdminTwoFactorController::begin($request, $user, $request->boolean('remember'));
        }

        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);
        \App\Services\AccountSecurityEventLogger::log($user, \App\Models\AccountSecurityEvent::TYPE_LOGIN, $request);

        return redirect()->intended(match ($user->role) {
            'admin' => route('admin.dashboard'),
            'editor' => route('editor.dashboard'),
            'provider' => route('provider.dashboard'),
            default => route('visitor.dashboard'),
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── MOT DE PASSE OUBLIÉ ───────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'Adresse e-mail invalide.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __('Un lien de réinitialisation a été envoyé à votre adresse e-mail.'))
            : back()->withErrors(['email' => __('Aucun compte trouvé avec cette adresse e-mail.')]);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password_hash' => Hash::make($password)])->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __('Votre mot de passe a été réinitialisé avec succès.'))
            : back()->withErrors(['email' => __('Le lien est invalide ou a expiré. Veuillez en demander un nouveau.')]);
    }

    // ── VÉRIFICATION E-MAIL ───────────────────────────────────────────────

    public function showVerifyEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectAfterEmailVerification($request->user());
        }

        if (! $this->hasActiveVerificationCode($request->user()) && ! $this->sendEmailVerificationCode($request->user())) {
            session()->now('mail_warning', __('L\'e-mail de vérification n\'a pas pu être envoyé pour le moment. Réessayez dans quelques minutes avec « Renvoyer le code ».'));
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request, string $id, string $hash)
    {
        abort_unless($request->user()->getKey() == $id, 403);
        abort_unless(hash_equals(sha1($request->user()->getEmailForVerification()), $hash), 403);

        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->markEmailAsVerified();
            event(new Verified($request->user()));
        }

        return redirect()->route($this->dashboardRoute($request->user()))
            ->with('verified', true);
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectAfterEmailVerification($request->user());
        }

        if (! $this->sendEmailVerificationCode($request->user())) {
            return back()->with('mail_warning', __('L\'e-mail de vérification n\'a pas pu être envoyé pour le moment. Réessayez dans quelques minutes avec « Renvoyer le code ».'));
        }

        return back()->with('status', __('Un nouveau code de vérification a été envoyé à votre adresse e-mail.'));
    }

    public function verifyEmailCode(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->redirectAfterEmailVerification($user);
        }

        // Code copié depuis l'e-mail (« 860 431 ») : on ne garde que les chiffres.
        $request->merge(['verification_code' => preg_replace('/\D/', '', (string) $request->input('verification_code'))]);

        $validated = $request->validate([
            'verification_code' => ['required', 'digits:6'],
        ], [
            'verification_code.required' => 'Veuillez saisir le code de vérification.',
            'verification_code.digits' => 'Le code de vérification doit contenir 6 chiffres.',
        ]);

        $cacheKey = $this->verificationCodeCacheKey($user);
        $cached = Cache::get($cacheKey);

        if (! is_array($cached) || empty($cached['hash']) || ! isset($cached['expires_at'])) {
            return back()->withErrors([
                'verification_code' => 'Le code a expiré. Cliquez sur "Renvoyer le code".',
            ]);
        }

        $expiresAtTs = $this->resolveExpiryTimestamp($cached['expires_at']);
        if ($expiresAtTs === null || now()->timestamp > $expiresAtTs) {
            Cache::forget($cacheKey);

            return back()->withErrors([
                'verification_code' => 'Le code a expiré. Cliquez sur "Renvoyer le code".',
            ]);
        }

        if (! Hash::check($validated['verification_code'], $cached['hash'])) {
            return back()->withErrors([
                'verification_code' => 'Le code saisi est incorrect.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->forceFill(['is_verified' => true])->save();
            event(new Verified($user));
        }

        Cache::forget($cacheKey);

        return $this->redirectAfterEmailVerification($user)->with('verified', true);
    }

    /**
     * Après vérification e-mail : retour prioritaire vers la page publique de paiement d’abonnement
     * si un plan a été mémorisé (flux depuis /abonnements/{plan}/paiement).
     */
    private function redirectAfterEmailVerification(User $user): RedirectResponse
    {
        $planId = session()->pull('post_email_verification_subscription_plan_id');
        $avec = (string) session()->pull('post_email_verification_subscription_extra_ids', '');
        $sectorSlug = session()->pull('post_email_verification_subscription_sector');

        if ($user->role === 'provider' && $planId) {
            $plan = SubscriptionPlan::query()
                ->whereKey((int) $planId)
                ->where('is_active', true)
                ->first();

            if ($plan) {
                return redirect()->route('subscriptions.checkout', array_filter(['plan' => $plan, 'categorie' => $sectorSlug, 'avec' => $avec ?: null]));
            }
        }

        return redirect()->intended(route($this->dashboardRoute($user)));
    }

    private function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'editor' => 'editor.dashboard',
            'provider' => 'provider.dashboard',
            default => 'visitor.dashboard',
        };
    }

    /** Envoie un nouveau code ; renvoie false (erreur journalisée) si le serveur d'e-mails ne répond pas. */
    /** Envoi du code : logique commune au site et à l'API mobile (App\Services\EmailVerificationCodeService). */
    private function sendEmailVerificationCode(User $user): bool
    {
        return app(\App\Services\EmailVerificationCodeService::class)->send($user);
    }

    private function hasActiveVerificationCode(User $user): bool
    {
        $cached = Cache::get($this->verificationCodeCacheKey($user));
        $expiresAtTs = is_array($cached) ? $this->resolveExpiryTimestamp($cached['expires_at'] ?? null) : null;

        return is_array($cached)
            && ! empty($cached['hash'])
            && $expiresAtTs !== null
            && now()->timestamp <= $expiresAtTs;
    }

    private function verificationCodeCacheKey(User $user): string
    {
        return \App\Services\EmailVerificationCodeService::cacheKey($user);
    }

    private function resolveExpiryTimestamp(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            if (ctype_digit($value)) {
                return (int) $value;
            }

            $parsed = strtotime($value);

            return $parsed !== false ? $parsed : null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        return null;
    }
}
