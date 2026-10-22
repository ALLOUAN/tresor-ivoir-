<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Comptes équipe (réservé au propriétaire) : invitation d'un employé, qui reçoit un e-mail pour
 * choisir son mot de passe, et retrait de l'accès. Limité aux opérations du quotidien
 * (cf. ProviderStaffGate).
 */
class TeamController extends Controller
{
    private const MAX_MEMBERS = 15;

    private function ownedProvider(): Provider
    {
        $provider = Provider::where('user_id', Auth::id())->first();
        abort_unless($provider, 403, 'Réservé au responsable du compte.');

        return $provider;
    }

    public function index(): View
    {
        $provider = $this->ownedProvider();
        $members = $provider->members()->with('user')->latest()->get();

        return view('provider.team.index', compact('provider', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->ownedProvider();
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => \App\Services\ClientAccountService::uniqueEmailRule(),
            'job_title' => ['nullable', 'string', 'max:80'],
        ], ['email.unique' => 'Cette adresse a déjà un compte sur le site : utilisez une adresse professionnelle dédiée.'], ['first_name' => 'prénom', 'last_name' => 'nom', 'job_title' => 'poste']);

        if ($provider->members()->count() >= self::MAX_MEMBERS) {
            return back()->with('error', 'Nombre maximal de membres atteint ('.self::MAX_MEMBERS.').');
        }

        DB::transaction(function () use ($data, $provider) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password_hash' => Str::random(40), // remplacé par le membre via le lien reçu
                'role' => 'provider',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(), // adresse garantie par le responsable qui invite
            ]);
            ProviderMember::create(['provider_id' => $provider->id, 'user_id' => $user->id, 'invited_by' => Auth::id(), 'job_title' => $data['job_title'] ?? null]);
        });

        // Lien « choisir mon mot de passe » (même mécanisme que le mot de passe oublié).
        Password::sendResetLink(['email' => $data['email']]);

        return back()->with('success', $data['first_name'].' a été invité(e) : un e-mail lui permet de choisir son mot de passe.');
    }

    public function destroy(ProviderMember $member): RedirectResponse
    {
        $provider = $this->ownedProvider();
        abort_unless((int) $member->provider_id === (int) $provider->id, 404);

        DB::transaction(function () use ($member) {
            $user = $member->user;
            $member->delete();
            // Le compte ne servait qu'à l'équipe : il est désactivé (l'historique reste lisible).
            $user?->update(['is_active' => false]);
        });

        return back()->with('success', 'Accès retiré.');
    }
}
