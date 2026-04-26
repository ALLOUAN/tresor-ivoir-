<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\ProviderProfileBootstrap;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'role' => ['required', 'in:visitor,provider'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'role.required' => 'Veuillez choisir un type de compte.',
            'role.in' => 'Type de compte invalide.',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => true,
            'is_verified' => false,
        ]);

        if ($data['role'] === 'provider') {
            ProviderProfileBootstrap::ensure($user);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->sendEmailVerificationNotification();

        $planId = $request->query('plan') ?? session()->pull('selected_plan_id');
        if ($planId && $user->role === 'provider') {
            $plan = SubscriptionPlan::query()
                ->whereKey($planId)
                ->where('is_active', true)
                ->first();

            if ($plan) {
                return redirect()
                    ->route('subscriptions.checkout', $plan)
                    ->with('status', 'Bienvenue ! Finalisez votre abonnement ci-dessous.');
            }
        }

        if ($user->role === 'visitor') {
            return redirect()
                ->route('home')
                ->with('status', 'Bienvenue ! Un e-mail de vérification a été envoyé à votre adresse.');
        }

        return redirect()
            ->route('plans.public')
            ->with('status', 'Bienvenue ! Choisissez un forfait pour activer votre espace prestataire.');
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

        $request->session()->regenerate();

        Auth::user()->update(['last_login_at' => now()]);

        return redirect()->intended(match (Auth::user()->role) {
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
            ? back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.')
            : back()->withErrors(['email' => 'Aucun compte trouvé avec cette adresse e-mail.']);
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
            ? redirect()->route('login')->with('status', 'Votre mot de passe a été réinitialisé avec succès.')
            : back()->withErrors(['email' => 'Le lien est invalide ou a expiré. Veuillez en demander un nouveau.']);
    }

    // ── VÉRIFICATION E-MAIL ───────────────────────────────────────────────

    public function showVerifyEmail(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route($this->dashboardRoute($request->user()))
            : view('auth.verify-email');
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
            return redirect()->route($this->dashboardRoute($request->user()));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.');
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
}
