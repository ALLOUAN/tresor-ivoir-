@extends('layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil visiteur')

@section('content')
<div class="max-w-3xl mx-auto">
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('visitor.profile.update') }}" class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-300 mb-1">Prénom</label>
                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                @error('first_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Nom</label>
                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                @error('last_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-300 mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Langue</label>
            <select name="locale" class="w-full md:w-64 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="fr" @selected(old('locale', $user->locale) === 'fr')>Français</option>
                <option value="en" @selected(old('locale', $user->locale) === 'en')>English</option>
            </select>
            @error('locale') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="border-t border-slate-800 pt-5">
            <p class="text-white text-sm font-semibold mb-3">Changer le mot de passe (optionnel)</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    @error('current_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Nouveau mot de passe</label>
                    <input type="password" name="new_password" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    @error('new_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Confirmation</label>
                    <input type="password" name="new_password_confirmation" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
