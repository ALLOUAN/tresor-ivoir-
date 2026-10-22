@extends('layouts.app')

@section('title', $bundle->exists ? 'Modifier l\'offre' : 'Nouvelle offre groupée')
@section('page-title', $bundle->exists ? 'Modifier l\'offre groupée' : 'Nouvelle offre groupée')

@section('header-actions')
    <a href="{{ route('admin.bundles.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour
    </a>
@endsection

@section('content')
@php
    $field = 'w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none';
    $selected = collect(old('includes') ? collect(old('includes'))->flatMap(fn ($ids, $type) => collect($ids)->map(fn ($id) => ['type' => $type, 'id' => (int) $id])) : ($bundle->includes ?? []));
    $isSelected = fn ($type, $id) => $selected->contains(fn ($i) => $i['type'] === $type && (int) $i['id'] === (int) $id);
@endphp

@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
@endif

<form method="POST" action="{{ $bundle->exists ? route('admin.bundles.update', $bundle) : route('admin.bundles.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf
    @if($bundle->exists) @method('PUT') @endif

    <div class="bg-green-900 border border-slate-800 rounded-xl p-6 space-y-4">
        <h2 class="text-white font-semibold">L'offre</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Titre *</label>
                <input name="title" required maxlength="180" value="{{ old('title', $bundle->title) }}" placeholder="Week-end à Grand-Bassam" class="{{ $field }}">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Accroche (affichée sur la carte)</label>
                <input name="summary" maxlength="300" value="{{ old('summary', $bundle->summary) }}" class="{{ $field }}">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Programme / description</label>
                <textarea name="description" rows="6" class="{{ $field }} resize-y" placeholder="Jour 1 : arrivée, déjeuner au bord de la lagune…">{{ old('description', $bundle->description) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Ce qui est compris (une ligne par élément)</label>
                <textarea name="highlights" rows="4" class="{{ $field }} resize-y" placeholder="2 nuits en chambre double&#10;Visite guidée du quartier France&#10;Transferts depuis Abidjan">{{ old('highlights', implode("\n", $bundle->highlights ?? [])) }}</textarea>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Prestataire porteur * <span class="text-slate-500">(reçoit les demandes et le paiement)</span></label>
                <select name="provider_id" required class="{{ $field }}">
                    <option value="">— Choisir —</option>
                    @foreach($providers as $p)
                        <option value="{{ $p->id }}" @selected(old('provider_id', $bundle->provider_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Ville</label>
                <select name="city_id" class="{{ $field }}">
                    <option value="">— Aucune —</option>
                    @foreach($cities as $c)
                        <option value="{{ $c->id }}" @selected(old('city_id', $bundle->city_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3 md:col-span-2 sm:grid-cols-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Durée (jours) *</label>
                    <input type="number" name="duration_days" min="1" max="60" required value="{{ old('duration_days', $bundle->duration_days) }}" class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Prix / personne (XOF) *</label>
                    <input type="number" name="price_xof" min="5" step="5" required value="{{ old('price_xof', $bundle->price_xof) }}" class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Voyageurs min *</label>
                    <input type="number" name="min_people" min="1" required value="{{ old('min_people', $bundle->min_people) }}" class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Voyageurs max</label>
                    <input type="number" name="max_people" min="1" value="{{ old('max_people', $bundle->max_people) }}" class="{{ $field }}">
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Photo de couverture</label>
                <input type="file" name="cover_image_file" accept="image/*" class="text-xs text-slate-300">
                @if($bundle->cover_url)<img src="{{ $bundle->cover_url }}" alt="" class="mt-2 h-20 rounded-lg object-cover">@endif
            </div>
            <div class="flex flex-col gap-2 text-sm text-slate-300">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $bundle->is_active))> Publiée</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $bundle->is_featured))> Mise en avant (« Coup de cœur »)</label>
                <label class="flex items-center gap-2">Ordre <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $bundle->sort_order ?? 0) }}" class="w-20 {{ $field }}"></label>
            </div>
        </div>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-1">Adresses incluses</h2>
        <p class="text-slate-500 text-xs mb-4">Les fiches cochées apparaissent sur l'offre avec un lien vers leur page. Maintenez Ctrl (ou Cmd) pour en choisir plusieurs.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($catalog as $type => $group)
                <div>
                    <label class="block text-xs text-slate-400 mb-1">{{ $group['label'] }}</label>
                    <select name="includes[{{ $type }}][]" multiple size="5" class="{{ $field }}">
                        @foreach($group['items'] as $item)
                            <option value="{{ $item->id }}" @selected($isSelected($type, $item->id))>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>

    <details class="bg-green-900 border border-slate-800 rounded-xl p-6">
        <summary class="text-white font-semibold cursor-pointer">Version anglaise (facultatif)</summary>
        <div class="space-y-3 mt-4">
            <input name="title_en" maxlength="180" value="{{ old('title_en', $bundle->title_en) }}" placeholder="Title" class="{{ $field }}">
            <input name="summary_en" maxlength="300" value="{{ old('summary_en', $bundle->summary_en) }}" placeholder="Summary" class="{{ $field }}">
            <textarea name="description_en" rows="5" placeholder="Description" class="{{ $field }} resize-y">{{ old('description_en', $bundle->description_en) }}</textarea>
        </div>
    </details>

    <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-5 py-2.5 rounded-lg">
        <i class="fas fa-floppy-disk"></i> {{ $bundle->exists ? 'Enregistrer' : 'Créer l\'offre' }}
    </button>
</form>
@endsection
