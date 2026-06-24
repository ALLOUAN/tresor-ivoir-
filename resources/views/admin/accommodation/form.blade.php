@extends('layouts.app')

@section('title', isset($accommodation) ? 'Modifier — '.$accommodation->name : 'Nouvel hébergement')
@section('page-title', isset($accommodation) ? $accommodation->name : 'Nouvel hébergement')

@section('header-actions')
<a href="{{ route('admin.accommodations.index') }}"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-arrow-left"></i> Retour à la liste
</a>
@if(isset($accommodation))
<a href="#" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-eye"></i> Aperçu
</a>
@endif
@endsection

@section('content')
@php $isEdit = isset($accommodation); @endphp

{{-- Flash / Erreurs ────────────────────────────────────────────────────── --}}
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

<form method="POST"
      action="{{ $isEdit ? route('admin.accommodations.update', $accommodation) : route('admin.accommodations.store') }}"
      enctype="multipart/form-data"
      id="accom-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ═══════════════ COLONNE PRINCIPALE (2/3) ═══════════════ --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- ① Infos générales ──────────────────────────────────── --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-hotel text-amber-400"></i> Informations générales
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">
                            Nom <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name"
                               value="{{ old('name', $accommodation->name ?? '') }}"
                               required maxlength="150"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Type <span class="text-red-400">*</span></label>
                        <select name="type" required
                                class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            @foreach(['hotel'=>'Hôtel','resort'=>'Resort','guesthouse'=>"Maison d'hôtes",'hostel'=>'Auberge de jeunesse','auberge'=>'Auberge','villa'=>'Villa','eco_lodge'=>'Éco-lodge'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('type', $accommodation->type ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Étoiles</label>
                        <select name="stars"
                                class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            @for($s = 0; $s <= 5; $s++)
                                <option value="{{ $s }}" {{ old('stars', $accommodation->stars ?? 0) == $s ? 'selected' : '' }}>
                                    {{ $s === 0 ? 'Sans étoile' : str_repeat('★', $s).' ('.$s.' étoile'.($s>1?'s':'').')' }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ville <span class="text-red-400">*</span></label>
                        <select name="city_id" required
                                class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            <option value="">— Sélectionner une ville —</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $accommodation->city_id ?? '') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Quartier</label>
                        <input type="text" name="quartier"
                               value="{{ old('quartier', $accommodation->quartier ?? '') }}"
                               maxlength="100"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Adresse</label>
                        <input type="text" name="adresse"
                               value="{{ old('adresse', $accommodation->adresse ?? '') }}"
                               maxlength="255"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">
                            Description courte <span class="text-slate-600">(max 300 car.)</span>
                        </label>
                        <textarea name="short_description" rows="2" maxlength="300"
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none">{{ old('short_description', $accommodation->short_description ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Horaires</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[11px] text-slate-500 mb-1 block">Check-in</span>
                                <input type="text" name="check_in_time"
                                       value="{{ old('check_in_time', substr($accommodation->check_in_time ?? '', 0, 5)) }}"
                                       placeholder="14:00" maxlength="5"
                                       class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 mb-1 block">Check-out</span>
                                <input type="text" name="check_out_time"
                                       value="{{ old('check_out_time', substr($accommodation->check_out_time ?? '', 0, 5)) }}"
                                       placeholder="12:00" maxlength="5"
                                       class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Description complète</label>
                        <textarea name="description" rows="5"
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y">{{ old('description', $accommodation->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ② Localisation & Contact ────────────────────────────── --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-amber-400"></i> Localisation & Contact
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude"
                               value="{{ old('latitude', $accommodation->latitude ?? '') }}"
                               placeholder="5.3600"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude"
                               value="{{ old('longitude', $accommodation->longitude ?? '') }}"
                               placeholder="-4.0083"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Téléphone</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $accommodation->phone ?? '') }}"
                               maxlength="30"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $accommodation->email ?? '') }}"
                               maxlength="150"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">
                            <i class="fas fa-globe text-amber-400/70 mr-1"></i>Site web
                        </label>
                        <div class="flex gap-2">
                            <input type="url" name="website"
                                   value="{{ old('website', $accommodation->website ?? '') }}"
                                   maxlength="300" placeholder="https://…"
                                   id="website_input"
                                   oninput="toggleWebsiteBtn(this.value)"
                                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            <a id="website_btn" href="{{ $accommodation->website ?? '#' }}" target="_blank" rel="noopener"
                               class="shrink-0 w-9 h-9 rounded-lg bg-slate-800 hover:bg-amber-500/20 border border-slate-700 hover:border-amber-500/40 flex items-center justify-center text-slate-400 hover:text-amber-400 transition {{ ($isEdit && $accommodation->website) ? '' : 'hidden' }}">
                                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ③ Images ───────────────────────────────────────────── --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-image text-amber-400"></i> Images
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Cover --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs text-slate-400">
                                <i class="fas fa-panorama text-amber-400/60 mr-1"></i>Image de couverture
                            </label>
                            <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5">
                                <button type="button" id="cover_btn_url" onclick="setImgMode('cover','url')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition bg-slate-700 text-white">
                                    <i class="fas fa-link mr-1"></i>URL
                                </button>
                                <button type="button" id="cover_btn_file" onclick="setImgMode('cover','file')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition text-slate-500 hover:text-white">
                                    <i class="fas fa-upload mr-1"></i>Fichier
                                </button>
                            </div>
                        </div>
                        <div id="cover_url_wrap">
                            <input type="text" name="cover_image"
                                   value="{{ old('cover_image', $accommodation->cover_image ?? '') }}"
                                   placeholder="https://… ou /storage/…" maxlength="500"
                                   oninput="previewImgUrl(this,'cover_preview')"
                                   class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                        </div>
                        <div id="cover_file_wrap" class="hidden">
                            <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-xl p-4 cursor-pointer transition group">
                                <i class="fas fa-cloud-arrow-up text-xl text-slate-600 group-hover:text-amber-400/70 mb-1.5 transition"></i>
                                <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez</span>
                                <input type="file" name="cover_image_file" accept="image/*" class="hidden"
                                       onchange="previewImgFile(this,'cover_preview')">
                            </label>
                        </div>
                        {{-- Aperçu --}}
                        <div class="mt-2 relative">
                            @if($isEdit && $accommodation->cover_image)
                                <img id="cover_preview" src="{{ $accommodation->cover_image }}"
                                     class="w-full h-28 object-cover rounded-lg border border-slate-700">
                            @else
                                <div id="cover_preview"
                                     class="w-full h-28 rounded-lg border border-dashed border-slate-700 bg-slate-800/50 flex items-center justify-center">
                                    <i class="fas fa-image text-slate-600 text-2xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Thumbnail --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs text-slate-400">
                                <i class="fas fa-image text-amber-400/60 mr-1"></i>Vignette (thumbnail)
                            </label>
                            <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5">
                                <button type="button" id="thumb_btn_url" onclick="setImgMode('thumb','url')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition bg-slate-700 text-white">
                                    <i class="fas fa-link mr-1"></i>URL
                                </button>
                                <button type="button" id="thumb_btn_file" onclick="setImgMode('thumb','file')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition text-slate-500 hover:text-white">
                                    <i class="fas fa-upload mr-1"></i>Fichier
                                </button>
                            </div>
                        </div>
                        <div id="thumb_url_wrap">
                            <input type="text" name="thumbnail"
                                   value="{{ old('thumbnail', $accommodation->thumbnail ?? '') }}"
                                   placeholder="https://… ou /storage/…" maxlength="500"
                                   oninput="previewImgUrl(this,'thumb_preview')"
                                   class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                        </div>
                        <div id="thumb_file_wrap" class="hidden">
                            <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-xl p-4 cursor-pointer transition group">
                                <i class="fas fa-cloud-arrow-up text-xl text-slate-600 group-hover:text-amber-400/70 mb-1.5 transition"></i>
                                <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez</span>
                                <input type="file" name="thumbnail_file" accept="image/*" class="hidden"
                                       onchange="previewImgFile(this,'thumb_preview')">
                            </label>
                        </div>
                        <div class="mt-2">
                            @if($isEdit && $accommodation->thumbnail)
                                <img id="thumb_preview" src="{{ $accommodation->thumbnail }}"
                                     class="w-full h-28 object-cover rounded-lg border border-slate-700">
                            @else
                                <div id="thumb_preview"
                                     class="w-full h-28 rounded-lg border border-dashed border-slate-700 bg-slate-800/50 flex items-center justify-center">
                                    <i class="fas fa-image text-slate-600 text-2xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- ④ Commodités ──────────────────────────────────────── --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        <i class="fas fa-concierge-bell text-amber-400"></i> Commodités
                    </h2>
                    <button type="button" onclick="addAmenityRow()"
                            class="text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Ajouter
                    </button>
                </div>
                <div id="amenities-list" class="space-y-2">
                    @php $amenities = old('amenity_labels') ? null : ($accommodation->amenities ?? []); @endphp
                    @if($amenities)
                        @foreach($amenities as $am)
                        <div class="amenity-row flex gap-2">
                            <input type="text" name="amenity_icons[]"
                                   value="{{ $am['icon'] ?? 'fas fa-check' }}"
                                   placeholder="fas fa-wifi"
                                   class="w-36 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none transition font-mono">
                            <input type="text" name="amenity_labels[]"
                                   value="{{ $am['label'] ?? '' }}"
                                   placeholder="Wi-Fi gratuit"
                                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            <button type="button" onclick="this.closest('.amenity-row').remove()"
                                    class="w-8 h-[38px] rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        @endforeach
                    @elseif(old('amenity_labels'))
                        @foreach(old('amenity_labels') as $idx => $lbl)
                        <div class="amenity-row flex gap-2">
                            <input type="text" name="amenity_icons[]"
                                   value="{{ old('amenity_icons.'.$idx, 'fas fa-check') }}"
                                   class="w-36 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none transition font-mono">
                            <input type="text" name="amenity_labels[]"
                                   value="{{ $lbl }}"
                                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                            <button type="button" onclick="this.closest('.amenity-row').remove()"
                                    class="w-8 h-[38px] rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        @endforeach
                    @endif
                </div>
                <p class="text-[11px] text-slate-600 mt-3">
                    <i class="fas fa-circle-info mr-1"></i>Icône : classe FontAwesome (ex: <code class="text-amber-400/70">fas fa-wifi</code>)
                </p>
            </div>

            {{-- ⑤ Types de chambres ────────────────────────────────── --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        <i class="fas fa-bed text-amber-400"></i> Types de chambres
                    </h2>
                    <button type="button" onclick="addRoomRow()"
                            class="text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Ajouter
                    </button>
                </div>
                <div id="rooms-list" class="space-y-3">
                    @php $roomTypes = old('room_name') ? null : ($accommodation->room_types ?? []); @endphp
                    @if($roomTypes)
                        @foreach($roomTypes as $r)
                            @include('admin.accommodation._room_row', ['r'=>$r, 'i'=>$loop->index])
                        @endforeach
                    @elseif(old('room_name'))
                        @foreach(old('room_name') as $i => $rn)
                            @php $r = ['name'=>$rn,'max_adults'=>old('room_max_adults.'.$i),'max_children'=>old('room_max_children.'.$i),'area_m2'=>old('room_area_m2.'.$i),'price_xof'=>old('room_price_xof.'.$i),'price_eur'=>old('room_price_eur.'.$i),'amenities'=>old('room_amenities.'.$i,'')]; @endphp
                            @include('admin.accommodation._room_row', ['r'=>$r, 'i'=>$i])
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- ⑥ Liens de réservation ─────────────────────────────── --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        <i class="fas fa-bookmark text-amber-400"></i> Liens de réservation
                    </h2>
                    <button type="button" onclick="addBookingRow()"
                            class="text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                        <i class="fas fa-plus text-[10px]"></i> Ajouter
                    </button>
                </div>
                <div id="booking-list" class="space-y-3">
                    @php $bookingLinks = old('bl_provider') ? null : ($accommodation->booking_links ?? []); @endphp
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

            {{-- ⑦ Galerie photos ───────────────────────────────────── --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-photo-film text-amber-400"></i> Galerie photos
                </h2>

                @if($isEdit && $accommodation->media->isNotEmpty())
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-5">
                        @foreach($accommodation->media as $m)
                        <div class="relative group">
                            <img src="{{ $m->url }}" alt="{{ $m->alt_text ?? '' }}"
                                 loading="lazy"
                                 class="w-full aspect-square object-cover rounded-lg border border-slate-700">
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                                <button type="submit"
                                        form="media-del-{{ $m->id }}"
                                        onclick="return confirm('Supprimer ce média ?')"
                                        class="w-6 h-6 bg-red-900/80 hover:bg-red-700 text-red-200 rounded-md flex items-center justify-center">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

                <label class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-xl p-6 cursor-pointer transition group">
                    <i class="fas fa-cloud-arrow-up text-2xl text-slate-600 group-hover:text-amber-400/70 mb-2 transition"></i>
                    <span class="text-slate-500 text-sm group-hover:text-slate-300 transition">Ajouter des photos</span>
                    <span class="text-slate-700 text-xs mt-1">JPG, PNG, WebP — plusieurs fichiers acceptés</span>
                    <input type="file" name="media_files[]" multiple accept="image/*" class="hidden"
                           onchange="showMediaPreviews(this)">
                </label>
                <div id="media-previews" class="flex flex-wrap gap-2 mt-3"></div>
            </div>

        </div>{{-- fin col principale --}}

        {{-- ═══════════════ COLONNE LATÉRALE (1/3) ═══════════════ --}}
        <div class="space-y-5">

            {{-- Publication --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 sticky top-4">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders text-amber-400"></i> Publication
                </h2>

                <div class="space-y-3 mb-4">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               class="rounded border-slate-600 bg-slate-800 text-amber-500"
                               {{ old('is_active', $accommodation->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-300 group-hover:text-white transition">
                            Actif <span class="text-slate-500 text-xs">(visible sur le site)</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1"
                               class="rounded border-slate-600 bg-slate-800 text-amber-500"
                               {{ old('is_featured', $accommodation->is_featured ?? false) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-300 group-hover:text-white transition">
                            En vedette
                        </span>
                    </label>
                </div>

                <div class="mb-5">
                    <label class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
                    <input type="number" name="sort_order" min="0"
                           value="{{ old('sort_order', $accommodation->sort_order ?? 0) }}"
                           class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-bold text-sm rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    {{ $isEdit ? 'Enregistrer' : 'Créer l\'hébergement' }}
                </button>
                <a href="{{ route('admin.accommodations.index') }}"
                   class="mt-2 w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-xl transition flex items-center justify-center gap-2">
                    Annuler
                </a>
            </div>

            {{-- Catégories --}}
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-tags text-amber-400"></i> Catégories touristiques
                </h2>
                @php $selectedCats = old('category_ids', $accommodation->category_ids ?? []); @endphp
                <div class="space-y-1 max-h-64 overflow-y-auto pr-1">
                    @foreach($categories as $cat)
                    <label class="flex items-center gap-2.5 cursor-pointer p-2 rounded-lg hover:bg-slate-800 transition">
                        <input type="checkbox" name="category_ids[]"
                               value="{{ $cat->id }}"
                               class="rounded border-slate-600 bg-slate-800 text-amber-500 shrink-0"
                               {{ in_array($cat->id, (array)$selectedCats) ? 'checked' : '' }}>
                        <span class="flex items-center gap-2 text-sm text-slate-300">
                            @if($cat->icon)
                                <i class="{{ $cat->icon }} text-xs"
                                   @if($cat->color) style="color:{{ $cat->color }}" @endif></i>
                            @endif
                            {{ $cat->name }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Infos techniques (edit only) --}}
            @if($isEdit)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-3 flex items-center gap-2 text-sm">
                    <i class="fas fa-code text-slate-500"></i> Infos techniques
                </h2>
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">ID</span>
                        <span class="text-slate-300 font-mono">{{ $accommodation->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Slug</span>
                        <span class="text-slate-300 font-mono truncate max-w-[140px]" title="{{ $accommodation->slug }}">{{ $accommodation->slug }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Vues</span>
                        <span class="text-slate-300">{{ number_format($accommodation->views_count) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Créé</span>
                        <span class="text-slate-300">{{ $accommodation->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Modifié</span>
                        <span class="text-slate-300">{{ $accommodation->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-800">
                    <button type="submit"
                            form="accom-del-form"
                            onclick="return confirm('Supprimer définitivement « {{ addslashes($accommodation->name) }} » ?')"
                            class="w-full px-4 py-2 bg-slate-800 hover:bg-red-900/50 text-slate-400 hover:text-red-300 text-xs rounded-lg transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-trash text-[10px]"></i> Supprimer cet hébergement
                    </button>
                </div>
            </div>
            @endif

        </div>{{-- fin col latérale --}}

    </div>{{-- fin grid --}}
</form>

@if($isEdit)
{{-- Formulaires de suppression séparés (hors formulaire principal pour éviter _method=DELETE parasite) --}}
@foreach($accommodation->media as $m)
<form id="media-del-{{ $m->id }}" method="POST"
      action="{{ route('admin.accommodations.media.destroy', $m) }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endforeach
<form id="accom-del-form" method="POST"
      action="{{ route('admin.accommodations.destroy', $accommodation) }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endif

{{-- ── Templates JS ─────────────────────────────────────────────────────── --}}
<template id="tpl-amenity">
    <div class="amenity-row flex gap-2">
        <input type="text" name="amenity_icons[]" value="fas fa-check"
               placeholder="fas fa-wifi"
               class="w-36 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none font-mono">
        <input type="text" name="amenity_labels[]"
               placeholder="Wi-Fi gratuit"
               class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
        <button type="button" onclick="this.closest('.amenity-row').remove()"
                class="w-8 h-[38px] rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
</template>

<template id="tpl-room">
    <div class="room-row bg-slate-800/60 border border-slate-700 rounded-xl p-4 space-y-3">
        <div class="flex gap-2 items-center">
            <input type="text" name="room_name[]"
                   placeholder="Chambre Standard"
                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none font-medium">
            <button type="button" onclick="this.closest('.room-row').remove()"
                    class="w-8 h-9 rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">Adultes max</span>
                <input type="number" name="room_max_adults[]" min="1" max="10" value="2"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">Enfants max</span>
                <input type="number" name="room_max_children[]" min="0" max="10" value="0"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">Surface m²</span>
                <input type="number" step="0.5" name="room_area_m2[]" placeholder="25"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div><!-- spacer --></div>
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">Prix XOF/nuit</span>
                <input type="number" name="room_price_xof[]" placeholder="50 000"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <span class="text-[11px] text-slate-500 mb-1 block">Prix EUR/nuit</span>
                <input type="number" step="0.01" name="room_price_eur[]" placeholder="75"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div class="col-span-2">
                <span class="text-[11px] text-slate-500 mb-1 block">Équipements <span class="text-slate-600">(séparés par virgule)</span></span>
                <input type="text" name="room_amenities[]" placeholder="Climatisation, TV, Coffre-fort"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
            </div>
            <div class="col-span-2">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] text-slate-500 flex items-center gap-1">
                        <i class="fas fa-images text-[9px] text-amber-400/60"></i>
                        Photos de la chambre
                    </span>
                    <button type="button" onclick="addRoomPhotoRow(this)"
                            class="inline-flex items-center gap-1 text-[10px] text-amber-400 hover:text-amber-300 transition">
                        <i class="fas fa-plus text-[8px]"></i>Ajouter URL
                    </button>
                </div>
                <input type="hidden" name="room_photos[]" class="room-photos-input" value="">
                <div class="room-photos-list space-y-1.5 mb-2"></div>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <span class="text-[11px] text-slate-500 group-hover:text-slate-300 transition flex items-center gap-1">
                        <i class="fas fa-cloud-arrow-up text-[9px] text-amber-400/60"></i> Uploader des photos
                    </span>
                    <input type="file" name="room_photo_files[__RIDX__][]"
                           multiple accept="image/*"
                           class="room-photo-file-input hidden"
                           onchange="previewRoomFiles(this)">
                    <span class="px-2 py-0.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-[10px] rounded transition">
                        Choisir fichiers
                    </span>
                </label>
                <div class="room-file-previews flex flex-wrap gap-1.5 mt-1.5"></div>
            </div>
        </div>
    </div>
</template>

<template id="tpl-booking">
    <div class="booking-row bg-slate-800/60 border border-slate-700 rounded-xl p-4 space-y-3">
        <div class="flex gap-2 items-center">
            <input type="text" name="bl_provider[]"
                   placeholder="Booking.com"
                   class="flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none font-medium">
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
                <input type="checkbox" name="bl_official[]"
                       class="rounded border-slate-600 bg-slate-800 text-amber-500">
                <span class="text-xs text-slate-400">Site officiel de l'établissement</span>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
// ── Toggles URL/Fichier images ─────────────────────────────────────────────
function setImgMode(field, mode) {
    const urlWrap  = document.getElementById(field + '_url_wrap');
    const fileWrap = document.getElementById(field + '_file_wrap');
    const btnUrl   = document.getElementById(field + '_btn_url');
    const btnFile  = document.getElementById(field + '_btn_file');
    const on = 'bg-slate-700 text-white', off = 'text-slate-500 hover:text-white';
    if (mode === 'url') {
        urlWrap.classList.remove('hidden');
        fileWrap.classList.add('hidden');
        btnUrl.className  = btnUrl.className.replace(off, on);
        btnFile.className = btnFile.className.replace(on, off);
    } else {
        urlWrap.classList.add('hidden');
        fileWrap.classList.remove('hidden');
        btnFile.className = btnFile.className.replace(off, on);
        btnUrl.className  = btnUrl.className.replace(on, off);
    }
}

// ── Aperçu image depuis URL ────────────────────────────────────────────────
function previewImgUrl(input, previewId) {
    const el = document.getElementById(previewId);
    if (!el || !input.value) return;
    if (el.tagName === 'IMG') {
        el.src = input.value;
    } else {
        const img = document.createElement('img');
        img.src = input.value;
        img.id  = previewId;
        img.className = 'w-full h-28 object-cover rounded-lg border border-slate-700';
        el.replaceWith(img);
    }
}

// ── Aperçu image depuis fichier ────────────────────────────────────────────
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
            img.id  = previewId;
            img.className = 'w-full h-28 object-cover rounded-lg border border-slate-700';
            el.replaceWith(img);
        }
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Aperçu galerie médias ──────────────────────────────────────────────────
function showMediaPreviews(input) {
    const container = document.getElementById('media-previews');
    container.innerHTML = '';

    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'];
    const files      = Array.from(input.files);
    const rejected   = files.filter(f => !validTypes.includes(f.type));

    if (rejected.length) {
        // Reconstruire la FileList sans les fichiers non-image
        const dt = new DataTransfer();
        files.filter(f => validTypes.includes(f.type)).forEach(f => dt.items.add(f));
        input.files = dt.files;

        const warn = document.createElement('p');
        warn.className = 'w-full text-xs text-red-400 mb-1';
        warn.textContent = rejected.map(f => `« ${f.name} » ignoré (non-image)`).join(' · ');
        container.appendChild(warn);
    }

    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img     = document.createElement('img');
            img.src       = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded-lg border border-slate-700';
            img.title     = file.name;
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

// ── Bouton lien externe --
function toggleWebsiteBtn(url) {
    const btn = document.getElementById('website_btn');
    if (!btn) return;
    if (url && url.startsWith('http')) {
        btn.href = url;
        btn.style.display = 'flex';
    } else {
        btn.style.display = 'none';
    }
}

// ── Lignes dynamiques ──────────────────────────────────────────────────────
function addAmenityRow() {
    const tpl = document.getElementById('tpl-amenity').content.cloneNode(true);
    document.getElementById('amenities-list').appendChild(tpl);
}
function addRoomRow() {
    const list = document.getElementById('rooms-list');
    const idx  = list.querySelectorAll('.room-row').length;
    const tpl  = document.getElementById('tpl-room').content.cloneNode(true);
    const fi   = tpl.querySelector('.room-photo-file-input');
    if (fi) fi.name = 'room_photo_files[' + idx + '][]';
    list.appendChild(tpl);
}

// ── Aperçu des fichiers uploadés par chambre ───────────────────────────────
function previewRoomFiles(input) {
    const container  = input.closest('.col-span-2').querySelector('.room-file-previews');
    container.innerHTML = '';

    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'];
    const files      = Array.from(input.files);
    const rejected   = files.filter(function (f) { return !validTypes.includes(f.type); });

    if (rejected.length) {
        const dt = new DataTransfer();
        files.filter(function (f) { return validTypes.includes(f.type); }).forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;

        const warn = document.createElement('p');
        warn.className   = 'w-full text-xs text-red-400 mb-1';
        warn.textContent = rejected.map(function (f) { return '« ' + f.name + ' » ignoré (non-image)'; }).join(' · ');
        container.appendChild(warn);
    }

    Array.from(input.files).forEach(function (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img     = document.createElement('img');
            img.src       = e.target.result;
            img.className = 'w-16 h-16 object-cover rounded-lg border border-slate-700';
            img.title     = file.name;
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
function addBookingRow() {
    const list = document.getElementById('booking-list');
    const idx  = list.querySelectorAll('.booking-row').length;
    const tpl  = document.getElementById('tpl-booking').content.cloneNode(true);
    const cb   = tpl.querySelector('input[name="bl_official[]"]');
    if (cb) cb.value = String(idx);
    list.appendChild(tpl);
}

// ── Photos par chambre ─────────────────────────────────────────────────────
function addRoomPhotoRow(btn) {
    const roomRow = btn.closest('.room-row');
    const list    = roomRow.querySelector('.room-photos-list');

    const row = document.createElement('div');
    row.className = 'room-photo-row flex gap-2 items-center';
    row.innerHTML =
        '<div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">'
        + '<img src="" alt="" class="w-full h-full object-cover" style="display:none"'
        + ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'\'">'
        + '<i class="fas fa-image text-slate-600 text-xs"></i>'
        + '</div>'
        + '<input type="text" placeholder="https://…/photo.jpg"'
        + ' class="photo-url-field flex-1 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-1.5 text-xs text-slate-100 outline-none transition"'
        + ' oninput="syncRoomPhotos(this)">'
        + '<button type="button" onclick="removeRoomPhotoRow(this)"'
        + ' class="w-8 h-[34px] rounded-lg bg-slate-700 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition shrink-0">'
        + '<i class="fas fa-times text-xs"></i>'
        + '</button>';

    list.appendChild(row);
    row.querySelector('input[type="text"]').focus();
}

function removeRoomPhotoRow(btn) {
    btn.closest('.room-photo-row').remove();
    // Resync hidden input after removal
    const roomRow = btn.closest('.room-row');
    if (roomRow) _syncRoomPhotosInput(roomRow);
}

function syncRoomPhotos(input) {
    const row = input.closest('.room-photo-row');

    // Mise à jour de la miniature
    const img  = row.querySelector('img');
    const icon = row.querySelector('.fa-image');
    const url  = input.value.trim();
    if (url && img) {
        img.src           = url;
        img.style.display = '';
        if (icon) icon.style.display = 'none';
    } else if (img) {
        img.style.display = 'none';
        if (icon) icon.style.display = '';
    }

    // Synchronisation de l'input caché
    _syncRoomPhotosInput(input.closest('.room-row'));
}

function _syncRoomPhotosInput(roomRow) {
    if (!roomRow) return;
    const urls   = Array.from(roomRow.querySelectorAll('.photo-url-field'))
                        .map(function (i) { return i.value.trim(); })
                        .filter(Boolean);
    const hidden = roomRow.querySelector('.room-photos-input');
    if (hidden) hidden.value = urls.join(', ');
}
</script>
@endpush
@endsection
