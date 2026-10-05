@extends('layouts.app')

@section('title', 'Nos Prestations')
@section('page-title', 'Gestion — Nos Prestations')

@section('header-actions')
<a href="{{ route('prestations.public') }}" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-eye"></i> Voir la page publique
</a>
@endsection

@section('content')

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

{{-- ═══════════════ ÉTAT ACTUEL : ce qui est visible sur la page publique ═══════════════ --}}
<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-eye text-orange-400"></i> État actuel sur la page publique
        </h2>
        <a href="{{ route('prestations.public') }}" target="_blank"
           class="text-orange-400 hover:text-orange-300 text-xs font-medium transition inline-flex items-center gap-1">
            Ouvrir la page publique <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
        </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Bannières --}}
        @php $firstActiveBanner = $banners->firstWhere('is_active', true); @endphp
        <div class="rounded-lg border border-slate-800 bg-slate-800/30 overflow-hidden">
            @if($firstActiveBanner?->image_url)
                <img src="{{ $firstActiveBanner->image_url }}" class="w-full h-24 object-cover">
            @else
                <div class="w-full h-24 bg-slate-800 flex items-center justify-center text-slate-600">
                    <i class="fas fa-panorama text-xl"></i>
                </div>
            @endif
            <div class="p-3">
                <p class="text-slate-500 text-[11px] uppercase tracking-wide mb-1">Bannières</p>
                @php $activeBannerCount = $banners->where('is_active', true)->count(); @endphp
                <p class="text-white text-sm font-medium truncate">
                    {{ $activeBannerCount }} active{{ $activeBannerCount > 1 ? 's' : '' }}
                    <span class="text-slate-500 font-normal">/ {{ $banners->total() }} au total</span>
                </p>
            </div>
        </div>

        {{-- Vidéo --}}
        <div class="rounded-lg border border-slate-800 bg-slate-800/30 overflow-hidden">
            @if($settings->video_source === 'upload' && $settings->video_url)
                <video src="{{ $settings->video_url }}" @if($settings->video_poster_url) poster="{{ $settings->video_poster_url }}" @endif class="w-full h-24 object-cover"></video>
            @elseif($settings->video_source === 'embed' && $settings->video_embed_url)
                <div class="w-full h-24 bg-slate-800 flex items-center justify-center text-emerald-400">
                    <i class="fas fa-circle-play text-2xl"></i>
                </div>
            @else
                <div class="w-full h-24 bg-slate-800 flex items-center justify-center text-slate-600">
                    <i class="fas fa-circle-play text-xl"></i>
                </div>
            @endif
            <div class="p-3">
                <p class="text-slate-500 text-[11px] uppercase tracking-wide mb-1">Vidéo</p>
                <p class="text-white text-sm font-medium">
                    @if($settings->hasVideo())
                        {{ $settings->video_source === 'upload' ? 'Fichier envoyé' : 'Lien externe' }}
                    @else
                        Aucune vidéo
                    @endif
                </p>
            </div>
        </div>

        {{-- Catalogue --}}
        <div class="rounded-lg border border-slate-800 bg-slate-800/30 overflow-hidden">
            <div class="w-full h-24 bg-slate-800 flex items-center justify-center {{ $settings->catalog_file_url ? 'text-emerald-400' : 'text-slate-600' }}">
                <i class="fas fa-file-pdf text-2xl"></i>
            </div>
            <div class="p-3">
                <p class="text-slate-500 text-[11px] uppercase tracking-wide mb-1">Catalogue</p>
                <p class="text-white text-sm font-medium truncate mb-1">{{ $settings->catalog_title ?: ($settings->catalog_file_url ? 'Sans titre' : 'Aucun catalogue') }}</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $settings->catalog_enabled && $settings->catalog_file_url ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-600/30 text-slate-400 border border-slate-600/40' }}">
                    {{ $settings->catalog_enabled && $settings->catalog_file_url ? 'Visible publiquement' : 'Non affiché' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ BANNIÈRES (liste répétable) ═══════════════ --}}
