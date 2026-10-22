@extends('layouts.app')

@section('title', 'Page artiste')
@section('page-title', 'Page artiste, livraison et sur mesure')

@section('header-actions')
    <a href="{{ route('artist.show', $profile->slug) }}" target="_blank" class="inline-flex items-center gap-2 text-slate-300 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2"><i class="fas fa-arrow-up-right-from-square text-xs"></i> Voir ma page</a>
@endsection

@section('content')
@include('provider.art._nav')
@php $in = 'w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 mt-1'; $sec = 'bg-green-900 border border-slate-800 rounded-xl p-5 space-y-3'; @endphp
<form method="POST" action="{{ route('provider.artist-profile.update') }}" enctype="multipart/form-data" class="max-w-3xl space-y-4">
    @csrf @method('PUT')
    <div class="{{ $sec }}">
        <p class="text-white text-sm font-semibold"><i class="fas fa-id-badge text-orange-400 mr-1"></i>Présentation</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="text-xs text-slate-400">Nom d'artiste<input name="display_name" required maxlength="150" value="{{ old('display_name', $profile->display_name) }}" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Ville<input name="city" maxlength="120" value="{{ old('city', $profile->city) }}" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400 sm:col-span-2">Accroche<input name="tagline" maxlength="200" value="{{ old('tagline', $profile->tagline) }}" placeholder="Peintre de la lumière lagunaire" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Année de naissance<input type="number" name="birth_year" value="{{ old('birth_year', $profile->birth_year) }}" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Photo de profil<input type="file" name="photo" accept="image/*" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400 sm:col-span-2">Biographie<textarea name="bio" rows="4" maxlength="5000" class="{{ $in }}">{{ old('bio', $profile->bio) }}</textarea></label>
            <label class="text-xs text-slate-400 sm:col-span-2">Démarche artistique<textarea name="statement" rows="3" maxlength="5000" class="{{ $in }}">{{ old('statement', $profile->statement) }}</textarea></label>
            <label class="text-xs text-slate-400">Expositions (une par ligne)<textarea name="exhibitions" rows="4" maxlength="5000" placeholder="2025 — Abidjan Art Week, Galerie Cécile Fakhoury" class="{{ $in }}">{{ old('exhibitions', $profile->exhibitions) }}</textarea></label>
            <label class="text-xs text-slate-400">Formation (une par ligne)<textarea name="education" rows="4" maxlength="3000" placeholder="INSAAC, Abidjan" class="{{ $in }}">{{ old('education', $profile->education) }}</textarea></label>
            <label class="text-xs text-slate-400 sm:col-span-2">Photo de l'atelier<input type="file" name="studio_photo" accept="image/*" class="{{ $in }}"></label>
        </div>
    </div>
    <div class="{{ $sec }}">
        <p class="text-white text-sm font-semibold"><i class="fas fa-truck text-orange-400 mr-1"></i>Livraison, assurance, retours</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="text-xs text-slate-400 sm:col-span-2">Adresse de retrait à l'atelier<input name="pickup_address" maxlength="255" value="{{ old('pickup_address', $profile->pickup_address) }}" placeholder="Cocody Angré, 8e tranche" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Livraison à Abidjan (XOF, vide = non proposée)<input type="number" name="abidjan_delivery_xof" min="0" value="{{ old('abidjan_delivery_xof', $profile->abidjan_delivery_xof) }}" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Livraison hors Abidjan (XOF, vide = non proposée)<input type="number" name="country_delivery_xof" min="0" value="{{ old('country_delivery_xof', $profile->country_delivery_xof) }}" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Supplément grand format &gt; 100 cm (XOF)<input type="number" name="large_supplement_xof" min="0" value="{{ old('large_supplement_xof', $profile->large_supplement_xof) }}" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Assurance transport (% de la valeur)<input type="number" step="0.5" name="insurance_percent" min="0" max="10" required value="{{ old('insurance_percent', $profile->insurance_percent) }}" class="{{ $in }}"></label>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-300"><input type="hidden" name="returns_accepted" value="0"><input type="checkbox" name="returns_accepted" value="1" @checked(old('returns_accepted', $profile->returns_accepted)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Accepter les retours sous {{ \App\Models\ArtworkOrder::RETURN_DAYS }} jours (recommandé : rassure les acheteurs en ligne)</label>
        <p class="text-slate-500 text-[11px]">Avec les retours acceptés, votre part est disponible {{ \App\Models\ArtworkOrder::RETURN_DAYS }} jours après la réception confirmée par l'acheteur.</p>
    </div>
    <div class="{{ $sec }}">
        <p class="text-white text-sm font-semibold"><i class="fas fa-paintbrush text-orange-400 mr-1"></i>Commandes sur mesure</p>
        <label class="flex items-center gap-2 text-sm text-slate-300"><input type="hidden" name="accepts_commissions" value="0"><input type="checkbox" name="accepts_commissions" value="1" @checked(old('accepts_commissions', $profile->accepts_commissions)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> J'accepte les commandes sur mesure</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="text-xs text-slate-400">À partir de (XOF)<input type="number" name="commission_from_xof" min="0" value="{{ old('commission_from_xof', $profile->commission_from_xof) }}" class="{{ $in }}"></label>
            <label class="text-xs text-slate-400">Acompte demandé<select name="commission_deposit_percent" class="{{ $in }}">@foreach([30, 40, 50] as $p)<option value="{{ $p }}" @selected((int) old('commission_deposit_percent', $profile->commission_deposit_percent) === $p)>{{ $p }} %</option>@endforeach</select></label>
        </div>
    </div>
    <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-5 py-2.5 rounded-lg"><i class="fas fa-floppy-disk"></i> Enregistrer</button>
</form>
@endsection
