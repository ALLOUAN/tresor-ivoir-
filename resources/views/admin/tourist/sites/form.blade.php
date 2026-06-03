@extends('layouts.app')

@section('title', isset($site) ? 'Modifier — '.$site->name : 'Nouveau site touristique')
@section('page-title', isset($site) ? 'Modifier le site' : 'Nouveau site touristique')

@section('header-actions')
<a href="{{ route('admin.tourist.sites.index') }}"
    class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-arrow-left"></i> Retour à la liste
</a>
@endsection

@section('content')

@include('admin.tourist.partials.subnav')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" enctype="multipart/form-data"
    action="{{ isset($site) ? route('admin.tourist.sites.update', $site) : route('admin.tourist.sites.store') }}"
    class="space-y-6">
    @csrf
    @isset($site) @method('PUT') @endisset

    {{-- ── SECTION 1 : Informations générales ──────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
            <i class="fas fa-info-circle text-amber-400"></i> Informations générales
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block text-xs text-slate-400 mb-1">Ville <span class="text-red-400">*</span></label>
                <select name="city_id" required
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <option value="">— Choisir une ville —</option>
                    @foreach($cities as $c)
                    <option value="{{ $c->id }}" {{ old('city_id', $site->city_id ?? '') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Catégorie <span class="text-red-400">*</span></label>
                <select name="category_id" required
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    <option value="">— Choisir une catégorie —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $site->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Nom du site <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $site->name ?? '') }}" required maxlength="150"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Description courte <span class="text-slate-600">(max 300 car.)</span></label>
                <input type="text" name="short_description" value="{{ old('short_description', $site->short_description ?? '') }}" maxlength="300"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Description complète</label>
                <textarea name="description" rows="5"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition resize-y">{{ old('description', $site->description ?? '') }}</textarea>
            </div>

            {{-- ── Image principale (thumbnail) ──────────────────────── --}}
            <div class="md:col-span-2">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs text-slate-400">
                        <i class="fas fa-image text-amber-400/70 mr-1"></i>
                        Image principale <span class="text-slate-600">(affichée sur la carte et en haut du détail)</span>
                    </label>
                    <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5">
                        <button type="button" id="site_thumb_btn_url" onclick="setSiteThumbMode('url')"
                            class="px-2.5 py-1 rounded-md text-[11px] font-medium transition bg-slate-700 text-white">
                            <i class="fas fa-link mr-1"></i>URL
                        </button>
                        <button type="button" id="site_thumb_btn_file" onclick="setSiteThumbMode('file')"
                            class="px-2.5 py-1 rounded-md text-[11px] font-medium transition text-slate-500 hover:text-white">
                            <i class="fas fa-upload mr-1"></i>Uploader
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    {{-- Aperçu --}}
                    <div class="w-28 h-20 rounded-xl overflow-hidden border border-slate-700 bg-slate-800 flex items-center justify-center shrink-0">
                        <i id="site_thumb_placeholder" class="{{ isset($site) && $site->thumbnail ? 'hidden' : '' }} fas fa-image text-slate-600 text-xl"></i>
                        <img id="site_thumb_img"
                            src="{{ old('thumbnail', $site->thumbnail ?? '') }}"
                            alt="Aperçu"
                            class="{{ isset($site) && $site->thumbnail ? '' : 'hidden' }} w-full h-full object-cover">
                    </div>
                    <div class="flex-1 space-y-2">
                        {{-- Mode URL --}}
                        <div id="site_thumb_url_section">
                            <input type="url" name="thumbnail"
                                id="site_thumbnail"
                                value="{{ old('thumbnail', $site->thumbnail ?? '') }}"
                                maxlength="500"
                                placeholder="https://… (URL de l'image principale)"
                                oninput="previewSiteThumb(this.value)"
                                class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                        </div>
                        {{-- Mode Upload --}}
                        <div id="site_thumb_file_section" class="hidden">
                            <label class="flex items-center gap-2 border border-dashed border-slate-700 hover:border-amber-500/50 rounded-lg px-3 py-3 cursor-pointer transition group">
                                <i class="fas fa-cloud-arrow-up text-slate-600 group-hover:text-amber-400/70 transition"></i>
                                <div>
                                    <span class="text-slate-400 text-xs group-hover:text-slate-200 transition block">Choisir une image principale</span>
                                    <span class="text-slate-700 text-[10px]">JPG, PNG, WebP — max 5 Mo</span>
                                </div>
                                <input type="file" name="thumbnail_file" id="site_thumb_file"
                                    accept="image/jpeg,image/png,image/webp,image/jpg"
                                    class="hidden"
                                    onchange="previewSiteThumbFile(this)">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Tarif d'entrée</label>
                <input type="text" name="entrance_fee" value="{{ old('entrance_fee', $site->entrance_fee ?? '') }}" maxlength="100" placeholder="Ex: Gratuit, 500 FCFA…"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Site web</label>
                <input type="url" name="website" value="{{ old('website', $site->website ?? '') }}" maxlength="300"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $site->phone ?? '') }}" maxlength="30"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $site->email ?? '') }}" maxlength="150"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div class="flex items-end gap-5 pb-1">
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $site->is_active ?? true) ? 'checked' : '' }}
                        class="rounded border-slate-600 bg-slate-800 text-amber-500">
                    Actif
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $site->is_featured ?? false) ? 'checked' : '' }}
                        class="rounded border-slate-600 bg-slate-800 text-amber-500">
                    En vedette
                </label>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $site->sort_order ?? 0) }}" min="0"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
        </div>
    </div>

    {{-- ── SECTION 2 : Situation géographique ───────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
            <i class="fas fa-map-location-dot text-amber-400"></i> Situation géographique
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            <div>
                <label class="block text-xs text-slate-400 mb-1">Latitude GPS</label>
                <input type="number" step="any" name="latitude" value="{{ old('latitude', $site->latitude ?? '') }}"
                    placeholder="Ex: 5.3599517"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Longitude GPS</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude', $site->longitude ?? '') }}"
                    placeholder="Ex: -4.0082563"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Département</label>
                <input type="text" name="departement" value="{{ old('departement', $site->departement ?? '') }}" maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Sous-préfecture</label>
                <input type="text" name="sous_prefecture" value="{{ old('sous_prefecture', $site->sous_prefecture ?? '') }}" maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Localité / Quartier</label>
                <input type="text" name="localite" value="{{ old('localite', $site->localite ?? '') }}" maxlength="150"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Distance du centre (km)</label>
                <input type="number" step="0.1" name="distance_centre_km" value="{{ old('distance_centre_km', $site->distance_centre_km ?? '') }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Altitude (m)</label>
                <input type="number" name="altitude_m" value="{{ old('altitude_m', $site->altitude_m ?? '') }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Superficie (ha)</label>
                <input type="number" step="0.01" name="superficie_ha" value="{{ old('superficie_ha', $site->superficie_ha ?? '') }}"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Point de repère</label>
                <input type="text" name="point_repere" value="{{ old('point_repere', $site->point_repere ?? '') }}" maxlength="250"
                    placeholder="Ex: À 2 km après le marché..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div class="md:col-span-2 lg:col-span-3">
                <label class="block text-xs text-slate-400 mb-1">Comment s'y rendre</label>
                <textarea name="acces_description" rows="3"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none">{{ old('acces_description', $site->acces_description ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2 lg:col-span-3">
                <label class="block text-xs text-slate-400 mb-1">Code intégration Google Maps (iframe src)</label>
                <input type="text" name="map_embed_url" value="{{ old('map_embed_url', $site->map_embed_url ?? '') }}"
                    placeholder="https://www.google.com/maps/embed?pb=..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
        </div>
    </div>

    {{-- ── SECTION 3 : Horaires d'ouverture ─────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-clock text-amber-400"></i> Horaires d'ouverture
            </h2>
            <button type="button" onclick="addScheduleRow()"
                class="text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                <i class="fas fa-plus"></i> Ajouter un jour
            </button>
        </div>
        <div id="scheduleRows" class="space-y-3">
            @php $schedules = old('schedule_day') ? null : ($site->schedules ?? []); @endphp
            @if(!empty($schedules))
                @foreach($schedules as $i => $s)
                <div class="schedule-row grid grid-cols-12 gap-3 items-center">
                    <div class="col-span-3">
                        <input type="text" name="schedule_day[]" value="{{ $s['day'] }}" placeholder="Lundi"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                    <div class="col-span-3">
                        <input type="time" name="schedule_opens[]" value="{{ $s['opens'] }}"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                    <div class="col-span-3">
                        <input type="time" name="schedule_closes[]" value="{{ $s['closes'] }}"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                    <div class="col-span-2 flex items-center gap-2 text-xs text-slate-400">
                        <input type="checkbox" name="schedule_closed[{{ $i }}]" value="1" {{ ($s['closed'] ?? false) ? 'checked' : '' }}
                            class="rounded border-slate-600 bg-slate-800 text-amber-500">
                        Fermé
                    </div>
                    <div class="col-span-1 flex justify-end">
                        <button type="button" onclick="this.closest('.schedule-row').remove()"
                            class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/40 text-slate-500 hover:text-red-300 flex items-center justify-center transition">
                            <i class="fas fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        <p class="text-slate-600 text-xs mt-3">Laissez vide si les horaires ne sont pas applicables.</p>
    </div>

    {{-- ── SECTION 4 : Informations pratiques ──────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-white font-semibold flex items-center gap-2">
                <i class="fas fa-circle-info text-amber-400"></i> Informations pratiques
            </h2>
            <button type="button" onclick="addInfoRow()"
                class="text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                <i class="fas fa-plus"></i> Ajouter une info
            </button>
        </div>
        <div id="infoRows" class="space-y-3">
            @php $practicalInfo = old('info_label') ? null : ($site->practical_info ?? []); @endphp
            @if(!empty($practicalInfo))
                @foreach($practicalInfo as $info)
                <div class="info-row grid grid-cols-12 gap-3 items-center">
                    <div class="col-span-2">
                        <input type="text" name="info_icon[]" value="{{ $info['icon'] ?? '' }}" placeholder="fas fa-car"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                    <div class="col-span-3">
                        <input type="text" name="info_label[]" value="{{ $info['label'] }}" placeholder="Parking"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                    <div class="col-span-6">
                        <input type="text" name="info_value[]" value="{{ $info['value'] }}" placeholder="Gratuit sur place"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                    <div class="col-span-1 flex justify-end">
                        <button type="button" onclick="this.closest('.info-row').remove()"
                            class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/40 text-slate-500 hover:text-red-300 flex items-center justify-center transition">
                            <i class="fas fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ── SECTION 5 : Médias ───────────────────────────────────────────── --}}
    @isset($site)
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
            <i class="fas fa-images text-amber-400"></i> Photos & Vidéos
            <span class="text-slate-600 font-normal text-xs ml-1">({{ $site->media->count() }} fichier(s))</span>
        </h2>

        {{-- Médias existants --}}
        @if($site->media->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
            @foreach($site->media as $m)
            <div class="relative group rounded-xl overflow-hidden bg-slate-800 border border-slate-700">
                @if($m->type === 'photo')
                <img src="{{ $m->url }}" class="w-full h-28 object-cover">
                @else
                <div class="w-full h-28 flex items-center justify-center bg-slate-700">
                    <i class="fas fa-play-circle text-3xl text-slate-400"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                    <form method="POST" action="{{ route('admin.tourist.media.destroy', $m) }}">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </form>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-black/60 px-2 py-1 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">{{ $m->type === 'photo' ? '📷' : '🎬' }} {{ $m->caption ?: '—' }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Onglets Upload / URL --}}
        <div class="border-t border-slate-800 pt-5">
            <div class="flex items-center gap-3 mb-4">
                <p class="text-xs text-slate-400 font-medium">Ajouter des médias :</p>
                <div class="flex gap-1 bg-slate-800 rounded-lg p-0.5">
                    <button type="button" id="media_btn_upload" onclick="setMediaMode('upload')"
                        class="px-3 py-1.5 rounded-md text-xs font-medium transition bg-slate-700 text-white">
                        <i class="fas fa-upload mr-1"></i>Uploader des photos
                    </button>
                    <button type="button" id="media_btn_url" onclick="setMediaMode('url')"
                        class="px-3 py-1.5 rounded-md text-xs font-medium transition text-slate-500 hover:text-white">
                        <i class="fas fa-link mr-1"></i>Ajouter par URL
                    </button>
                </div>
            </div>

            {{-- Upload multiple photos --}}
            <div id="media_upload_section">
                <label id="media_drop_zone"
                    class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-2xl p-8 cursor-pointer transition group">
                    <i class="fas fa-cloud-arrow-up text-4xl text-slate-600 group-hover:text-amber-400/70 transition mb-3"></i>
                    <p class="text-slate-400 text-sm group-hover:text-slate-200 transition font-medium">Cliquez ou glissez vos photos ici</p>
                    <p class="text-slate-600 text-xs mt-1">JPG, PNG, WebP — max 5 Mo par fichier — plusieurs fichiers acceptés</p>
                    <input type="file" name="media_files[]" id="media_files_input"
                        accept="image/jpeg,image/png,image/webp,image/jpg"
                        multiple class="hidden"
                        onchange="previewMediaFiles(this)">
                </label>
                {{-- Aperçus photos sélectionnées --}}
                <div id="media_files_preview" class="hidden mt-3 grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2"></div>
            </div>

            {{-- Ajout par URL --}}
            <div id="media_url_section" class="hidden">
                <div class="grid grid-cols-12 gap-2 mb-1 text-[10px] text-slate-600 uppercase tracking-wide px-1">
                    <div class="col-span-2">Type</div>
                    <div class="col-span-4">URL</div>
                    <div class="col-span-3">Miniature vidéo</div>
                    <div class="col-span-2">Légende</div>
                    <div class="col-span-1">Alt</div>
                </div>
                <div id="newMediaRows" class="space-y-2">
                    <div class="media-row grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-2">
                            <select name="media_type[]"
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-2 text-xs text-slate-100 outline-none">
                                <option value="photo">Photo</option>
                                <option value="video">Vidéo</option>
                            </select>
                        </div>
                        <div class="col-span-4">
                            <input type="url" name="media_url[]" placeholder="https://…"
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                        </div>
                        <div class="col-span-3">
                            <input type="url" name="media_thumbnail_url[]" placeholder="https://… (vidéo)"
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                        </div>
                        <div class="col-span-2">
                            <input type="text" name="media_caption[]" placeholder="Légende"
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                        </div>
                        <div class="col-span-1">
                            <input type="text" name="media_alt_text[]" placeholder="Alt"
                                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addMediaRow()"
                    class="mt-2 text-xs text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                    <i class="fas fa-plus"></i> Ajouter une ligne
                </button>
            </div>
        </div>
    </div>
    @endisset

    {{-- ── Submit ──────────────────────────────────────────────────────── --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.tourist.sites.index') }}"
            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">
            Annuler
        </a>
        <button type="submit"
            class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold rounded-lg transition">
            <i class="fas fa-floppy-disk mr-1.5"></i>
            {{ isset($site) ? 'Mettre à jour' : 'Créer le site' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
function addScheduleRow() {
    const container = document.getElementById('scheduleRows');
    const idx = container.querySelectorAll('.schedule-row').length;
    const div = document.createElement('div');
    div.className = 'schedule-row grid grid-cols-12 gap-3 items-center';
    div.innerHTML = `
        <div class="col-span-3">
            <input type="text" name="schedule_day[]" placeholder="Lundi"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-3">
            <input type="time" name="schedule_opens[]"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-3">
            <input type="time" name="schedule_closes[]"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-2 flex items-center gap-2 text-xs text-slate-400">
            <input type="checkbox" name="schedule_closed[${idx}]" value="1"
                class="rounded border-slate-600 bg-slate-800 text-amber-500">
            Fermé
        </div>
        <div class="col-span-1 flex justify-end">
            <button type="button" onclick="this.closest('.schedule-row').remove()"
                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/40 text-slate-500 hover:text-red-300 flex items-center justify-center transition">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>`;
    container.appendChild(div);
}

function addInfoRow() {
    const container = document.getElementById('infoRows');
    const div = document.createElement('div');
    div.className = 'info-row grid grid-cols-12 gap-3 items-center';
    div.innerHTML = `
        <div class="col-span-2">
            <input type="text" name="info_icon[]" placeholder="fas fa-car"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-3">
            <input type="text" name="info_label[]" placeholder="Parking"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-6">
            <input type="text" name="info_value[]" placeholder="Gratuit sur place"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-1 flex justify-end">
            <button type="button" onclick="this.closest('.info-row').remove()"
                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/40 text-slate-500 hover:text-red-300 flex items-center justify-center transition">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>`;
    container.appendChild(div);
}

function addMediaRow() {
    const container = document.getElementById('newMediaRows');
    const div = document.createElement('div');
    div.className = 'media-row grid grid-cols-12 gap-2 items-center';
    div.innerHTML = `
        <div class="col-span-2">
            <select name="media_type[]"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-2 text-xs text-slate-100 outline-none">
                <option value="photo">Photo</option>
                <option value="video">Vidéo</option>
            </select>
        </div>
        <div class="col-span-4">
            <input type="url" name="media_url[]" placeholder="https://…"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-3">
            <input type="url" name="media_thumbnail_url[]" placeholder="https://… (vidéo)"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-2">
            <input type="text" name="media_caption[]" placeholder="Légende"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>
        <div class="col-span-1">
            <input type="text" name="media_alt_text[]" placeholder="Alt"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        </div>`;
    container.appendChild(div);
}

// ── Toggle mode médias ────────────────────────────────────────────────────
function setMediaMode(mode) {
    const uploadSec = document.getElementById('media_upload_section');
    const urlSec    = document.getElementById('media_url_section');
    const btnUpload = document.getElementById('media_btn_upload');
    const btnUrl    = document.getElementById('media_btn_url');
    const active    = 'bg-slate-700 text-white';
    const inactive  = 'text-slate-500 hover:text-white';

    if (mode === 'upload') {
        uploadSec.classList.remove('hidden');
        urlSec.classList.add('hidden');
        btnUpload.className = btnUpload.className.replace(inactive, active);
        btnUrl.className    = btnUrl.className.replace(active, inactive);
    } else {
        uploadSec.classList.add('hidden');
        urlSec.classList.remove('hidden');
        btnUrl.className    = btnUrl.className.replace(inactive, active);
        btnUpload.className = btnUpload.className.replace(active, inactive);
        document.getElementById('media_files_input').value = '';
        document.getElementById('media_files_preview').innerHTML = '';
        document.getElementById('media_files_preview').classList.add('hidden');
    }
}

// ── Aperçu thumbnail depuis URL ───────────────────────────────────────────
function previewSiteThumb(url) {
    const img         = document.getElementById('site_thumb_img');
    const placeholder = document.getElementById('site_thumb_placeholder');
    if (url && url.startsWith('http')) {
        img.src = url;
        img.onload  = () => { img.classList.remove('hidden'); placeholder.classList.add('hidden'); };
        img.onerror = () => { img.classList.add('hidden'); placeholder.classList.remove('hidden'); };
    } else {
        img.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}

// ── Aperçu thumbnail depuis fichier ──────────────────────────────────────
function previewSiteThumbFile(input) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('site_thumb_img');
        img.src = e.target.result;
        img.classList.remove('hidden');
        document.getElementById('site_thumb_placeholder').classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Toggle mode thumbnail ──────────────────────────────────────────────────
function setSiteThumbMode(mode) {
    const urlSec  = document.getElementById('site_thumb_url_section');
    const fileSec = document.getElementById('site_thumb_file_section');
    const btnUrl  = document.getElementById('site_thumb_btn_url');
    const btnFile = document.getElementById('site_thumb_btn_file');
    const active  = 'bg-slate-700 text-white';
    const inactive= 'text-slate-500 hover:text-white';
    if (mode === 'url') {
        urlSec.classList.remove('hidden');  fileSec.classList.add('hidden');
        btnUrl.className  = btnUrl.className.replace(inactive, active);
        btnFile.className = btnFile.className.replace(active, inactive);
        document.getElementById('site_thumb_file').value = '';
    } else {
        fileSec.classList.remove('hidden'); urlSec.classList.add('hidden');
        btnFile.className = btnFile.className.replace(inactive, active);
        btnUrl.className  = btnUrl.className.replace(active, inactive);
    }
}

// ── Aperçu photos sélectionnées pour upload multiple ─────────────────────
function previewMediaFiles(input) {
    const container = document.getElementById('media_files_preview');
    container.innerHTML = '';

    if (!input.files || !input.files.length) {
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');
    container.style.display = 'grid';

    Array.from(input.files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'relative rounded-xl overflow-hidden bg-slate-800 border border-slate-700';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-20 object-cover">
                <div class="absolute bottom-0 left-0 right-0 bg-black/60 px-1.5 py-1">
                    <p class="text-[9px] text-slate-300 truncate">${file.name}</p>
                </div>`;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush

@endsection