<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20 mb-8">
    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-green-700 via-green-600 to-green-600">
        <div>
            <h2 class="text-white font-semibold text-lg tracking-tight">Bannières</h2>
            <p class="text-green-100/80 text-xs mt-0.5">Image, titre et contenu — plusieurs bannières défilent en carrousel sur la page publique.</p>
        </div>
        <button type="button" onclick="openCreateBannerModal()"
                class="inline-flex items-center justify-center gap-2 shrink-0 bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus"></i> Ajouter une bannière
        </button>
    </div>

    <div class="p-5 space-y-3">
        @forelse($banners as $banner)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-slate-800 bg-slate-800/20 hover:border-slate-700/80 transition">
            <div class="shrink-0">
                @if($banner->image_url)
                    <img src="{{ $banner->image_url }}" alt="" class="w-24 h-16 object-cover rounded-lg border border-slate-700">
                @else
                    <div class="w-24 h-16 rounded-lg border border-slate-700 bg-slate-800 flex items-center justify-center">
                        <i class="fas fa-panorama text-slate-600 text-xl"></i>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold truncate">{{ $banner->title ?: 'Sans titre' }}</p>
                @if($banner->content)
                    <p class="text-slate-500 text-xs mt-0.5 line-clamp-1">{{ $banner->content }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $banner->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-600/30 text-slate-300 border border-slate-600/40' }}">
                        {{ $banner->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/20 text-slate-300 border border-slate-600/40">
                        Ordre : {{ $banner->display_order }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-end sm:justify-center gap-1 shrink-0 border-t border-slate-800/80 sm:border-0 pt-3 sm:pt-0">
                <button type="button"
                        onclick="openEditBannerModal(this)"
                        data-id="{{ $banner->id }}"
                        data-title="{{ e($banner->title ?? '') }}"
                        data-content="{{ e($banner->content ?? '') }}"
                        data-link-url="{{ e($banner->link_url ?? '') }}"
                        data-link-label="{{ e($banner->link_label ?? '') }}"
                        data-display-order="{{ $banner->display_order }}"
                        data-is-active="{{ $banner->is_active ? '1' : '0' }}"
                        data-image-url="{{ e($banner->image_url ?? '') }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white transition"
                        title="Modifier">
                    <i class="fas fa-pen"></i>
                </button>
                <form method="POST" action="{{ route('admin.prestations.banners.toggle', $banner) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white transition"
                            title="{{ $banner->is_active ? 'Désactiver' : 'Activer' }}">
                        <i class="fas {{ $banner->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.prestations.banners.destroy', $banner) }}" class="inline" onsubmit="return confirm('Supprimer cette bannière ?');">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-red-700/90 hover:bg-red-600 text-white transition"
                            title="Supprimer">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-14 text-slate-500 border border-dashed border-slate-700 rounded-xl">
            <i class="fas fa-panorama text-3xl mb-3 text-slate-600"></i>
            <p>Aucune bannière pour le moment.</p>
            <button type="button" onclick="openCreateBannerModal()" class="mt-4 text-orange-400 hover:text-orange-300 text-sm font-medium">Ajouter la première bannière</button>
        </div>
        @endforelse
    </div>

    @if($banners->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $banners->links() }}
        </div>
    @endif
</div>

{{-- ── Modal création bannière ────────────────────────────────────── --}}
<div id="create-banner-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeCreateBannerModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Ajouter une bannière</h2>
                <button type="button" onclick="closeCreateBannerModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.prestations.banners.store') }}" enctype="multipart/form-data" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @include('admin.prestations.partials.banner-form-fields')
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeCreateBannerModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal édition bannière ─────────────────────────────────────── --}}
<div id="edit-banner-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeEditBannerModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Modifier la bannière</h2>
                <button type="button" onclick="closeEditBannerModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" id="edit-banner-form" action="" enctype="multipart/form-data" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @method('PATCH')
                @include('admin.prestations.partials.banner-form-fields', ['isEdit' => true])
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditBannerModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════ PARAMÈTRES : Vidéo + Catalogue ═══════════════ --}}
<form method="POST" action="{{ route('admin.prestations.settings.update') }}" enctype="multipart/form-data" class="space-y-5 mb-8">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 gap-5">

        {{-- ① Vidéo de présentation --}}
        <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
            <h2 class="text-white font-semibold mb-5 flex items-center gap-2">
                <i class="fas fa-circle-play text-orange-400"></i> Vidéo de présentation
            </h2>

            <div class="flex items-center gap-1 bg-slate-800 rounded-lg p-0.5 mb-4 w-fit">
                <label class="px-3 py-1.5 rounded-md text-xs font-medium cursor-pointer transition {{ old('video_source', $settings->video_source) === 'upload' ? 'bg-slate-700 text-white' : 'text-slate-500' }}">
                    <input type="radio" name="video_source" value="upload" class="hidden" onchange="setVideoMode('upload')" {{ old('video_source', $settings->video_source) === 'upload' ? 'checked' : '' }}>
                    <i class="fas fa-upload mr-1"></i>Fichier
                </label>
                <label class="px-3 py-1.5 rounded-md text-xs font-medium cursor-pointer transition {{ old('video_source', $settings->video_source) === 'embed' ? 'bg-slate-700 text-white' : 'text-slate-500' }}">
                    <input type="radio" name="video_source" value="embed" class="hidden" onchange="setVideoMode('embed')" {{ old('video_source', $settings->video_source) === 'embed' ? 'checked' : '' }}>
                    <i class="fas fa-link mr-1"></i>Lien (YouTube/Vimeo)
                </label>
            </div>

            <div id="video_upload_wrap" class="{{ old('video_source', $settings->video_source) === 'embed' ? 'hidden' : '' }}">
                <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-4 cursor-pointer transition group mb-2">
                    <i class="fas fa-cloud-arrow-up text-xl text-slate-600 group-hover:text-orange-400/70 mb-1.5 transition"></i>
                    <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez (MP4, WebM — 100 Mo max)</span>
                    <input type="file" name="video_file" accept="video/mp4,video/webm" class="hidden" onchange="previewVideoFile(this)">
                </label>
                @if(!empty($settings->video_url))
                    <p class="text-slate-500 text-xs mb-3"><i class="fas fa-circle-check text-emerald-400 mr-1"></i>Vidéo actuelle : {{ basename($settings->video_url) }}</p>
                    <video src="{{ $settings->video_url }}" controls class="w-full rounded-lg border border-slate-700 mb-3 max-h-40"></video>
                @endif

                <label class="block text-xs text-slate-400 mb-1">Image d'aperçu (poster, optionnel)</label>
                <input type="file" name="video_poster_file" accept="image/jpeg,image/png,image/webp"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 outline-none file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-slate-200 file:text-xs">
            </div>

            <div id="video_embed_wrap" class="{{ old('video_source', $settings->video_source) !== 'embed' ? 'hidden' : '' }}">
                <label class="block text-xs text-slate-400 mb-1">URL de la vidéo (YouTube, Vimeo…)</label>
                <input type="url" name="video_embed_url" maxlength="500" value="{{ old('video_embed_url', $settings->video_embed_url) }}"
                       placeholder="https://www.youtube.com/watch?v=…"
                       class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
                @if($settings->video_source === 'embed' && $settings->video_embed_src)
                    <p class="text-slate-500 text-xs mt-3 mb-2"><i class="fas fa-circle-check text-emerald-400 mr-1"></i>Aperçu du lien actuellement enregistré :</p>
                    <div class="aspect-video rounded-lg overflow-hidden border border-slate-700 bg-black">
                        <iframe src="{{ $settings->video_embed_src }}" class="w-full h-full" title="Aperçu vidéo" allowfullscreen></iframe>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ② Catalogue interactif (feuilletage PDF) --}}
    <div class="bg-green-900 border border-slate-800 rounded-xl p-6">
        <h2 class="text-white font-semibold mb-1 flex items-center gap-2">
            <i class="fas fa-book-open text-orange-400"></i> Catalogue interactif (feuilletage PDF)
        </h2>
        <p class="text-slate-500 text-xs mb-5">Le document PDF sera présenté en feuilletable (effet « page qui tourne ») sur la page publique.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Titre du catalogue</label>
                <input type="text" name="catalog_title" maxlength="255" value="{{ old('catalog_title', $settings->catalog_title) }}"
                       placeholder="Notre catalogue de prestations"
                       class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition mb-4">

                <label class="flex items-center gap-2.5 cursor-pointer group">
                    <input type="hidden" name="catalog_enabled" value="0">
                    <input type="checkbox" name="catalog_enabled" value="1"
                           class="rounded border-slate-600 bg-slate-800 text-orange-500"
                           {{ old('catalog_enabled', $settings->catalog_enabled) ? 'checked' : '' }}>
                    <span class="text-sm text-slate-300 group-hover:text-white transition">
                        Afficher le catalogue sur la page publique
                    </span>
                </label>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-2">Document PDF</label>
                <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-4 cursor-pointer transition group">
                    <i class="fas fa-file-pdf text-xl text-slate-600 group-hover:text-orange-400/70 mb-1.5 transition"></i>
                    <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez un PDF (50 Mo max)</span>
                    <input type="file" name="catalog_file" accept="application/pdf" class="hidden" onchange="showFileName(this,'catalog_filename')">
                </label>
                <p id="catalog_filename" class="text-slate-500 text-xs mt-2 {{ empty($settings->catalog_file_url) ? 'hidden' : '' }}">
                    <i class="fas fa-circle-check text-emerald-400 mr-1"></i>
                    <span>{{ $settings->catalog_file_url ? basename($settings->catalog_file_url) : '' }}</span>
                </p>
                @if($settings->catalog_file_url)
                    <a href="{{ $settings->catalog_file_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 mt-2 text-orange-400 hover:text-orange-300 text-xs font-medium transition">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> Ouvrir le PDF actuellement enregistré
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl transition flex items-center gap-2">
            <i class="fas fa-save"></i> Enregistrer les paramètres
        </button>
    </div>
