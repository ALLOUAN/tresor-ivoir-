@extends('layouts.app')

@section('title', 'Régions Touristiques — Villes')
@section('page-title', 'Villes de Côte d\'Ivoire')

@section('header-actions')
<a href="{{ route('tourist.cities') }}" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-eye"></i> Voir le site
</a>
<button onclick="openCityModal()"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvelle ville
</button>
@endsection

@section('content')

@include('admin.tourist.partials.subnav')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['fas fa-city',       'text-slate-300',   $counts['total'],    'Total'],
        ['fas fa-circle-check','text-emerald-400', $counts['active'],   'Actives'],
        ['fas fa-star',        'text-amber-400',   $counts['featured'], 'En vedette'],
    ] as [$icon, $color, $val, $lbl])
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <i class="{{ $icon }} {{ $color }} text-sm mb-2 block"></i>
        <p class="text-2xl font-bold text-white">{{ $val }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $lbl }}</p>
    </div>
    @endforeach
</div>

{{-- Search --}}
<form method="GET" action="{{ route('admin.tourist.cities.index') }}" class="mb-5 flex gap-2">
    <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher une ville…"
        class="flex-1 bg-slate-900 border border-slate-800 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition placeholder-slate-600">
    <button class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-search"></i>
    </button>
</form>

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Table --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Ville</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">District / Région</th>
                    <th class="text-left px-5 py-3">Sites</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($cities as $city)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($city->thumbnail)
                            <img src="{{ $city->thumbnail }}" class="w-10 h-10 rounded-lg object-cover shrink-0 hidden sm:block">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 hidden sm:block">
                                <i class="fas fa-city text-slate-600 text-sm"></i>
                            </div>
                            @endif
                            <div>
                                <p class="text-white font-medium">{{ $city->name }}</p>
                                @if($city->is_featured)
                                <span class="text-amber-400 text-[10px]"><i class="fas fa-star mr-0.5"></i>En vedette</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell text-slate-400 text-xs">
                        {{ $city->district ?? '—' }}<br>
                        <span class="text-slate-600">{{ $city->region_administrative ?? '' }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-amber-400 font-semibold">{{ $city->sites_count }}</span>
                        <span class="text-slate-600 text-xs ml-1">site(s)</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium border
                            {{ $city->is_active ? 'bg-emerald-900/40 text-emerald-300 border-emerald-800' : 'bg-slate-800 text-slate-500 border-slate-700' }}">
                            {{ $city->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Featured toggle --}}
                            <form method="POST" action="{{ route('admin.tourist.cities.featured', $city) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center text-slate-400 hover:text-amber-300 transition"
                                    title="{{ $city->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}">
                                    <i class="fas fa-star text-xs {{ $city->is_featured ? 'text-amber-400' : '' }}"></i>
                                </button>
                            </form>
                            {{-- Active toggle --}}
                            <form method="POST" action="{{ route('admin.tourist.cities.toggle', $city) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 transition"
                                    title="{{ $city->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $city->is_active ? 'eye' : 'eye-slash' }} text-xs"></i>
                                </button>
                            </form>
                            {{-- Edit --}}
                            <button onclick="openCityModal({{ $city->toJson() }})"
                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center text-slate-400 hover:text-amber-300 transition" title="Modifier">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.tourist.cities.destroy', $city) }}"
                                onsubmit="return confirm('Supprimer la ville « {{ addslashes($city->name) }} » ?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center text-slate-500">
                        <i class="fas fa-city text-3xl mb-3 block text-slate-700"></i>
                        Aucune ville enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cities->hasPages())
    <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
        <span>{{ $cities->firstItem() }}–{{ $cities->lastItem() }} sur {{ $cities->total() }}</span>
        <div class="flex gap-1">
            @if(!$cities->onFirstPage())
            <a href="{{ $cities->previousPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">← Préc.</a>
            @endif
            @if($cities->hasMorePages())
            <a href="{{ $cities->nextPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">Suiv. →</a>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Modal Créer / Modifier Ville --}}
