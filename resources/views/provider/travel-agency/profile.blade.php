@extends('layouts.app')

@section('title', 'Fiche établissement')
@section('page-title', 'Fiche établissement')

@section('header-actions')
    <a href="{{ route('provider.travel-agency.dashboard') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour
    </a>
@endsection

@section('content')
@php $a = $agency; @endphp

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<p class="text-slate-400 text-sm mb-5">
    Informations générales, localisation, contact, images principales et équipements.
    Vos circuits et votre galerie photos se gèrent depuis leurs pages dédiées.
</p>

<form method="POST" action="{{ route('provider.travel-agency.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ═══════════════ COLONNE PRINCIPALE (2/3) ═══════════════ --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- ① Infos générales --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-plane text-orange-400"></i> Informations générales
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Nom <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $a->name ?? $provider->name) }}"
                               required maxlength="150"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ville <span class="text-red-400">*</span></label>
                        <select name="city_id" required
                                class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            <option value="">— Sélectionner une ville —</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $a->city_id ?? '') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Quartier</label>
                        <input type="text" name="quartier" value="{{ old('quartier', $a->quartier ?? '') }}" maxlength="100"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse', $a->adresse ?? '') }}" maxlength="255"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Description courte <span class="text-slate-600">(max 300 car.)</span></label>
                        <textarea name="short_description" rows="2" maxlength="300"
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none">{{ old('short_description', $a->short_description ?? '') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Description complète</label>
                        <textarea name="description" rows="5"
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ old('description', $a->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ② Localisation & Contact --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-orange-400"></i> Localisation & Contact
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude" value="{{ old('latitude', $a->latitude ?? '') }}" placeholder="5.3600"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" value="{{ old('longitude', $a->longitude ?? '') }}" placeholder="-4.0083"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $a->phone ?? '') }}" maxlength="30"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $a->email ?? '') }}" maxlength="150"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Site web</label>
                        <input type="url" name="website" value="{{ old('website', $a->website ?? '') }}" maxlength="300" placeholder="https://…"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                </div>
            </div>

            {{-- ③ Images principales --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-image text-orange-400"></i> Images principales
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs text-slate-400 mb-2 block"><i class="fas fa-panorama text-orange-400/60 mr-1"></i>Image de couverture</label>
                        <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-4 cursor-pointer transition group">
                            <i class="fas fa-cloud-arrow-up text-xl text-slate-600 group-hover:text-orange-400/70 mb-1.5 transition"></i>
                            <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez</span>
                            <input type="file" name="cover_image_file" accept="image/*" class="hidden" onchange="previewImgFile(this,'cover_preview')">
                        </label>
                        <div class="mt-2">
                            @if(!empty($a?->cover_image))
                                <img id="cover_preview" src="{{ $a->cover_image }}" class="w-full h-28 object-cover rounded-lg border border-slate-700">
                            @else
                                <div id="cover_preview" class="w-full h-28 rounded-lg border border-dashed border-slate-700 bg-slate-800/50 flex items-center justify-center">
                                    <i class="fas fa-image text-slate-600 text-2xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 mb-2 block"><i class="fas fa-image text-orange-400/60 mr-1"></i>Vignette (thumbnail)</label>
                        <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-4 cursor-pointer transition group">
                            <i class="fas fa-cloud-arrow-up text-xl text-slate-600 group-hover:text-orange-400/70 mb-1.5 transition"></i>
                            <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez</span>
                            <input type="file" name="thumbnail_file" accept="image/*" class="hidden" onchange="previewImgFile(this,'thumb_preview')">
                        </label>
                        <div class="mt-2">
                            @if(!empty($a?->thumbnail))
                                <img id="thumb_preview" src="{{ $a->thumbnail }}" class="w-full h-28 object-cover rounded-lg border border-slate-700">
                            @else
                                <div id="thumb_preview" class="w-full h-28 rounded-lg border border-dashed border-slate-700 bg-slate-800/50 flex items-center justify-center">
                                    <i class="fas fa-image text-slate-600 text-2xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ④ Équipements --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        <i class="fas fa-list-check text-orange-400"></i> Équipements & services
                    </h2>
                    <button type="button" onclick="addAmenityRow()" class="text-xs text-orange-400 hover:text-orange-300 transition flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Ajouter
                    </button>
                </div>
                <div id="amenities-list" class="space-y-2">
                    @php $amenities = old('amenity_labels') ? null : ($a->amenities ?? []); @endphp
                    @if($amenities)
                        @foreach($amenities as $am)
                        <div class="amenity-row flex gap-2">
                            <input type="text" name="amenity_icons[]" value="{{ $am['icon'] ?? 'fas fa-check' }}" placeholder="fas fa-wifi"
                                   class="w-36 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none transition font-mono">
                            <input type="text" name="amenity_labels[]" value="{{ $am['label'] ?? '' }}" placeholder="Guide francophone"
                                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            <button type="button" onclick="this.closest('.amenity-row').remove()"
                                    class="w-8 h-[38px] rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        @endforeach
                    @elseif(old('amenity_labels'))
                        @foreach(old('amenity_labels') as $idx => $lbl)
                        <div class="amenity-row flex gap-2">
                            <input type="text" name="amenity_icons[]" value="{{ old('amenity_icons.'.$idx, 'fas fa-check') }}"
                                   class="w-36 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none transition font-mono">
                            <input type="text" name="amenity_labels[]" value="{{ $lbl }}"
                                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            <button type="button" onclick="this.closest('.amenity-row').remove()"
                                    class="w-8 h-[38px] rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        @endforeach
                    @endif
                </div>
                <p class="text-[11px] text-slate-600 mt-3">
                    <i class="fas fa-circle-info mr-1"></i>Icône : classe FontAwesome (ex: <code class="text-orange-400/70">fas fa-wifi</code>)
                </p>
            </div>

        </div>{{-- fin col principale --}}

        {{-- ═══════════════ COLONNE LATÉRALE (1/3) ═══════════════ --}}
        <div class="space-y-5">
            <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sticky top-4">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders text-orange-400"></i> Publication
                </h2>
                <div class="space-y-3 mb-5">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               class="rounded border-slate-600 bg-slate-800 text-orange-500"
                               {{ old('is_active', $a->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-300 group-hover:text-white transition">
                            Actif <span class="text-slate-500 text-xs">(visible sur le site)</span>
                        </span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Enregistrer
                </button>

                @if($a?->is_featured)
                    <p class="mt-3 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-star text-orange-400/70"></i> Mis en vedette par l'administration
                    </p>
                @endif
            </div>
        </div>{{-- fin col latérale --}}

    </div>{{-- fin grid --}}
</form>

<template id="tpl-amenity">
    <div class="amenity-row flex gap-2">
        <input type="text" name="amenity_icons[]" value="fas fa-check" placeholder="fas fa-wifi"
               class="w-36 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none font-mono">
        <input type="text" name="amenity_labels[]" placeholder="Guide francophone"
               class="flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
        <button type="button" onclick="this.closest('.amenity-row').remove()"
                class="w-8 h-[38px] rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
</template>

@push('scripts')
<script>
function previewImgFile(input, previewId) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const el = document.getElementById(previewId);
        if (el && el.tagName === 'IMG') {
            el.src = e.target.result;
        } else if (el) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.id = previewId;
            img.className = 'w-full h-28 object-cover rounded-lg border border-slate-700';
            el.replaceWith(img);
        }
    };
    reader.readAsDataURL(input.files[0]);
}

function addAmenityRow() {
    const tpl = document.getElementById('tpl-amenity').content.cloneNode(true);
    document.getElementById('amenities-list').appendChild(tpl);
}
</script>
@endpush
@endsection
