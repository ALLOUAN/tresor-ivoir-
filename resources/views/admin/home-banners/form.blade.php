@extends('layouts.app')

@section('title', $banner->exists ? 'Modifier la bannière' : 'Ajouter une bannière')
@section('page-title', $banner->exists ? 'Modifier la bannière' : 'Ajouter une bannière')

@section('content')
@php
    $in = 'w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2.5 text-white';
    $lb = 'block text-sm font-semibold text-slate-200 mb-1.5';
@endphp
<a href="{{ route('admin.home-banners.index') }}" class="inline-flex items-center gap-2 mb-4 text-sm text-slate-400 hover:text-orange-400"><i class="fas fa-arrow-left text-xs"></i> Toutes les bannières</a>

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-200 text-sm rounded-xl">
    <ul class="list-disc pl-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $banner->exists ? route('admin.home-banners.update', $banner) : route('admin.home-banners.store') }}" enctype="multipart/form-data"
      class="bg-green-900 border border-slate-800 rounded-xl p-6 space-y-6">
    @csrf
    @if($banner->exists) @method('PUT') @endif

    <div class="grid md:grid-cols-2 gap-5">
        <div>
            <label class="{{ $lb }}" for="placement">Emplacement sur l'accueil *</label>
            <select id="placement" name="placement" class="{{ $in }}">
                @foreach(\App\Models\HomePromoBanner::PLACEMENTS as $key => $label)
                    <option value="{{ $key }}" @selected(old('placement', $banner->placement) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $lb }}" for="display_order">Ordre</label>
            <input id="display_order" name="display_order" type="number" min="0" max="999" value="{{ old('display_order', $banner->display_order ?? 0) }}" class="{{ $in }}">
            <p class="text-xs text-slate-400 mt-1">La première bannière active de l'emplacement est affichée.</p>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="kicker">Surtitre</label>
            <input id="kicker" name="kicker" type="text" maxlength="80" value="{{ old('kicker', $banner->kicker) }}" class="{{ $in }}" placeholder="Ex. : Offres du moment">
        </div>
        <div>
            <label class="{{ $lb }}" for="title_strong">Titre — partie en gras *</label>
            <input id="title_strong" name="title_strong" type="text" required maxlength="90" value="{{ old('title_strong', $banner->title_strong) }}" class="{{ $in }}" placeholder="Ex. : Séjour à Assinie">
        </div>
        <div>
            <label class="{{ $lb }}" for="title_rest">Titre — suite</label>
            <input id="title_rest" name="title_rest" type="text" maxlength="120" value="{{ old('title_rest', $banner->title_rest) }}" class="{{ $in }}" placeholder="Ex. : bientôt disponible">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="accent">Accroche (en orange)</label>
            <input id="accent" name="accent" type="text" maxlength="120" value="{{ old('accent', $banner->accent) }}" class="{{ $in }}" placeholder="Ex. : Des prix imbattables">
        </div>
        <div>
            <label class="{{ $lb }}" for="cta_label">Texte du bouton *</label>
            <input id="cta_label" name="cta_label" type="text" required maxlength="40" value="{{ old('cta_label', $banner->cta_label) }}" class="{{ $in }}" placeholder="Ex. : Réserver">
        </div>
        <div>
            <label class="{{ $lb }}" for="link_url">Lien *</label>
            <input id="link_url" name="link_url" type="text" required maxlength="500" value="{{ old('link_url', $banner->link_url) }}" class="{{ $in }}" placeholder="/residences-hotels ou https://…">
            <label class="mt-2 flex items-center gap-2 text-xs text-slate-300"><input type="checkbox" name="open_new_tab" value="1" @checked(old('open_new_tab', $banner->open_new_tab)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Ouvrir dans un nouvel onglet (lien externe)</label>
        </div>
    </div>

    <div class="pt-5 border-t border-slate-800 grid md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <p class="text-sm font-semibold text-slate-200 mb-1">Visuels qui défilent <span class="text-slate-400 font-normal">({{ count($banner->imageList()) }} / {{ \App\Models\HomePromoBanner::MAX_IMAGES }})</span></p>
            <p class="text-xs text-slate-400 mb-3">Plusieurs images défilent automatiquement à gauche de la bannière (fondu toutes les 3 s), dans l'ordre d'ajout. PNG détouré (fond transparent) conseillé, JPG/WebP acceptés, 4 Mo max par image, {{ \App\Models\HomePromoBanner::MAX_IMAGES }} images au plus.</p>
            @if($banner->imageList())
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
                    @foreach($banner->imageList() as $i => $url)
                        <label class="block rounded-lg border border-slate-700 bg-slate-800/50 p-2">
                            <img src="{{ $url }}" alt="" class="w-full h-20 object-contain rounded bg-white">
                            <span class="mt-2 flex items-center justify-between gap-2 text-xs text-slate-300"><span class="font-semibold">{{ $i + 1 }}</span>
                                <span class="flex items-center gap-1.5"><input type="checkbox" name="images_remove[]" value="{{ $url }}" class="rounded border-slate-600 bg-slate-800 text-orange-500"> Retirer</span></span>
                        </label>
                    @endforeach
                </div>
            @endif
            <input type="file" name="images_new[]" multiple accept="image/png,image/jpeg,image/webp" class="text-sm text-slate-300 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-orange-500 file:text-white file:font-semibold">
            <p class="text-xs text-slate-400 mt-1">Vous pouvez sélectionner plusieurs fichiers d'un coup ; ils s'ajoutent à la suite.</p>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="icon">Icône de repli (sans visuel)</label>
            <input id="icon" name="icon" type="text" maxlength="60" value="{{ old('icon', $banner->icon) }}" class="{{ $in }}" placeholder="fa-store, fa-hotel, fa-clapperboard…">
            <p class="text-xs text-slate-400 mt-1">Icône Font Awesome affichée à la place du visuel s'il n'y en a pas.</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-5 pt-4 border-t border-slate-800">
        <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Afficher sur l'accueil</label>
        <button type="submit" class="ml-auto px-6 py-2.5 bg-orange-500 hover:bg-orange-600 font-bold text-sm rounded-xl flex items-center gap-2"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
</form>
@endsection