<div id="cityModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeCityModal()"></div>
    <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <h3 id="cityModalTitle" class="text-white font-semibold mb-5">Nouvelle ville</h3>
        <form id="cityForm" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div id="methodField"></div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Nom de la ville <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="city_name" required maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">District</label>
                <input type="text" name="district" id="city_district" maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Région administrative</label>
                <input type="text" name="region_administrative" id="city_region" maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Latitude</label>
                <input type="number" step="any" name="latitude" id="city_lat"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Longitude</label>
                <input type="number" step="any" name="longitude" id="city_lng"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            {{-- ── BANNIÈRE PRINCIPALE ──────────────────────────────── --}}
            <div class="md:col-span-2">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs text-slate-400">
                        <i class="fas fa-panorama text-amber-400/70 mr-1"></i>
                        Bannière principale <span class="text-slate-600">(haut de la page ville)</span>
                    </label>
                    {{-- Toggle URL / Upload --}}
                    <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5">
                        <button type="button" id="cover_btn_url"
                            onclick="setCoverMode('url')"
                            class="px-2.5 py-1 rounded-md text-[11px] font-medium transition bg-slate-700 text-white">
                            <i class="fas fa-link mr-1"></i>URL
                        </button>
                        <button type="button" id="cover_btn_file"
                            onclick="setCoverMode('file')"
                            class="px-2.5 py-1 rounded-md text-[11px] font-medium transition text-slate-500 hover:text-white">
                            <i class="fas fa-upload mr-1"></i>Uploader
                        </button>
                    </div>
                </div>

                {{-- Mode URL --}}
                <div id="cover_url_section">
                    <input type="url" name="cover_image" id="city_cover" maxlength="500"
                        placeholder="https://… (URL de la bannière)"
                        oninput="previewCityBanner(this.value)"
                        class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                </div>

                {{-- Mode Upload --}}
                <div id="cover_file_section" class="hidden">
                    <label class="flex flex-col items-center justify-center w-full border-2 border-dashed border-slate-700 hover:border-amber-500/50 rounded-xl p-5 cursor-pointer transition group">
                        <i class="fas fa-cloud-arrow-up text-2xl text-slate-600 group-hover:text-amber-400/70 mb-2 transition"></i>
                        <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez pour choisir ou glissez une image</span>
                        <span class="text-slate-700 text-[10px] mt-1">JPG, PNG, WebP — max 5 Mo</span>
                        <input type="file" name="cover_image_file" id="city_cover_file"
                            accept="image/jpeg,image/png,image/webp,image/jpg"
                            class="hidden"
                            onchange="previewCityBannerFile(this)">
                    </label>
                </div>

                {{-- Aperçu bannière --}}
                <div id="city_banner_preview" class="hidden mt-2 w-full h-36 rounded-xl overflow-hidden border border-slate-700 relative bg-slate-800">
                    <img id="city_banner_img" src="" alt="Aperçu bannière" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-linear-to-t from-black/70 to-transparent flex items-end p-3">
                        <span id="city_banner_name_preview" class="text-white font-serif text-lg font-bold drop-shadow"></span>
                    </div>
                    <button type="button" onclick="clearCityBanner()"
                        class="absolute top-2 right-2 w-6 h-6 rounded-full bg-black/60 hover:bg-red-600 text-white flex items-center justify-center transition text-xs">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
                <div id="city_banner_empty"
                    class="mt-2 w-full h-10 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-700 text-xs gap-2">
                    <i class="fas fa-image"></i> Aucune image sélectionnée
                </div>
            </div>

            {{-- ── MINIATURE ────────────────────────────────────────── --}}
            <div class="md:col-span-2">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs text-slate-400">
                        <i class="fas fa-image text-amber-400/70 mr-1"></i>
                        Miniature <span class="text-slate-600">(vignette dans la liste des villes)</span>
                    </label>
                    <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5">
                        <button type="button" id="thumb_btn_url"
                            onclick="setThumbMode('url')"
                            class="px-2.5 py-1 rounded-md text-[11px] font-medium transition bg-slate-700 text-white">
                            <i class="fas fa-link mr-1"></i>URL
                        </button>
                        <button type="button" id="thumb_btn_file"
                            onclick="setThumbMode('file')"
                            class="px-2.5 py-1 rounded-md text-[11px] font-medium transition text-slate-500 hover:text-white">
                            <i class="fas fa-upload mr-1"></i>Uploader
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    {{-- Aperçu miniature --}}
                    <div class="w-20 h-14 rounded-xl overflow-hidden border border-slate-700 bg-slate-800 flex items-center justify-center shrink-0">
                        <i id="city_thumb_placeholder" class="fas fa-image text-slate-600 text-lg"></i>
                        <img id="city_thumb_img" src="" alt="" class="hidden w-full h-full object-cover">
                    </div>
                    <div class="flex-1 space-y-2">
                        {{-- Mode URL --}}
                        <div id="thumb_url_section">
                            <input type="url" name="thumbnail" id="city_thumbnail" maxlength="500"
                                placeholder="https://… (URL de la miniature)"
                                oninput="previewCityThumb(this.value)"
                                class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                        </div>
                        {{-- Mode Upload --}}
                        <div id="thumb_file_section" class="hidden">
                            <label class="flex items-center gap-2 border border-dashed border-slate-700 hover:border-amber-500/50 rounded-lg px-3 py-2.5 cursor-pointer transition group">
                                <i class="fas fa-cloud-arrow-up text-slate-600 group-hover:text-amber-400/70 transition"></i>
                                <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Choisir une miniature</span>
                                <input type="file" name="thumbnail_file" id="city_thumb_file"
                                    accept="image/jpeg,image/png,image/webp,image/jpg"
                                    class="hidden"
                                    onchange="previewCityThumbFile(this)">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Description</label>
                <textarea name="description" id="city_description" rows="3"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-none"></textarea>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
                <input type="number" name="sort_order" id="city_sort_order" min="0" value="0"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            </div>

            <div class="flex items-end gap-5 pb-1">
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" id="city_is_active" value="1" checked
                        class="rounded border-slate-600 bg-slate-800 text-amber-500">
                    Active
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_featured" id="city_is_featured" value="1"
                        class="rounded border-slate-600 bg-slate-800 text-amber-500">
                    En vedette
                </label>
            </div>

            <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeCityModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold rounded-lg transition">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const cityStoreUrl   = "{{ route('admin.tourist.cities.store') }}";
