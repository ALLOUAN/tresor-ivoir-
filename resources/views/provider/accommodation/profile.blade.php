@extends('layouts.app')

@section('title', 'Fiche établissement')
@section('page-title', 'Fiche établissement')

@section('header-actions')
    <a href="{{ route('provider.accommodation.dashboard') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour
    </a>
@endsection

@section('content')
@php $accom = $accommodation; @endphp

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<p class="text-slate-400 text-sm mb-5">
    Informations générales, localisation, contact, images principales, commodités, liens de réservation et publication.
    Les chambres et la galerie photos se gèrent depuis leurs pages dédiées.
</p>

<form method="POST" action="{{ route('provider.accommodation.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ═══════════════ COLONNE PRINCIPALE (2/3) ═══════════════ --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- ① Infos générales ──────────────────────────────────── --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-hotel text-orange-400"></i> Informations générales
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Nom <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $accom->name ?? $provider->name) }}"
                               required maxlength="150"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Type <span class="text-red-400">*</span></label>
                        <select name="type" required
                                class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            @foreach(['hotel'=>'Hôtel','residence'=>'Résidence','resort'=>'Resort','guesthouse'=>"Maison d'hôtes",'hostel'=>'Auberge de jeunesse','auberge'=>'Auberge','villa'=>'Villa','eco_lodge'=>'Éco-lodge'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('type', $accom->type ?? 'hotel') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Étoiles</label>
                        <select name="stars"
                                class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            @for($s = 0; $s <= 5; $s++)
                                <option value="{{ $s }}" {{ old('stars', $accom->stars ?? 0) == $s ? 'selected' : '' }}>
                                    {{ $s === 0 ? 'Sans étoile' : str_repeat('★', $s).' ('.$s.' étoile'.($s>1?'s':'').')' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ville <span class="text-red-400">*</span></label>
                        <select name="city_id" required
                                class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            <option value="">— Sélectionner une ville —</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $accom->city_id ?? '') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Quartier</label>
                        <input type="text" name="quartier" value="{{ old('quartier', $accom->quartier ?? '') }}" maxlength="100"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse', $accom->adresse ?? '') }}" maxlength="255"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Description courte <span class="text-slate-600">(max 300 car.)</span></label>
                        <textarea name="short_description" rows="2" maxlength="300"
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none">{{ old('short_description', $accom->short_description ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Horaires</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[11px] text-slate-500 mb-1 block">Check-in</span>
                                <input type="text" name="check_in_time" value="{{ old('check_in_time', substr($accom->check_in_time ?? '', 0, 5)) }}"
                                       placeholder="14:00" maxlength="5"
                                       class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 mb-1 block">Check-out</span>
                                <input type="text" name="check_out_time" value="{{ old('check_out_time', substr($accom->check_out_time ?? '', 0, 5)) }}"
                                       placeholder="12:00" maxlength="5"
                                       class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Description complète</label>
                        <textarea name="description" rows="5"
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ old('description', $accom->description ?? '') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Conditions de réservation / d'annulation</label>
                        <textarea name="cancellation_policy" rows="4" placeholder="Ex : Annulation gratuite jusqu'à 48h avant l'arrivée. Au-delà, la première nuit est facturée..."
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ old('cancellation_policy', $accom->cancellation_policy ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ② Contact ─────────────────────────────────────────── --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-orange-400"></i> Localisation & Contact
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude" value="{{ old('latitude', $accom->latitude ?? '') }}" placeholder="5.3600"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" value="{{ old('longitude', $accom->longitude ?? '') }}" placeholder="-4.0083"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $accom->phone ?? '') }}" maxlength="30"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $accom->email ?? '') }}" maxlength="150"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Site web</label>
                        <input type="url" name="website" value="{{ old('website', $accom->website ?? '') }}" maxlength="300" placeholder="https://…"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                </div>
            </div>

            {{-- ③ Images principales ──────────────────────────────── --}}
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
                            @if(!empty($accom?->cover_image))
                                <img id="cover_preview" src="{{ $accom->cover_image }}" class="w-full h-28 object-cover rounded-lg border border-slate-700">
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
                            @if(!empty($accom?->thumbnail))
                                <img id="thumb_preview" src="{{ $accom->thumbnail }}" class="w-full h-28 object-cover rounded-lg border border-slate-700">
                            @else
                                <div id="thumb_preview" class="w-full h-28 rounded-lg border border-dashed border-slate-700 bg-slate-800/50 flex items-center justify-center">
                                    <i class="fas fa-image text-slate-600 text-2xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ④ Commodités ──────────────────────────────────────── --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        <i class="fas fa-concierge-bell text-orange-400"></i> Commodités
                    </h2>
                    <button type="button" onclick="addAmenityRow()" class="text-xs text-orange-400 hover:text-orange-300 transition flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Ajouter
                    </button>
                </div>
                <div id="amenities-list" class="space-y-2">
                    @php $amenities = old('amenity_labels') ? null : ($accom->amenities ?? []); @endphp
                    @if($amenities)
                        @foreach($amenities as $am)
                        <div class="amenity-row flex gap-2">
                            <input type="text" name="amenity_icons[]" value="{{ $am['icon'] ?? 'fas fa-check' }}" placeholder="fas fa-wifi"
                                   class="w-36 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none transition font-mono">
                            <input type="text" name="amenity_labels[]" value="{{ $am['label'] ?? '' }}" placeholder="Wi-Fi gratuit"
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

            {{-- ⑤ Liens de réservation ─────────────────────────────── --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        <i class="fas fa-bookmark text-orange-400"></i> Liens de réservation externes
                    </h2>
                    <button type="button" onclick="addBookingRow()" class="text-xs text-orange-400 hover:text-orange-300 transition flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Ajouter
                    </button>
                </div>
                <div id="booking-list" class="space-y-3">
                    @php $bookingLinks = old('bl_provider') ? null : ($accom->booking_links ?? []); @endphp
                    @if($bookingLinks)
                        @foreach($bookingLinks as $bl)
                            @include('admin.accommodation._booking_row', ['bl'=>$bl, 'i'=>$loop->index])
                        @endforeach
                    @elseif(old('bl_provider'))
                        @foreach(old('bl_provider') as $i => $prov)
                            @php $bl = ['provider_name'=>$prov,'affiliate_url'=>old('bl_url.'.$i),'logo_url'=>old('bl_logo.'.$i),'badge_text'=>old('bl_badge.'.$i),'is_official'=>in_array((string)$i,(array)old('bl_official',[]))]; @endphp
                            @include('admin.accommodation._booking_row', ['bl'=>$bl, 'i'=>$i])
                        @endforeach
                    @endif
                </div>
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
                               {{ old('is_active', $accom->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-300 group-hover:text-white transition">
                            Actif <span class="text-slate-500 text-xs">(visible sur le site)</span>
                        </span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Enregistrer
                </button>

                @if($accom?->is_featured)
                    <p class="mt-3 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-star text-orange-400/70"></i> Mis en vedette par l'administration
                    </p>
                @endif
            </div>

            <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-tags text-orange-400"></i> Catégories touristiques
                </h2>
                @php $selectedCats = old('category_ids', $accom->category_ids ?? []); @endphp
                <div class="space-y-1 max-h-64 overflow-y-auto pr-1">
                    @foreach($categories as $cat)
                    <label class="flex items-center gap-2.5 cursor-pointer p-2 rounded-lg hover:bg-slate-800 transition">
                        <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                               class="rounded border-slate-600 bg-slate-800 text-orange-500 shrink-0"
                               {{ in_array($cat->id, (array)$selectedCats) ? 'checked' : '' }}>
                        <span class="flex items-center gap-2 text-sm text-slate-300">
                            @if($cat->icon)<i class="{{ $cat->icon }} text-xs" @if($cat->color) style="color:{{ $cat->color }}" @endif></i>@endif
                            {{ $cat->name }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>{{-- fin col latérale --}}

    </div>{{-- fin grid --}}
</form>

{{-- ── Templates JS ─────────────────────── --}}
<template id="tpl-amenity">
    <div class="amenity-row flex gap-2">
        <input type="text" name="amenity_icons[]" value="fas fa-check" placeholder="fas fa-wifi"
               class="w-36 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none font-mono">
        <input type="text" name="amenity_labels[]" placeholder="Wi-Fi gratuit"
               class="flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
        <button type="button" onclick="this.closest('.amenity-row').remove()"
                class="w-8 h-[38px] rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
</template>

<template id="tpl-booking">
    <div class="booking-row bg-slate-800/60 border border-slate-700 rounded-xl p-4 space-y-3">
        <div class="flex gap-2 items-center">
            <input type="text" name="bl_provider[]" placeholder="Booking.com"
                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none font-medium">
            <button type="button" onclick="this.closest('.booking-row').remove()"
                    class="w-8 h-9 rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">URL de réservation</span>
                <input type="url" name="bl_url[]" placeholder="https://booking.com/…"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">URL du logo</span>
                <input type="url" name="bl_logo[]" placeholder="https://…/logo.png"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">Badge texte</span>
                <input type="text" name="bl_badge[]" placeholder="Meilleur prix"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div class="flex items-center gap-2.5 pt-4">
                <input type="checkbox" name="bl_official[]" class="rounded border-slate-600 bg-slate-800 text-orange-500">
                <span class="text-xs text-slate-400">Site officiel de l'établissement</span>
            </div>
        </div>
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

function addBookingRow() {
    const list = document.getElementById('booking-list');
    const idx = list.querySelectorAll('.booking-row').length;
    const tpl = document.getElementById('tpl-booking').content.cloneNode(true);
    const cb = tpl.querySelector('input[name="bl_official[]"]');
    if (cb) cb.value = String(idx);
    list.appendChild(tpl);
}
</script>
@endpush
@endsection
