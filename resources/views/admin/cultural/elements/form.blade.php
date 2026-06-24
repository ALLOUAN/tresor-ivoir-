@extends('layouts.app')

@section('title', isset($element) ? 'Modifier — '.$element->name : 'Nouvel élément culturel')
@section('page-title', isset($element) ? 'Modifier l\'élément' : 'Nouvel élément culturel')

@section('header-actions')
<a href="{{ route('admin.cultural.elements.index') }}"
    class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-arrow-left"></i> Retour à la liste
</a>
@endsection

@section('content')

@include('admin.cultural.partials.subnav')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" enctype="multipart/form-data"
    action="{{ isset($element) ? route('admin.cultural.elements.update', $element) : route('admin.cultural.elements.store') }}"
    class="space-y-6">
    @csrf
    @isset($element) @method('PUT') @endisset

    {{-- ── 1. Informations générales ──────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
            <i class="fas fa-info-circle text-amber-400"></i> Informations générales
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block text-xs text-slate-400 mb-1">Domaine culturel <span class="text-red-400">*</span></label>
                <select name="domain_id" required
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <option value="">— Choisir un domaine —</option>
                    @foreach($domains as $d)
                    <option value="{{ $d->id }}" {{ old('domain_id', $element->domain_id ?? '') == $d->id ? 'selected' : '' }}>
                        {{ $d->parent ? $d->parent->name.' › ' : '' }}{{ $d->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Niveau de risque</label>
                <select name="niveau_risque"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    @foreach(['stable'=>'Stable','vulnerable'=>'Vulnérable','en_danger'=>'En danger','disparu'=>'Disparu'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('niveau_risque', $element->niveau_risque ?? 'stable') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Nom de l'élément <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $element->name ?? '') }}" required maxlength="150"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Description courte <span class="text-slate-600">(max 300 car.)</span></label>
                <input type="text" name="short_description" value="{{ old('short_description', $element->short_description ?? '') }}" maxlength="300"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Description complète</label>
                <textarea name="description" rows="5"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ old('description', $element->description ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Origine historique</label>
                <textarea name="origine_historique" rows="3"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ old('origine_historique', $element->origine_historique ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Statut UNESCO</label>
                <input type="text" name="unesco_status" value="{{ old('unesco_status', $element->unesco_status ?? '') }}" maxlength="100"
                    placeholder="Ex: Patrimoine culturel immatériel"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Site web</label>
                <input type="url" name="website" value="{{ old('website', $element->website ?? '') }}" maxlength="300"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
        </div>
    </div>

    {{-- ── 2. Image principale ─────────────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
            <i class="fas fa-image text-amber-400"></i> Images
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs text-slate-400 mb-2">Thumbnail (URL)</label>
                <input type="url" name="thumbnail" value="{{ old('thumbnail', $element->thumbnail ?? '') }}" maxlength="500"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                @isset($element) @if($element->thumbnail)
                <img src="{{ $element->thumbnail }}" class="mt-2 h-20 rounded-lg object-cover">
                @endif @endisset
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-2">Image bannière (URL)</label>
                <input type="url" name="cover_image" value="{{ old('cover_image', $element->cover_image ?? '') }}" maxlength="500"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                @isset($element) @if($element->cover_image)
                <img src="{{ $element->cover_image }}" class="mt-2 h-20 w-full rounded-lg object-cover">
                @endif @endisset
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-2">Ou uploader une image thumbnail</label>
                <input type="file" name="thumbnail_file" accept="image/*"
                    class="w-full text-xs text-slate-400 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-amber-500 file:text-black file:font-semibold">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-2">Ou uploader une image bannière</label>
                <input type="file" name="cover_image_file" accept="image/*"
                    class="w-full text-xs text-slate-400 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-amber-500 file:text-black file:font-semibold">
            </div>
        </div>
    </div>

    {{-- ── 3. Peuples associés ─────────────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-people-group text-amber-400"></i> Peuples associés
            </h2>
            <button type="button" onclick="addPeopleRow()"
                class="text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                <i class="fas fa-plus text-[10px]"></i> Ajouter
            </button>
        </div>
        <div id="people-rows" class="space-y-3">
            @php $existingPeoples = old('people_id', collect($element->people_roles ?? [])->pluck('people_id')->toArray()); @endphp
            @forelse(old('people_id', $element->people_roles ?? []) as $i => $pr)
            <div class="flex gap-3 items-center people-row">
                <select name="people_id[]"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <option value="">— Peuple —</option>
                    @foreach($peoples as $p)
                    <option value="{{ $p->id }}" {{ (is_array($pr) ? $pr['people_id'] : $pr) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="people_role[]" value="{{ is_array($pr) ? ($pr['role'] ?? '') : '' }}"
                    placeholder="Rôle (ex: peuple d'origine)"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <button type="button" onclick="this.closest('.people-row').remove()"
                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center shrink-0 transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @empty
            <p class="text-slate-600 text-xs" id="no-people-msg">Aucun peuple associé. Cliquez sur « Ajouter ».</p>
            @endforelse
        </div>
        <template id="people-row-tpl">
            <div class="flex gap-3 items-center people-row">
                <select name="people_id[]"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <option value="">— Peuple —</option>
                    @foreach($peoples as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="people_role[]" placeholder="Rôle (optionnel)"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <button type="button" onclick="this.closest('.people-row').remove()"
                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center shrink-0 transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </template>
    </div>

    {{-- ── 4. Villes touristiques liées ────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-map-location-dot text-amber-400"></i> Villes touristiques liées
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
            @foreach($cities as $city)
            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-800 transition">
                <input type="checkbox" name="city_ids[]" value="{{ $city->id }}"
                    {{ in_array($city->id, old('city_ids', $element->city_ids ?? [])) ? 'checked' : '' }}
                    class="rounded border-slate-600 bg-slate-800 text-amber-500">
                <span class="text-slate-300 text-xs">{{ $city->name }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ── 5. Infos pratiques ──────────────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-circle-info text-amber-400"></i> Informations pratiques
            </h2>
            <button type="button" onclick="addInfoRow()"
                class="text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                <i class="fas fa-plus text-[10px]"></i> Ajouter
            </button>
        </div>
        <div id="info-rows" class="space-y-3">
            @foreach(old('info_label', collect($element->practical_info ?? [])->pluck('label')->toArray()) as $i => $lbl)
            <div class="flex gap-2 items-center info-row">
                <input type="text" name="info_icon[]" value="{{ old('info_icon.'.$i, ($element->practical_info[$i]['icon'] ?? '')) }}"
                    placeholder="fas fa-…" class="w-28 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <input type="text" name="info_label[]" value="{{ $lbl }}" placeholder="Label"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <input type="text" name="info_value[]" value="{{ old('info_value.'.$i, ($element->practical_info[$i]['value'] ?? '')) }}" placeholder="Valeur"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <button type="button" onclick="this.closest('.info-row').remove()"
                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center shrink-0 transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @endforeach
        </div>
        <template id="info-row-tpl">
            <div class="flex gap-2 items-center info-row">
                <input type="text" name="info_icon[]" placeholder="fas fa-…"
                    class="w-28 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <input type="text" name="info_label[]" placeholder="Label"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <input type="text" name="info_value[]" placeholder="Valeur"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                <button type="button" onclick="this.closest('.info-row').remove()"
                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center shrink-0 transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </template>
    </div>

    {{-- ── 6. Médias existants ─────────────────────────────────────────────── --}}
    @isset($element)
    @if($element->media->isNotEmpty())
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-photo-film text-amber-400"></i> Médias
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($element->media as $media)
            <div class="relative group">
                @if($media->type === 'photo')
                <img src="{{ $media->url }}" class="w-full aspect-square object-cover rounded-lg">
                @elseif($media->type === 'video')
                <div class="w-full aspect-square bg-slate-800 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play text-slate-500 text-xl"></i>
                </div>
                @else
                <div class="w-full aspect-square bg-slate-800 rounded-lg flex items-center justify-center">
                    <i class="fas fa-music text-slate-500 text-xl"></i>
                </div>
                @endif
                <form method="POST" action="{{ route('admin.cultural.media.destroy', $media) }}"
                    onsubmit="return confirm('Supprimer ce média ?')"
                    class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-6 h-6 bg-red-900/80 hover:bg-red-700 text-red-200 rounded-md flex items-center justify-center">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endisset

    {{-- ── 7. Uploader des médias ──────────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-cloud-arrow-up text-amber-400"></i> Ajouter des photos
        </h2>
        <input type="file" name="media_files[]" accept="image/*" multiple
            class="w-full text-xs text-slate-400 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2
                   file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-amber-500 file:text-black file:font-semibold">
    </div>

    {{-- ── 8. Paramètres de publication ────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-sliders text-amber-400"></i> Publication
        </h2>
        <div class="flex flex-wrap items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $element->is_active ?? true) ? 'checked' : '' }}
                    class="rounded border-slate-600 bg-slate-800 text-amber-500">
                <span class="text-sm text-slate-300">Actif (visible publiquement)</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1"
                    {{ old('is_featured', $element->is_featured ?? false) ? 'checked' : '' }}
                    class="rounded border-slate-600 bg-slate-800 text-amber-500">
                <span class="text-sm text-slate-300">Mettre en vedette</span>
            </label>
            <div>
                <label class="text-xs text-slate-400 mb-1 block">Ordre d'affichage</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $element->sort_order ?? 0) }}" min="0"
                    class="w-24 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.cultural.elements.index') }}"
            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-xl transition">Annuler</a>
        <button type="submit"
            class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm rounded-xl transition">
            {{ isset($element) ? 'Enregistrer les modifications' : 'Créer l\'élément' }}
        </button>
    </div>
</form>

<script>
function addPeopleRow() {
    const msg = document.getElementById('no-people-msg');
    if (msg) msg.remove();
    const tpl = document.getElementById('people-row-tpl').content.cloneNode(true);
    document.getElementById('people-rows').appendChild(tpl);
}
function addInfoRow() {
    const tpl = document.getElementById('info-row-tpl').content.cloneNode(true);
    document.getElementById('info-rows').appendChild(tpl);
}
</script>
@endsection