const cityUpdateBase = "{{ url('admin/tourisme/villes') }}";

// ── Toggles URL / Upload ───────────────────────────────────────────────────
function setCoverMode(mode) {
    const urlSec  = document.getElementById('cover_url_section');
    const fileSec = document.getElementById('cover_file_section');
    const btnUrl  = document.getElementById('cover_btn_url');
    const btnFile = document.getElementById('cover_btn_file');
    const active  = 'bg-slate-700 text-white';
    const inactive= 'text-slate-500 hover:text-white';
    if (mode === 'url') {
        urlSec.classList.remove('hidden');
        fileSec.classList.add('hidden');
        btnUrl.className  = btnUrl.className.replace(inactive, active);
        btnFile.className = btnFile.className.replace(active, inactive);
        // vider le file input pour ne pas l'envoyer
        document.getElementById('city_cover_file').value = '';
    } else {
        urlSec.classList.add('hidden');
        fileSec.classList.remove('hidden');
        btnFile.className = btnFile.className.replace(inactive, active);
        btnUrl.className  = btnUrl.className.replace(active, inactive);
    }
}

function setThumbMode(mode) {
    const urlSec  = document.getElementById('thumb_url_section');
    const fileSec = document.getElementById('thumb_file_section');
    const btnUrl  = document.getElementById('thumb_btn_url');
    const btnFile = document.getElementById('thumb_btn_file');
    const active  = 'bg-slate-700 text-white';
    const inactive= 'text-slate-500 hover:text-white';
    if (mode === 'url') {
        urlSec.classList.remove('hidden');
        fileSec.classList.add('hidden');
        btnUrl.className  = btnUrl.className.replace(inactive, active);
        btnFile.className = btnFile.className.replace(active, inactive);
        document.getElementById('city_thumb_file').value = '';
    } else {
        urlSec.classList.add('hidden');
        fileSec.classList.remove('hidden');
        btnFile.className = btnFile.className.replace(inactive, active);
        btnUrl.className  = btnUrl.className.replace(active, inactive);
    }
}

