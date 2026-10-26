@extends('layouts.app')

@section('title', 'Mon compte')
@section('page-title', 'Mon compte')

@section('content')
@php
    $roleLabel = ['admin' => 'Admin', 'editor' => 'Éditeur', 'provider' => 'Prestataire'][$user->role] ?? ucfirst($user->role);
    $pwErrors = $errors->getBag('password');
@endphp
<style>
    .acc { max-width: 760px; margin: 0 auto; display: grid; gap: 20px; }
    .acc-card { background: #fff; border: 1px solid #F3F8F5; border-radius: 16px; padding: 22px; }
    .acc-id { display: flex; align-items: center; gap: 16px; }
    .acc-av { width: 64px; height: 64px; flex: none; border-radius: 999px; background: #6b7fe0; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 600; overflow: hidden; }
    .acc-av img { width: 100%; height: 100%; object-fit: cover; }
    .acc-id b { display: block; font-size: 18px; color: #1f2937; }
    .acc-id small { display: block; margin-top: 2px; font-size: 13.5px; color: #557466; }
    .acc-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; padding: 3px 10px; border-radius: 8px; background: #e0ecff; color: #2563eb; font-size: 12.5px; font-weight: 700; }
    .acc h2 { margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 8px; }
    .acc h2 i { color: #EB6E00; }
    .acc-hint { margin: 0 0 16px; font-size: 13px; color: #557466; }
    .acc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .acc-field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #24503D; }
    .acc-field input { width: 100%; height: 42px; padding: 0 12px; border: 1px solid #d6dbe4; border-radius: 10px; background: #fff; color: #111827; font-size: 14px; }
    .acc-field input:focus { outline: none; border-color: #EB6E00; box-shadow: 0 0 0 3px rgba(235,110,0,.18); }
    .acc-field .acc-err { margin-top: 5px; font-size: 12.5px; color: #dc2626; }
    .acc-full { grid-column: 1 / -1; }
    .acc-actions { display: flex; justify-content: flex-end; margin-top: 18px; }
    .acc-btn { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border: 0; border-radius: 10px; background: #EB6E00; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; }
    .acc-btn:hover { background: #EB6E00; }
    #mot-de-passe { scroll-margin-top: 80px; }
    html.dark .acc-card { background: #006B3F; border-color: #00874F; }
    html.dark .acc-id b, html.dark .acc h2 { color: #F3F8F5; }
    html.dark .acc-id small, html.dark .acc-hint { color: #8AA99B; }
    html.dark .acc-field label { color: #D8E9E0; }
    html.dark .acc-field input { background: #006B3F; border-color: #00874F; color: #F3F8F5; }
    html.dark .acc-badge { background: rgba(37,99,235,.18); color: #93c5fd; }
    @media (max-width: 640px) { .acc-grid { grid-template-columns: minmax(0, 1fr); } .acc-card { padding: 16px; } .acc-btn { width: 100%; justify-content: center; } }
</style>

<div class="acc">
    <div class="acc-card acc-id">
        <span class="acc-av">
            @if($user->avatar_url)<img src="{{ $user->avatar_url }}" alt="">@else{{ $user->initials }}@endif
        </span>
        <div class="min-w-0">
            <b>{{ $user->full_name }}</b>
            <small>{{ $user->email }}</small>
            <span class="acc-badge"><i class="fas fa-shield-halved"></i>{{ $roleLabel }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('account.profile.update') }}" class="acc-card">
        @csrf
        @method('PUT')
        <h2><i class="fas fa-circle-user"></i>Mon profil</h2>
        <p class="acc-hint">Vos informations de connexion et de contact.</p>
        <div class="acc-grid">
            <div class="acc-field">
                <label for="acc-first">Prénom</label>
                <input id="acc-first" name="first_name" value="{{ old('first_name', $user->first_name) }}" required maxlength="100" autocomplete="given-name">
                @error('first_name')<p class="acc-err">{{ $message }}</p>@enderror
            </div>
            <div class="acc-field">
                <label for="acc-last">Nom</label>
                <input id="acc-last" name="last_name" value="{{ old('last_name', $user->last_name) }}" required maxlength="100" autocomplete="family-name">
                @error('last_name')<p class="acc-err">{{ $message }}</p>@enderror
            </div>
            <div class="acc-field">
                <label for="acc-email">E-mail</label>
                <input id="acc-email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                @error('email')<p class="acc-err">{{ $message }}</p>@enderror
            </div>
            <div class="acc-field">
                <label for="acc-phone">Téléphone</label>
                <input id="acc-phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" autocomplete="tel">
                @error('phone')<p class="acc-err">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="acc-actions">
            <button type="submit" class="acc-btn"><i class="fas fa-check"></i>Enregistrer</button>
        </div>
    </form>

    <form method="POST" action="{{ route('account.password.update') }}" class="acc-card" id="mot-de-passe">
        @csrf
        @method('PUT')
        <h2><i class="fas fa-key"></i>Modifier mot de passe</h2>
        <p class="acc-hint">8 caractères minimum.</p>
        <div class="acc-grid">
            <div class="acc-field acc-full">
                <label for="acc-cur">Mot de passe actuel</label>
                <input id="acc-cur" type="password" name="current_password" required autocomplete="current-password">
                @if($pwErrors->has('current_password'))<p class="acc-err">{{ $pwErrors->first('current_password') }}</p>@endif
            </div>
            <div class="acc-field">
                <label for="acc-new">Nouveau mot de passe</label>
                <input id="acc-new" type="password" name="new_password" required minlength="8" autocomplete="new-password">
                @if($pwErrors->has('new_password'))<p class="acc-err">{{ $pwErrors->first('new_password') }}</p>@endif
            </div>
            <div class="acc-field">
                <label for="acc-new2">Confirmer le mot de passe</label>
                <input id="acc-new2" type="password" name="new_password_confirmation" required minlength="8" autocomplete="new-password">
            </div>
        </div>
        <div class="acc-actions">
            <button type="submit" class="acc-btn"><i class="fas fa-key"></i>Changer le mot de passe</button>
        </div>
    </form>
</div>
@endsection