</form>

{{-- ═══════════════ PRESTATIONS (liste répétable) ═══════════════ --}}
<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-green-700 via-green-600 to-green-600">
        <div>
            <h2 class="text-white font-semibold text-lg tracking-tight">Nos différentes prestations</h2>
            <p class="text-green-100/80 text-xs mt-0.5">Titre, description, image ou icône, lien — organisées par ordre d'affichage.</p>
        </div>
        <button type="button" onclick="openCreateItemModal()"
                class="inline-flex items-center justify-center gap-2 shrink-0 bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus"></i> Ajouter une prestation
        </button>
    </div>

    <div class="p-5 space-y-3">
        @forelse($items as $item)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-slate-800 bg-slate-800/20 hover:border-slate-700/80 transition">
            <div class="shrink-0">
                @if($item->image_url)
                    <img src="{{ $item->image_url }}" alt="" class="w-16 h-16 object-cover rounded-lg border border-slate-700">
                @else
                    <div class="w-16 h-16 rounded-lg border border-slate-700 bg-slate-800 flex items-center justify-center">
                        <i class="{{ $item->icon ?: 'fas fa-concierge-bell' }} text-orange-400 text-xl"></i>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold truncate">{{ $item->title }}</p>
                @if($item->description)
                    <p class="text-slate-500 text-xs mt-0.5 line-clamp-1">{{ $item->description }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-600/30 text-slate-300 border border-slate-600/40' }}">
                        {{ $item->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/20 text-slate-300 border border-slate-600/40">
                        Ordre : {{ $item->display_order }}
                    </span>
                    @if($item->link_url)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/40">
                            <i class="fas fa-link text-[10px]"></i> {{ $item->link_label ?: $item->link_url }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end sm:justify-center gap-1 shrink-0 border-t border-slate-800/80 sm:border-0 pt-3 sm:pt-0">
                <button type="button"
                        onclick="openEditItemModal(this)"
                        data-id="{{ $item->id }}"
                        data-title="{{ e($item->title) }}"
                        data-description="{{ e($item->description ?? '') }}"
                        data-icon="{{ e($item->icon ?? '') }}"
                        data-link-url="{{ e($item->link_url ?? '') }}"
                        data-link-label="{{ e($item->link_label ?? '') }}"
                        data-display-order="{{ $item->display_order }}"
                        data-is-active="{{ $item->is_active ? '1' : '0' }}"
                        data-image-url="{{ e($item->image_url ?? '') }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white transition"
                        title="Modifier">
                    <i class="fas fa-pen"></i>
                </button>
                <form method="POST" action="{{ route('admin.prestations.items.toggle', $item) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white transition"
                            title="{{ $item->is_active ? 'Désactiver' : 'Activer' }}">
                        <i class="fas {{ $item->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.prestations.items.destroy', $item) }}" class="inline" onsubmit="return confirm('Supprimer cette prestation ?');">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-red-700/90 hover:bg-red-600 text-white transition"
                            title="Supprimer">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-14 text-slate-500 border border-dashed border-slate-700 rounded-xl">
            <i class="fas fa-concierge-bell text-3xl mb-3 text-slate-600"></i>
            <p>Aucune prestation pour le moment.</p>
            <button type="button" onclick="openCreateItemModal()" class="mt-4 text-orange-400 hover:text-orange-300 text-sm font-medium">Ajouter la première prestation</button>
        </div>
        @endforelse
    </div>

    @if($items->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $items->links() }}
        </div>
    @endif
</div>

{{-- ── Modal création ─────────────────────────────────────────────── --}}
<div id="create-item-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeCreateItemModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Ajouter une prestation</h2>
                <button type="button" onclick="closeCreateItemModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.prestations.items.store') }}" enctype="multipart/form-data" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @include('admin.prestations.partials.item-form-fields')
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeCreateItemModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal édition ──────────────────────────────────────────────── --}}
<div id="edit-item-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-green-950/70" onclick="closeEditItemModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-lg bg-green-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600">
                <h2 class="text-white font-semibold">Modifier la prestation</h2>
                <button type="button" onclick="closeEditItemModal()" class="text-white/90 hover:text-white"><i class="fas fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" id="edit-item-form" action="" enctype="multipart/form-data" class="p-5 grid grid-cols-1 gap-4 overflow-y-auto">
                @csrf
                @method('PATCH')
                @include('admin.prestations.partials.item-form-fields', ['isEdit' => true])
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditItemModal()" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function previewVideoFile(input) {
        if (!input.files[0]) return;
        showFileName(input, null);
    }

    function showFileName(input, previewId) {
        if (!input.files[0]) return;
        const label = input.closest('label');
        const span = label ? label.querySelector('span') : null;
        if (span) span.textContent = input.files[0].name;
    }

    function setVideoMode(mode) {
        document.getElementById('video_upload_wrap').classList.toggle('hidden', mode !== 'upload');
        document.getElementById('video_embed_wrap').classList.toggle('hidden', mode !== 'embed');
        document.querySelectorAll('input[name="video_source"]').forEach((r) => {
            const lbl = r.closest('label');
            if (!lbl) return;
            lbl.classList.toggle('bg-slate-700', r.value === mode);
            lbl.classList.toggle('text-white', r.value === mode);
            lbl.classList.toggle('text-slate-500', r.value !== mode);
        });
    }

    function openCreateBannerModal() { document.getElementById('create-banner-modal').classList.remove('hidden'); }
    function closeCreateBannerModal() { document.getElementById('create-banner-modal').classList.add('hidden'); }
    function closeEditBannerModal() { document.getElementById('edit-banner-modal').classList.add('hidden'); }

    function openEditBannerModal(button) {
        const form = document.getElementById('edit-banner-form');
        const base = "{{ route('admin.prestations.banners.update', ['banner' => '__ID__']) }}";
        form.action = base.replace('__ID__', button.dataset.id);

        document.getElementById('edit_banner_title').value = button.dataset.title || '';
        document.getElementById('edit_banner_content').value = button.dataset.content || '';
        document.getElementById('edit_banner_link_url').value = button.dataset.linkUrl || '';
        document.getElementById('edit_banner_link_label').value = button.dataset.linkLabel || '';
        document.getElementById('edit_banner_display_order').value = button.dataset.displayOrder || '0';
        document.getElementById('edit_banner_is_active').checked = button.dataset.isActive === '1';

        const preview = document.getElementById('edit_banner_preview');
        if (preview) {
            if (button.dataset.imageUrl) {
                preview.src = button.dataset.imageUrl;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        }

        document.getElementById('edit-banner-modal').classList.remove('hidden');
    }

    function openCreateItemModal() { document.getElementById('create-item-modal').classList.remove('hidden'); }
    function closeCreateItemModal() { document.getElementById('create-item-modal').classList.add('hidden'); }
    function closeEditItemModal() { document.getElementById('edit-item-modal').classList.add('hidden'); }

    function openEditItemModal(button) {
        const form = document.getElementById('edit-item-form');
        const base = "{{ route('admin.prestations.items.update', ['item' => '__ID__']) }}";
        form.action = base.replace('__ID__', button.dataset.id);

        document.getElementById('edit_title').value = button.dataset.title || '';
        document.getElementById('edit_description').value = button.dataset.description || '';
        document.getElementById('edit_icon').value = button.dataset.icon || '';
        document.getElementById('edit_link_url').value = button.dataset.linkUrl || '';
        document.getElementById('edit_link_label').value = button.dataset.linkLabel || '';
        document.getElementById('edit_display_order').value = button.dataset.displayOrder || '0';
        document.getElementById('edit_is_active').checked = button.dataset.isActive === '1';

        const preview = document.getElementById('edit_image_preview');
        if (preview) {
            if (button.dataset.imageUrl) {
                preview.src = button.dataset.imageUrl;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        }

        document.getElementById('edit-item-modal').classList.remove('hidden');
    }
</script>
@endpush
