@extends('layouts.visitor-public')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil visiteur')

@section('content')
<div class="max-w-3xl mx-auto">
    @include('partials.visitor-account-nav')

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('visitor.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Informations personnelles --}}
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 space-y-5">
            <div class="flex items-center gap-2">
                <i class="fas fa-user-pen text-orange-400"></i>
                <h2 class="text-white text-sm font-semibold">Informations personnelles</h2>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-slate-800 border border-slate-700 shrink-0 flex items-center justify-center">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-orange-400 text-lg font-bold">{{ $user->initials }}</span>
                    @endif
                </div>
                <div>
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-medium cursor-pointer transition">
                        <i class="fas fa-camera"></i> Changer la photo
                        <input type="file" name="avatar" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden">
                    </label>
                    <p class="text-slate-500 text-[11px] mt-1.5">JPEG, PNG ou WEBP, 3 Mo max.</p>
                    @error('avatar') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

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
        </div>

        {{-- Sécurité --}}
        <div id="securite" class="bg-green-900 border rounded-xl p-5 space-y-4 {{ request()->query('securite') ? 'border-orange-500/50' : 'border-slate-800' }}">
            <div class="flex items-center gap-2">
                <i class="fas fa-shield-halved text-orange-400"></i>
                <h2 class="text-white text-sm font-semibold">Sécurité — changer le mot de passe</h2>
            </div>
            <p class="text-slate-500 text-xs -mt-2">Laissez ces champs vides si vous ne souhaitez pas changer de mot de passe.</p>
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
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">
                Enregistrer
            </button>
        </div>
    </form>
</div>

@if(request()->query('securite'))
<script>
    document.getElementById('securite')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
</script>
@endif
@endsection