// ── Aperçu bannière depuis fichier ─────────────────────────────────────────
function previewCityBannerFile(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('city_banner_img').src = e.target.result;
        document.getElementById('city_banner_name_preview').textContent =
            document.getElementById('city_name').value || '';
        document.getElementById('city_banner_preview').classList.remove('hidden');
        document.getElementById('city_banner_empty').classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Aperçu miniature depuis fichier ────────────────────────────────────────
function previewCityThumbFile(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('city_thumb_img');
        img.src = e.target.result;
        img.classList.remove('hidden');
        document.getElementById('city_thumb_placeholder').classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Aperçu bannière ────────────────────────────────────────────────────────
function previewCityBanner(url) {
    const preview = document.getElementById('city_banner_preview');
    const empty   = document.getElementById('city_banner_empty');
    const img     = document.getElementById('city_banner_img');
    const name    = document.getElementById('city_banner_name_preview');

    if (url && url.startsWith('http')) {
        img.src = url;
        img.onload = () => {
            preview.classList.remove('hidden');
            empty.classList.add('hidden');
            name.textContent = document.getElementById('city_name').value || '';
        };
        img.onerror = () => {
            preview.classList.add('hidden');
            empty.classList.remove('hidden');
            empty.innerHTML = '<i class="fas fa-exclamation-triangle text-red-400"></i> <span class="text-red-400">URL invalide ou image inaccessible</span>';
        };
    } else {
        preview.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.innerHTML = '<i class="fas fa-image"></i> Entrez une URL pour prévisualiser la bannière';
    }
}

function clearCityBanner() {
    document.getElementById('city_cover').value = '';
    document.getElementById('city_banner_preview').classList.add('hidden');
    document.getElementById('city_banner_empty').classList.remove('hidden');
    document.getElementById('city_banner_empty').innerHTML = '<i class="fas fa-image"></i> Entrez une URL pour prévisualiser la bannière';
}

// ── Aperçu miniature ───────────────────────────────────────────────────────
function previewCityThumb(url) {
    const img         = document.getElementById('city_thumb_img');
    const placeholder = document.getElementById('city_thumb_placeholder');

    if (url && url.startsWith('http')) {
        img.src = url;
        img.onload = () => {
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        img.onerror = () => {
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
        };
    } else {
        img.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}

// ── Sync nom → aperçu bannière ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('city_name').addEventListener('input', function () {
        const el = document.getElementById('city_banner_name_preview');
        if (el) el.textContent = this.value;
    });
});

// ── Ouvrir / fermer modal ──────────────────────────────────────────────────
function openCityModal(city = null) {
    const form  = document.getElementById('cityForm');
    const title = document.getElementById('cityModalTitle');
    const mf    = document.getElementById('methodField');

    // Reset previews
    document.getElementById('city_banner_preview').classList.add('hidden');
    document.getElementById('city_banner_empty').classList.remove('hidden');
    document.getElementById('city_banner_empty').innerHTML = '<i class="fas fa-image"></i> Entrez une URL pour prévisualiser la bannière';
    document.getElementById('city_thumb_img').classList.add('hidden');
    document.getElementById('city_thumb_placeholder').classList.remove('hidden');

    if (city) {
        title.textContent = 'Modifier la ville';
        form.action = `${cityUpdateBase}/${city.id}`;
        mf.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('city_name').value           = city.name || '';
        document.getElementById('city_district').value       = city.district || '';
        document.getElementById('city_region').value         = city.region_administrative || '';
        document.getElementById('city_lat').value            = city.latitude || '';
        document.getElementById('city_lng').value            = city.longitude || '';
        document.getElementById('city_cover').value          = city.cover_image || '';
        document.getElementById('city_thumbnail').value      = city.thumbnail || '';
        document.getElementById('city_description').value    = city.description || '';
        document.getElementById('city_sort_order').value     = city.sort_order || 0;
        document.getElementById('city_is_active').checked    = city.is_active == 1;
        document.getElementById('city_is_featured').checked  = city.is_featured == 1;

        // Charger les aperçus si des URLs existent
        if (city.cover_image) previewCityBanner(city.cover_image);
        if (city.thumbnail)   previewCityThumb(city.thumbnail);

    } else {
        title.textContent = 'Nouvelle ville';
        form.action = cityStoreUrl;
        mf.innerHTML = '';
        form.reset();
        document.getElementById('city_is_active').checked = true;
        document.getElementById('city_sort_order').value  = 0;
    }

    const modal = document.getElementById('cityModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCityModal() {
    const modal = document.getElementById('cityModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCityModal(); });
</script>
@endpush

@endsection
