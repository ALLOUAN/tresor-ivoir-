<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $element->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0a0a09; color: #fff; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .hero-img { transition: transform 8s ease; }
        .hero-wrap:hover .hero-img { transform: scale(1.04); }
        .gallery-scroll { display: flex; gap: 10px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; padding-bottom: 4px; }
        .gallery-scroll::-webkit-scrollbar { display: none; }
        .gallery-item { scroll-snap-align: start; flex-shrink: 0; }
        .tabs-bar { position: sticky; top: 0; z-index: 30; background: rgba(10,10,9,.92); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,.06); }
        .tab-btn { position: relative; padding: 14px 20px; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; transition: color .2s; white-space: nowrap; }
        .tab-btn.active { color: #f59e0b; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 2px; background: #f59e0b; border-radius: 2px 2px 0 0; }
        .tab-btn:hover:not(.active) { color: #cbd5e1; }
        .tab-section { display: none; }
        .tab-section.active { display: block; }
        html:not(.dark) body { background: #f5f0eb; color: #1a1a1a; }
        html:not(.dark) .tabs-bar { background: rgba(245,240,235,.95); border-color: rgba(0,0,0,.08); }
        html:not(.dark) .info-block { background: #fff; border-color: rgba(0,0,0,.08); }
    </style>
</head>
<body class="min-h-screen">

@include('partials.public-top-nav')

{{-- ══ HERO ══════════════════════════════════════════════════════════════════ --}}
@php
    $heroMedia = $element->media->where('type', 'photo')->first();
    $riskMap = [
        'stable'     => ['label' => 'Stable',     'bg' => 'rgba(22,163,74,.15)',  'border' => 'rgba(22,163,74,.35)',  'text' => '#4ade80'],
        'vulnerable' => ['label' => 'Vulnérable',  'bg' => 'rgba(217,119,6,.15)', 'border' => 'rgba(217,119,6,.35)', 'text' => '#fb923c'],
        'en_danger'  => ['label' => 'En danger',   'bg' => 'rgba(220,38,38,.15)', 'border' => 'rgba(220,38,38,.35)', 'text' => '#f87171'],
        'disparu'    => ['label' => 'Disparu',      'bg' => 'rgba(113,113,113,.15)','border' => 'rgba(113,113,113,.35)','text' => '#9ca3af'],
    ];
    $rc = $riskMap[$element->niveau_risque] ?? $riskMap['stable'];
@endphp

<section class="hero-wrap relative h-[60vh] md:h-[72vh] overflow-hidden">

    @if($heroMedia)
    <img id="mainPhoto" src="{{ $heroMedia->url }}" alt="{{ $element->name }}"
        class="hero-img w-full h-full object-cover">
    @elseif($element->cover_image)
    <img src="{{ $element->cover_image }}" alt="{{ $element->name }}"
        class="hero-img w-full h-full object-cover">
    @elseif($element->thumbnail)
    <img src="{{ $element->thumbnail }}" alt="{{ $element->name }}"
        class="hero-img w-full h-full object-cover">
    @else
    <div class="w-full h-full flex items-center justify-center"
        style="background: linear-gradient(135deg, #78350f 0%, #1c1917 60%, #0a0a09 100%);">
        <i class="fas fa-masks-theater text-8xl opacity-10"></i>
    </div>
    @endif

    <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(10,10,9,1) 0%, rgba(10,10,9,.5) 40%, rgba(10,10,9,.1) 100%);"></div>
    <div class="absolute inset-0" style="background: linear-gradient(to right, rgba(10,10,9,.5) 0%, transparent 60%);"></div>

    {{-- Breadcrumb --}}
    <div class="absolute top-6 left-0 right-0 max-w-7xl mx-auto px-6">
        <nav class="flex items-center gap-1.5 text-xs text-white/50">
            <a href="{{ route('cultural.peoples') }}" class="hover:text-amber-400 transition">Cultures</a>
            @if($peoples->isNotEmpty())
            <i class="fas fa-chevron-right text-[8px]"></i>
            <a href="{{ route('cultural.people', $peoples->first()->slug) }}" class="hover:text-amber-400 transition">{{ $peoples->first()->name }}</a>
            @endif
            @if($element->domain)
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-white/60 truncate max-w-[140px]">{{ $element->domain->name }}</span>
            @endif
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-white/80 truncate max-w-[200px]">{{ $element->name }}</span>
        </nav>
    </div>

    {{-- Contenu bas --}}
    <div class="absolute bottom-0 left-0 right-0 max-w-7xl mx-auto px-6 pb-10">
        {{-- Badges --}}
        <div class="flex flex-wrap gap-2 mb-4">
            @if($element->is_featured)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500 text-black text-xs font-bold">
                <i class="fas fa-star text-[10px]"></i> À la une
            </span>
            @endif
            @if($element->domain)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border text-white text-xs"
                style="{{ $element->domain->color ? 'border-color:'.$element->domain->color.'50' : 'border-color:rgba(255,255,255,.15)' }}">
                @if($element->domain->icon)
                <i class="{{ $element->domain->icon }} text-[10px]"
                    style="{{ $element->domain->color ? 'color:'.$element->domain->color : '' }}"></i>
                @endif
                {{ $element->domain->name }}
            </span>
            @endif
            @if($element->niveau_risque && $element->niveau_risque !== 'stable')
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-medium"
                style="background:{{ $rc['bg'] }}; border-color:{{ $rc['border'] }}; color:{{ $rc['text'] }}">
                <i class="fas fa-triangle-exclamation text-[10px]"></i> {{ $rc['label'] }}
            </span>
            @endif
            @if($element->unesco_status)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/15 border border-blue-500/30 text-blue-300 text-xs">
                <i class="fas fa-earth-africa text-[10px]"></i> UNESCO
            </span>
            @endif
        </div>

        <h1 class="font-serif text-4xl md:text-5xl font-bold text-white leading-tight mb-4 drop-shadow-xl" style="max-width: 700px;">
            {{ $element->name }}
        </h1>

        <div class="flex flex-wrap items-center gap-4 text-sm">
            @if($peoples->isNotEmpty())
            <span class="flex items-center gap-1.5 text-amber-300 font-semibold">
                <i class="fas fa-users text-xs"></i>
                {{ $peoples->pluck('name')->implode(', ') }}
            </span>
            @endif
            @if($cities->isNotEmpty())
            <span class="flex items-center gap-1.5 text-white/60">
                <i class="fas fa-map-marker-alt text-amber-400/60 text-xs"></i>
                {{ $cities->pluck('name')->implode(', ') }}
            </span>
            @endif
            <span class="flex items-center gap-1.5 text-white/40">
                <i class="fas fa-eye text-xs"></i> {{ number_format($element->views_count) }} vues
            </span>
        </div>
    </div>

    {{-- Miniatures médias flottantes --}}
    @php $photoMedia = $element->media->where('type', 'photo'); @endphp
    @if($photoMedia->count() > 1)
    <div class="absolute bottom-6 right-6 hidden lg:flex gap-2">
        @foreach($photoMedia->take(4) as $m)
        <button onclick="changeHeroPhoto('{{ $m->url }}')"
            class="w-16 h-12 rounded-lg overflow-hidden border-2 transition
                {{ $loop->first ? 'border-amber-500 opacity-100' : 'border-transparent opacity-60 hover:opacity-100' }}"
            data-photo="{{ $m->url }}">
            <img src="{{ $m->url }}" alt="" class="w-full h-full object-cover">
        </button>
        @endforeach
        @if($photoMedia->count() > 4)
        <button onclick="document.querySelectorAll('.tab-btn').forEach(b => { if (b.textContent.includes('Galerie')) b.click(); })"
            class="w-16 h-12 rounded-lg bg-black/60 border-2 border-transparent hover:border-amber-500/50 flex items-center justify-center text-white/70 hover:text-white transition text-xs font-semibold">
            +{{ $photoMedia->count() - 4 }}
        </button>
        @endif
    </div>
    @endif
</section>

{{-- ══ ONGLETS STICKY ═══════════════════════════════════════════════════════ --}}
<div class="tabs-bar">
    <div class="max-w-7xl mx-auto px-6 flex items-center gap-0 overflow-x-auto">
        <button class="tab-btn active" onclick="showTab('description', this)">
            <i class="fas fa-align-left mr-1.5"></i>Description
        </button>
        @if($element->media->where('type', 'photo')->count() > 1 || $element->media->where('type', 'video')->isNotEmpty())
        <button class="tab-btn" onclick="showTab('galerie', this)">
            <i class="fas fa-images mr-1.5"></i>Galerie
        </button>
        @endif
        @if($element->media->where('type', 'audio')->isNotEmpty())
        <button class="tab-btn" onclick="showTab('audio', this)">
            <i class="fas fa-music mr-1.5"></i>Audio
        </button>
        @endif
        @if(!empty($element->practical_info))
        <button class="tab-btn" onclick="showTab('pratique', this)">
            <i class="fas fa-circle-info mr-1.5"></i>Infos pratiques
        </button>
        @endif
    </div>
</div>

{{-- ══ CONTENU PRINCIPAL ════════════════════════════════════════════════════ --}}
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- ── COLONNE PRINCIPALE ─────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- ONGLET : Description --}}
            <div id="tab-description" class="tab-section active space-y-8">

                @if($element->short_description)
                <p class="text-lg text-slate-300 font-light leading-relaxed border-l-2 border-amber-500/60 pl-5 italic">
                    {{ $element->short_description }}
                </p>
                @endif

                @if($element->description)
                <div class="text-slate-300 leading-8 text-base whitespace-pre-line">
                    {{ $element->description }}
                </div>
                @endif

                @if($element->origine_historique)
                <div class="info-block bg-[#111110] border border-slate-800 rounded-2xl p-6">
                    <h3 class="text-amber-400 text-xs font-semibold uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-scroll"></i> Origine historique
                    </h3>
                    <p class="text-slate-300 leading-relaxed text-sm">{{ $element->origine_historique }}</p>
                </div>
                @endif

                {{-- Peuples associés --}}
                @if($peoples->isNotEmpty())
                <div>
                    <h2 class="text-white font-serif text-xl font-bold mb-4">Peuples pratiquants</h2>
                    <div class="flex flex-wrap gap-3">
                        @foreach($peoples as $p)
                        <a href="{{ route('cultural.people', $p->slug) }}"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-[#111110] border border-slate-800 hover:border-amber-500/40 transition group">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-700 shrink-0">
                                @if($p->thumbnail)
                                <img src="{{ $p->thumbnail }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-user text-slate-500 text-xs"></i>
                                </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-white text-sm font-medium group-hover:text-amber-400 transition">{{ $p->name }}</p>
                                @php $role = $element->getRoleForPeople($p->id); @endphp
                                @if($role)
                                <p class="text-slate-600 text-xs">{{ $role }}</p>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Villes associées --}}
                @if($cities->isNotEmpty())
                <div>
                    <h2 class="text-white font-serif text-xl font-bold mb-4">Zones de pratique</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($cities as $city)
                        <a href="{{ route('tourist.city', $city->slug) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#111110] border border-slate-800 hover:border-amber-500/30 text-slate-300 hover:text-white text-sm transition">
                            <i class="fas fa-map-marker-alt text-amber-400/60 text-xs"></i>
                            {{ $city->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Meilleure période --}}
                @if(!empty($element->meilleure_periode))
                <div class="info-block bg-[#111110] border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-amber-400/80 text-xs font-semibold uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-calendar-days"></i> Meilleure période
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($element->meilleure_periode as $periode)
                        <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-300 text-sm">
                            {{ $periode }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            {{-- ONGLET : Galerie --}}
            <div id="tab-galerie" class="tab-section space-y-6">

                @php $photos = $element->media->where('type', 'photo'); @endphp
                @if($photos->count() > 1)
                <div>
                    <h2 class="text-white font-serif text-xl font-bold mb-4">
                        Photos <span class="text-slate-600 font-sans font-normal text-sm">({{ $photos->count() }})</span>
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($photos as $photo)
                        <div class="rounded-2xl overflow-hidden cursor-pointer group"
                            style="{{ $loop->first ? 'grid-column: span 2; aspect-ratio: 16/9;' : 'aspect-ratio: 4/3;' }}"
                            onclick="openLightbox('{{ $photo->url }}', '{{ addslashes($photo->caption ?? $element->name) }}')">
                            <img src="{{ $photo->url }}" alt="{{ $photo->alt_text ?: $element->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @php $videos = $element->media->where('type', 'video'); @endphp
                @if($videos->isNotEmpty())
                <div>
                    <h2 class="text-white font-serif text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-play-circle text-amber-400"></i> Vidéos
                    </h2>
                    @foreach($videos as $video)
                    <div class="rounded-2xl overflow-hidden bg-slate-900 border border-slate-800 aspect-video mb-3">
                        <iframe src="{{ $video->url }}" class="w-full h-full"
                            frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                    @if($video->caption)
                    <p class="text-slate-500 text-xs">{{ $video->caption }}</p>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ONGLET : Audio --}}
            <div id="tab-audio" class="tab-section space-y-4">
                <h2 class="text-white font-serif text-xl font-bold mb-4">
                    <i class="fas fa-music text-amber-400 mr-2"></i>Enregistrements audio
                </h2>
                @php $audios = $element->media->where('type', 'audio'); @endphp
                @foreach($audios as $audio)
                <div class="info-block bg-[#111110] border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0">
                            <i class="fas fa-music text-amber-400 text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            @if($audio->caption)
                            <p class="text-white font-medium text-sm">{{ $audio->caption }}</p>
                            @endif
                            @if($audio->duree_secondes)
                            <p class="text-slate-500 text-xs">{{ gmdate('i:s', $audio->duree_secondes) }}</p>
                            @endif
                        </div>
                    </div>
                    <audio controls class="w-full" style="filter: sepia(1) hue-rotate(20deg) brightness(.9);">
                        <source src="{{ $audio->url }}">
                    </audio>
                </div>
                @endforeach
            </div>

            {{-- ONGLET : Infos pratiques --}}
            <div id="tab-pratique" class="tab-section space-y-6">
                @if(!empty($element->practical_info))
                <h2 class="text-white font-serif text-xl font-bold mb-4">
                    <i class="fas fa-circle-info text-amber-400 mr-2"></i>Informations pratiques
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($element->practical_info as $info)
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-xl p-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="{{ $info['icon'] ?? 'fas fa-circle-dot' }} text-amber-400 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-slate-500 text-xs mb-0.5">{{ $info['label'] ?? '' }}</p>
                            <p class="text-white text-sm font-medium">{{ $info['value'] ?? '' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        {{-- ── PANNEAU STICKY DROITE ──────────────────────────────────────── --}}
        <div class="lg:sticky lg:top-16">
            <div class="bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden">

                {{-- En-tête panneau --}}
                <div class="relative h-44 overflow-hidden">
                    @if($heroMedia)
                    <img src="{{ $heroMedia->url }}" alt="" class="w-full h-full object-cover">
                    @elseif($element->thumbnail)
                    <img src="{{ $element->thumbnail }}" alt="" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full" style="background: linear-gradient(135deg, #78350f 0%, #1c1917 100%);"></div>
                    @endif
                    <div class="absolute inset-0" style="background: linear-gradient(to top, #111110, transparent);"></div>
                    @if($element->domain)
                    <div class="absolute bottom-3 left-4">
                        <p class="text-xs" style="{{ $element->domain->color ? 'color:'.$element->domain->color : 'color:#94a3b8' }}">
                            @if($element->domain->icon)<i class="{{ $element->domain->icon }} text-[10px] mr-1"></i>@endif
                            {{ $element->domain->name }}
                        </p>
                    </div>
                    @endif
                </div>

                <div class="p-5 space-y-4">

                    {{-- Statut UNESCO --}}
                    @if($element->unesco_status)
                    <div class="flex items-center gap-2 py-2.5 px-4 rounded-xl bg-blue-500/10 border border-blue-500/25">
                        <i class="fas fa-earth-africa text-blue-400 text-sm"></i>
                        <div>
                            <p class="text-blue-400/70 text-[10px] uppercase tracking-wide">Statut UNESCO</p>
                            <p class="text-blue-300 text-sm font-medium">{{ $element->unesco_status }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Niveau de risque --}}
                    @if($element->niveau_risque)
                    <div class="flex items-center gap-2 py-2.5 px-4 rounded-xl border"
                        style="background:{{ $rc['bg'] }}; border-color:{{ $rc['border'] }}">
                        <i class="fas fa-shield-halved text-sm" style="color:{{ $rc['text'] }}"></i>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide" style="color:{{ $rc['text'] }}; opacity:.7">Niveau de préservation</p>
                            <p class="text-sm font-semibold" style="color:{{ $rc['text'] }}">{{ $rc['label'] }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Website --}}
                    @if($element->website)
                    <a href="{{ $element->website }}" target="_blank" rel="noopener"
                        class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-amber-500/15 border border-slate-700 hover:border-amber-500/30 transition group">
                        <i class="fas fa-globe text-amber-400 text-sm w-4 text-center"></i>
                        <span class="text-slate-300 text-sm truncate group-hover:text-white transition">Site de référence</span>
                        <i class="fas fa-arrow-up-right-from-square text-slate-600 text-xs ml-auto group-hover:text-amber-400 transition"></i>
                    </a>
                    @endif

                    {{-- Peuples liens rapides --}}
                    @if($peoples->isNotEmpty())
                    <div>
                        <p class="text-slate-600 text-[10px] uppercase tracking-widest mb-2">Peuples associés</p>
                        <div class="space-y-1.5">
                            @foreach($peoples as $p)
                            <a href="{{ route('cultural.people', $p->slug) }}"
                                class="flex items-center gap-2 w-full px-3 py-2 rounded-lg bg-slate-800/50 hover:bg-slate-800 border border-slate-800 hover:border-amber-500/20 transition group text-sm">
                                <i class="fas fa-users text-amber-400/60 text-xs w-3 text-center shrink-0"></i>
                                <span class="text-slate-300 group-hover:text-white transition truncate">{{ $p->name }}</span>
                                <i class="fas fa-chevron-right text-slate-700 group-hover:text-amber-400/60 text-[10px] ml-auto transition"></i>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Retour --}}
                    <a href="{{ route('cultural.peoples') }}"
                        class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-slate-800 hover:bg-amber-500/15 border border-slate-700 hover:border-amber-500/30 text-slate-300 hover:text-white text-sm font-medium transition">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Toutes les cultures
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══ ÉLÉMENTS SIMILAIRES ══════════════════════════════════════════════════ --}}
@if($related->isNotEmpty())
<section class="max-w-7xl mx-auto px-6 pb-20 mt-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-white font-serif text-2xl font-bold">Dans le même domaine</h2>
        <a href="{{ route('cultural.peoples') }}"
            class="text-amber-400 hover:text-amber-300 text-sm flex items-center gap-1.5 transition">
            Explorer <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($related as $r)
        @php $rMedia = $r->media->where('type', 'photo')->first(); @endphp
        <a href="{{ route('cultural.element', $r->slug) }}"
            class="group block bg-[#111110] border border-slate-800 hover:border-amber-500/30 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1">
            <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                @if($rMedia)
                <img src="{{ $rMedia->url }}" alt="{{ $r->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @elseif($r->thumbnail)
                <img src="{{ $r->thumbnail }}" alt="{{ $r->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center bg-slate-800">
                    <i class="fas fa-masks-theater text-2xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(10,10,9,.75), transparent);"></div>
            </div>
            <div class="p-4">
                <h3 class="text-white text-sm font-semibold group-hover:text-amber-400 transition line-clamp-2 mb-1">
                    {{ $r->name }}
                </h3>
                @if($r->domain)
                <p class="text-xs" style="{{ $r->domain->color ? 'color:'.$r->domain->color : 'color:#64748b' }}">
                    @if($r->domain->icon)<i class="{{ $r->domain->icon }} text-[10px] mr-0.5"></i>@endif
                    {{ $r->domain->name }}
                </p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- ══ LIGHTBOX ═════════════════════════════════════════════════════════════ --}}
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/95 items-center justify-center p-4" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
        <i class="fas fa-xmark"></i>
    </button>
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    <p id="lightbox-caption" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/60 text-sm"></p>
</div>

@include('partials.homepage-footer')

<script>
function showTab(name, btn) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    const section = document.getElementById('tab-' + name);
    if (section) section.classList.add('active');
    btn.classList.add('active');
    window.scrollTo({ top: document.querySelector('.tabs-bar').offsetTop - 10, behavior: 'smooth' });
}
function changeHeroPhoto(url) {
    const img = document.getElementById('mainPhoto');
    if (img) img.src = url;
    document.querySelectorAll('[data-photo]').forEach(btn => {
        const active = btn.dataset.photo === url;
        btn.classList.toggle('border-amber-500', active);
        btn.classList.toggle('opacity-100', active);
        btn.classList.toggle('border-transparent', !active);
        btn.classList.toggle('opacity-60', !active);
    });
}
function openLightbox(url, caption) {
    document.getElementById('lightbox-img').src = url;
    document.getElementById('lightbox-caption').textContent = caption || '';
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
</body>
</html>
