@extends('layouts.app')

@section('title', isset($experience) ? 'Modifier — '.$experience->name : 'Nouveau site')
@section('page-title', isset($experience) ? $experience->name : 'Nouveau site touristique')

@section('header-actions')
<a href="{{ route('admin.tourist-experiences.index') }}"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-arrow-left"></i> Retour à la liste
</a>
@endsection

@section('content')
@php $isEdit = isset($experience); @endphp

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
      action="{{ $isEdit ? route('admin.tourist-experiences.update', $experience) : route('admin.tourist-experiences.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ═══════════════ COLONNE PRINCIPALE (2/3) ═══════════════ --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- ① Infos générales --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-landmark text-orange-400"></i> Informations générales
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Nom <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $experience->name ?? '') }}" required maxlength="150"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ville <span class="text-red-400">*</span></label>
                        <select name="city_id" required class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            <option value="">Choisir…</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ (string) old('city_id', $experience->city_id ?? '') === (string) $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
                        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $experience->sort_order ?? 0) }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Description courte</label>
                        <input type="text" name="short_description" maxlength="300" value="{{ old('short_description', $experience->short_description ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-400 mb-1">Description complète</label>
                        <textarea name="description" rows="5"
                                  class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">{{ old('description', $experience->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ② Localisation --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-location-dot text-orange-400"></i> Localisation
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse', $experience->adresse ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Quartier</label>
                        <input type="text" name="quartier" value="{{ old('quartier', $experience->quartier ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Latitude</label>
                        <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $experience->latitude ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Longitude</label>
                        <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $experience->longitude ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                </div>
            </div>

            {{-- ③ Contact --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-phone text-orange-400"></i> Contact
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $experience->phone ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $experience->email ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Site web</label>
                        <input type="url" name="website" value="{{ old('website', $experience->website ?? '') }}"
                               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                    </div>
                </div>
            </div>

            {{-- ④ Équipements --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-list-check text-orange-400"></i> Équipements &amp; services
                </h2>
                <div id="amenity-list" class="space-y-2">
                    @php $amenities = old('amenity_labels') ? null : ($experience->amenities ?? []); @endphp
                    @if($amenities)
                        @foreach($amenities as $am)
                        <div class="amenity-row flex gap-2">
                            <input type="text" name="amenity_icons[]" value="{{ $am['icon'] ?? 'fas fa-check' }}" placeholder="fas fa-wifi"
                                   class="w-32 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                            <input type="text" name="amenity_labels[]" value="{{ $am['label'] ?? '' }}" placeholder="Wifi gratuit"
                                   class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
                            <button type="button" onclick="this.closest('.amenity-row').remove()" class="w-9 h-9 shrink-0 bg-slate-800 hover:bg-red-900/50 text-slate-400 hover:text-red-300 rounded-lg flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>
                        </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" onclick="addAmenityRow()" class="mt-3 text-xs text-orange-400 hover:text-orange-300 flex items-center gap-1.5">
                    <i class="fas fa-plus"></i> Ajouter un équipement
                </button>
            </div>

            {{-- ⑤ Galerie photos --}}
            <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-photo-film text-orange-400"></i> Galerie photos
                </h2>
                @if($isEdit && $experience->media->isNotEmpty())
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-5">
                        @foreach($experience->media as $m)
                        <div class="relative group">
                            <img src="{{ $m->url }}" alt="" loading="lazy" class="w-full aspect-square object-cover rounded-lg border border-slate-700">
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                                <button type="submit" form="media-del-{{ $m->id }}" onclick="return confirm('Supprimer cette photo ?')"
                                        class="w-6 h-6 bg-red-900/80 hover:bg-red-700 text-red-200 rounded-md flex items-center justify-center">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
                <label class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-6 cursor-pointer transition group">
                    <i class="fas fa-cloud-arrow-up text-2xl text-slate-600 group-hover:text-orange-400/70 mb-2 transition"></i>
                    <span class="text-slate-500 text-sm group-hover:text-slate-300 transition">Ajouter des photos</span>
                    <span class="text-slate-700 text-xs mt-1">JPG, PNG, WebP — plusieurs fichiers acceptés</span>
                    <input type="file" name="media_files[]" multiple accept="image/*" class="hidden" onchange="showMediaPreviews(this)">
                </label>
                <div id="media-previews" class="flex flex-wrap gap-2 mt-3"></div>
            </div>

        </div>{{-- fin col principale --}}

        {{-- ═══════════════ COLONNE LATÉRALE (1/3) ═══════════════ --}}
        <div class="space-y-5">

            <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sticky top-4">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders text-orange-400"></i> Publication
                </h2>
                <div class="space-y-3 mb-4">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500"
                               {{ old('is_active', $experience->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-300 group-hover:text-white transition">Actif <span class="text-slate-500 text-xs">(visible sur le site)</span></span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500"
                               {{ old('is_featured', $experience->is_featured ?? false) ? 'checked' : '' }}>
                        <span class="text-sm text-slate-300 group-hover:text-white transition">En vedette</span>
                    </label>
                </div>
                <button type="submit" class="w-full px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> {{ $isEdit ? 'Enregistrer' : "Créer le site" }}
                </button>
                <a href="{{ route('admin.tourist-experiences.index') }}" class="mt-2 w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-xl transition flex items-center justify-center gap-2">
                    Annuler
                </a>
            </div>

            <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-image text-orange-400"></i> Images principales
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Image de couverture</label>
                        @if($isEdit && $experience->cover_image)
                            <img src="{{ $experience->cover_image }}" class="w-full h-28 object-cover rounded-lg border border-slate-700 mb-2">
                        @endif
                        <input type="file" name="cover_image_file" accept="image/*" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Vignette (thumbnail)</label>
                        @if($isEdit && $experience->thumbnail)
                            <img src="{{ $experience->thumbnail }}" class="w-full h-28 object-cover rounded-lg border border-slate-700 mb-2">
                        @endif
                        <input type="file" name="thumbnail_file" accept="image/*" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
                    </div>
                </div>
            </div>

            @if($isEdit)
            <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
                <h2 class="text-white font-semibold mb-3 flex items-center gap-2 text-sm">
                    <i class="fas fa-code text-slate-500"></i> Infos techniques
                </h2>
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between"><span class="text-slate-500">ID</span><span class="text-slate-300 font-mono">{{ $experience->id }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Slug</span><span class="text-slate-300 font-mono truncate max-w-[140px]" title="{{ $experience->slug }}">{{ $experience->slug }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Vues</span><span class="text-slate-300">{{ number_format($experience->views_count) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Prestataire lié</span><span class="text-slate-300 truncate max-w-[140px]">{{ $experience->provider?->name ?: '— aucun —' }}</span></div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-800">
                    <button type="submit" form="experience-del-form" onclick="return confirm('Supprimer définitivement « {{ addslashes($experience->name) }} » ?')"
                            class="w-full px-4 py-2 bg-slate-800 hover:bg-red-900/50 text-slate-400 hover:text-red-300 text-xs rounded-lg transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-trash text-[10px]"></i> Supprimer ce site
                    </button>
                </div>
            </div>
            @endif
        </div>{{-- fin col latérale --}}
    </div>
</form>

@if($isEdit)
@foreach($experience->media as $m)
<form id="media-del-{{ $m->id }}" method="POST" action="{{ route('admin.tourist-experiences.media.destroy', $m) }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endforeach
<form id="experience-del-form" method="POST" action="{{ route('admin.tourist-experiences.destroy', $experience) }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endif

<script>
function addAmenityRow() {
    const wrap = document.getElementById('amenity-list');
    const row = document.createElement('div');
    row.className = 'amenity-row flex gap-2';
    row.innerHTML = `
        <input type="text" name="amenity_icons[]" value="fas fa-check" placeholder="fas fa-wifi"
               class="w-32 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100 outline-none">
        <input type="text" name="amenity_labels[]" placeholder="Wifi gratuit"
               class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
        <button type="button" onclick="this.closest('.amenity-row').remove()" class="w-9 h-9 shrink-0 bg-slate-800 hover:bg-red-900/50 text-slate-400 hover:text-red-300 rounded-lg flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>`;
    wrap.appendChild(row);
}

function showMediaPreviews(input) {
    const container = document.getElementById('media-previews');
    container.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded-lg border border-slate-700';
            img.title = file.name;
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endsection
