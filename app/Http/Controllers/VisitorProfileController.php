<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class VisitorProfileController extends Controller
{
    public function edit(): View
    {
        return view('visitor.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'locale' => ['required', 'in:fr,en'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (! empty($data['new_password'])) {
            if (! Hash::check((string) $data['current_password'], (string) $user->password_hash)) {
                return back()->withErrors([
                    'current_password' => 'Mot de passe actuel incorrect.',
                ])->withInput();
            }

            $user->password_hash = (string) $data['new_password'];
        }

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = mb_strtolower((string) $data['email']);
        $user->phone = $data['phone'] ?: null;
        $user->locale = $data['locale'];
        $user->save();

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
