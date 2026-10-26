<?php

namespace App\Http\Controllers;

use App\Models\AccountSecurityEvent;
use App\Services\AccountSecurityEventLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * « Mon compte » du back-office (admin, éditeur, prestataire) : identité et mot de passe
 * de l'utilisateur connecté. Mêmes règles que VisitorProfileController.
 */
class AccountController extends Controller
{
    public function edit(): View
    {
        return view('account.edit', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $emailChanged = mb_strtolower((string) $data['email']) !== mb_strtolower((string) $user->email);
        $phoneChanged = ($data['phone'] ?: null) !== $user->phone;

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = mb_strtolower((string) $data['email']);
        $user->phone = $data['phone'] ?: null;
        $user->save();

        if ($emailChanged) {
            AccountSecurityEventLogger::log($user, AccountSecurityEvent::TYPE_EMAIL_CHANGED, $request);
        }
        if ($phoneChanged) {
            AccountSecurityEventLogger::log($user, AccountSecurityEvent::TYPE_PHONE_CHANGED, $request);
        }

        return back()->with('success', __('Profil mis à jour avec succès.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check((string) $data['current_password'], (string) $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.'], 'password');
        }

        $user->password_hash = (string) $data['new_password'];
        $user->save();

        AccountSecurityEventLogger::log($user, AccountSecurityEvent::TYPE_PASSWORD_CHANGED, $request);

        return redirect()->to(route('account.edit').'#mot-de-passe')->with('success', __('Mot de passe modifié.'));
    }
}
