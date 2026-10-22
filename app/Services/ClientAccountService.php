<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Création des comptes client : mêmes règles quelle que soit la porte d'entrée
 * (inscription, « créer mon compte et payer », réservation express par code).
 */
class ClientAccountService
{
    /** Règle « e-mail libre » : un compte supprimé (corbeille) ne bloque pas l'adresse. */
    public static function uniqueEmailRule(): array
    {
        return ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')];
    }

    /** Mot de passe : 8 caractères minimum et, si activé, absent des fuites connues (NIST SP 800-63B). */
    public static function passwordRule(): Password
    {
        $rule = Password::min(8);

        return config('auth.password_breach_check', true) ? $rule->uncompromised() : $rule;
    }

    /** Champ piège invisible : un humain le laisse vide, un robot le remplit. */
    public static function honeypotRules(): array
    {
        return ['website' => ['nullable', 'max:0']];
    }

    /** Libère l'adresse d'un compte supprimé (contrainte d'unicité en base). */
    public static function freeTrashedEmail(string $email): void
    {
        User::withTrashed()
            ->where('email', $email)
            ->whereNotNull('deleted_at')
            ->update(['email' => 'deleted_'.time().'_'.$email]);
    }

    /**
     * Crée un compte client.
     *
     * @param  array{first_name: string, last_name: string, email: string, phone?: ?string, password?: ?string}  $data
     * @param  bool  $emailVerified  true quand l'adresse vient d'être prouvée par un code
     */
    public function createVisitor(array $data, bool $emailVerified = false): User
    {
        $email = mb_strtolower(trim($data['email']));
        self::freeTrashedEmail($email);

        $user = User::create([
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            // Sans mot de passe choisi (réservation express), un secret aléatoire : la personne se
            // connecte par code ou définit son mot de passe avec « Mot de passe oublié ».
            'password_hash' => Hash::make($data['password'] ?? Str::random(40)),
            'role' => 'visitor',
            'is_active' => true,
            'is_verified' => false,
            'terms_accepted_at' => now(),
        ]);

        if ($emailVerified) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }

    /** Inscription à la newsletter avec consentement explicite (case non cochée par défaut). */
    public function subscribeNewsletter(User $user, string $source = 'register'): void
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            return;
        }

        NewsletterSubscriber::updateOrCreate(
            ['email' => mb_strtolower($user->email)],
            [
                'first_name' => $user->first_name,
                'user_id' => $user->id,
                'locale' => app()->getLocale(),
                'source' => $source,
                'status' => 'active',
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
                'ip_address' => request()->ip(),
            ]
        );
    }

    /** Découpe « Prénom Nom » saisi dans un seul champ. */
    public static function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim($fullName), 2) ?: [''];

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }
}
