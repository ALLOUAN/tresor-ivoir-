<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $site->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0a0a09; color: #fff; }
        .font-serif { font-family: 'Playfair Display', serif; }

        /* Hero */
        .hero-img { transition: transform 8s ease; }
        .hero-wrap:hover .hero-img { transform: scale(1.04); }

        /* Galerie scroll */
        .gallery-scroll { display: flex; gap: 10px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; padding-bottom: 4px; }
        .gallery-scroll::-webkit-scrollbar { display: none; }
        .gallery-item { scroll-snap-align: start; flex-shrink: 0; }

        /* Onglets sticky */
        .tabs-bar { position: sticky; top: 0; z-index: 30; background: rgba(10,10,9,.92); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,.06); }
        .tab-btn { position: relative; padding: 14px 20px; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; transition: color .2s; white-space: nowrap; }
        .tab-btn.active { color: #f59e0b; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 2px; background: #f59e0b; border-radius: 2px 2px 0 0; }
        .tab-btn:hover:not(.active) { color: #cbd5e1; }

        /* Sections */
        .tab-section { display: none; }
        .tab-section.active { display: block; }

        /* Panneau sticky */
        .sticky-panel { position: sticky; top: 64px; }

        /* Horaire badge */
        .schedule-open { background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
        .schedule-closed { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25); color: #f87171; }

        /* Light mode */
        html:not(.dark) body { background: #f5f0eb; color: #1a1a1a; }
        html:not(.dark) .tabs-bar { background: rgba(245,240,235,.95); border-color: rgba(0,0,0,.08); }
        html:not(.dark) .info-block { background: #fff; border-color: rgba(0,0,0,.08); }
        html:not(.dark) .sticky-panel-inner { background: #fff; border-color: rgba(0,0,0,.08); }
    </style>
</head>
<body class="min-h-screen">

@include('partials.public-top-nav')

{{-- ══ HERO ══════════════════════════════════════════════════════════════════ --}}
@php $heroPhoto = $site->photos->first(); @endphp
<section class="hero-wrap relative h-[60vh] md:h-[75vh] overflow-hidden">

    {{-- Image de fond --}}
    @if($heroPhoto)
    <img id="mainPhoto" src="{{ $heroPhoto->url }}" alt="{{ $site->name }}"
        class="hero-img w-full h-full object-cover">
    @elseif($site->thumbnail)
    <img src="{{ $site->thumbnail }}" alt="{{ $site->name }}"
        class="hero-img w-full h-full object-cover">
    @else
    <div class="w-full h-full flex items-center justify-center"
        style="background: linear-gradient(135deg, #78350f 0%, #1c1917 60%, #0a0a09 100%);">
        <i class="fas fa-image text-8xl opacity-10"></i>
    </div>
    @endif

    {{-- Overlays --}}
    <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(10,10,9,1) 0%, rgba(10,10,9,.5) 40%, rgba(10,10,9,.1) 100%);"></div>
    <div class="absolute inset-0" style="background: linear-gradient(to right, rgba(10,10,9,.5) 0%, transparent 60%);"></div>

    {{-- Breadcrumb en haut --}}
    <div class="absolute top-6 left-0 right-0 max-w-7xl mx-auto px-6">
        <nav class="flex items-center gap-1.5 text-xs text-white/50">
            <a href="{{ route('tourist.cities') }}" class="hover:text-amber-400 transition">Régions</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <a href="{{ route('tourist.city', $site->city->slug) }}" class="hover:text-amber-400 transition">{{ $site->city->name }}</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <a href="{{ route('tourist.category', [$site->city->slug, $site->category->slug]) }}" class="hover:text-amber-400 transition">{{ $site->category->name }}</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-white/80 truncate max-w-[200px]">{{ $site->name }}</span>
        </nav>
    </div>

    {{-- Contenu bas du hero --}}
    <div class="absolute bottom-0 left-0 right-0 max-w-7xl mx-auto px-6 pb-10">

        {{-- Badges --}}
        <div class="flex flex-wrap gap-2 mb-4">
            @if($site->is_featured)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500 text-black text-xs font-bold">
                <i class="fas fa-star text-[10px]"></i> Site vedette
            </span>
            @endif
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 text-white text-xs"
                style="{{ $site->category->color ? 'border-color:'.$site->category->color.'50' : '' }}">
                <i class="{{ $site->category->icon ?: 'fas fa-tag' }} text-[10px]"
                    style="{{ $site->category->color ? 'color:'.$site->category->color : '' }}"></i>
                {{ $site->category->name }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-white/70 text-xs">
                <i class="fas fa-city text-amber-400/70 text-[10px]"></i>
                {{ $site->city->name }}
            </span>
        </div>

        {{-- Titre --}}
        <h1 class="font-serif text-4xl md:text-6xl font-bold text-white leading-tight mb-4 drop-shadow-xl" style="max-width: 700px;">
            {{ $site->name }}
        </h1>

        {{-- Stats rapides --}}
        <div class="flex flex-wrap items-center gap-4 text-sm">
            @if($site->entrance_fee)
            <span class="flex items-center gap-1.5 text-amber-300 font-semibold">
                <i class="fas fa-ticket text-xs"></i> {{ $site->entrance_fee }}
            </span>
            @endif
            @if($site->localite || $site->departement)
            <span class="flex items-center gap-1.5 text-white/60">
                <i class="fas fa-map-marker-alt text-amber-400/60 text-xs"></i>
                {{ $site->localite ?? $site->departement }}
            </span>
            @endif
            @if($site->distance_centre_km)
            <span class="flex items-center gap-1.5 text-white/60">
                <i class="fas fa-route text-xs"></i> {{ $site->distance_centre_km }} km du centre
            </span>
            @endif
            <span class="flex items-center gap-1.5 text-white/40">
                <i class="fas fa-eye text-xs"></i> {{ number_format($site->views_count) }} vues
            </span>
        </div>
    </div>

    {{-- Galerie miniatures flottantes --}}
    @if($site->photos->count() > 1)
    <div class="absolute bottom-6 right-6 hidden lg:flex gap-2">
        @foreach($site->photos->take(4) as $photo)
        <button onclick="changeHeroPhoto('{{ $photo->url }}')"
            class="w-16 h-12 rounded-lg overflow-hidden border-2 transition
                {{ $loop->first ? 'border-amber-500 opacity-100' : 'border-transparent opacity-60 hover:opacity-100' }}"
            data-photo="{{ $photo->url }}">
            <img src="{{ $photo->url }}" alt="" class="w-full h-full object-cover">
        </button>
        @endforeach
        @if($site->photos->count() > 4)
        <button onclick="scrollToGallery()"
            class="w-16 h-12 rounded-lg bg-black/60 border-2 border-transparent hover:border-amber-500/50 flex items-center justify-center text-white/70 hover:text-white transition text-xs font-semibold">
            +{{ $site->photos->count() - 4 }}
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
        @if($site->photos->count() > 1 || $site->videos->isNotEmpty())
        <button class="tab-btn" onclick="showTab('galerie', this)">
            <i class="fas fa-images mr-1.5"></i>Galerie
        </button>
        @endif
        <button class="tab-btn" onclick="showTab('localisation', this)">
            <i class="fas fa-map-location-dot mr-1.5"></i>Localisation
        </button>
        @if(!empty($site->practical_info) || !empty($site->schedules))
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

                @if($site->short_description)
                <p class="text-lg text-slate-300 font-light leading-relaxed border-l-2 border-amber-500/60 pl-5 italic">
                    {{ $site->short_description }}
                </p>
                @endif

                @if($site->description)
                <div class="text-slate-300 leading-8 text-base whitespace-pre-line space-y-4">
                    {{ $site->description }}
                </div>
                @endif

                {{-- Données clés --}}
                @php
                $keyFacts = array_filter([
                    $site->superficie_ha ? ['fas fa-expand-arrows-alt', 'Superficie', number_format($site->superficie_ha, 2).' ha'] : null,
                    $site->altitude_m    ? ['fas fa-mountain',           'Altitude',   $site->altitude_m.' m'] : null,
                    $site->distance_centre_km ? ['fas fa-route', 'Distance centre', $site->distance_centre_km.' km'] : null,
                    $site->entrance_fee  ? ['fas fa-ticket',             'Entrée',     $site->entrance_fee] : null,
                ]);
                @endphp
                @if(!empty($keyFacts))
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($keyFacts as [$icon, $label, $val])
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-2xl p-4 text-center">
                        <i class="{{ $icon }} text-amber-400 text-lg mb-2 block"></i>
                        <p class="text-white font-semibold text-sm">{{ $val }}</p>
                        <p class="text-slate-600 text-xs mt-0.5">{{ $label }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ONGLET : Galerie --}}
            <div id="tab-galerie" class="tab-section" id="section-galerie">
                @if($site->photos->count() > 1)
                <div class="space-y-3">
                    <h2 class="text-white font-serif text-xl font-bold">
                        Photos <span class="text-slate-600 font-sans font-normal text-sm">({{ $site->photos->count() }})</span>
                    </h2>
                    {{-- Grid masonry-like --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($site->photos as $photo)
                        <div class="rounded-2xl overflow-hidden cursor-pointer group"
                            style="{{ $loop->first ? 'grid-column: span 2; aspect-ratio: 16/9;' : 'aspect-ratio: 4/3;' }}"
                            onclick="openLightbox('{{ $photo->url }}', '{{ addslashes($photo->caption ?? $site->name) }}')">
                            <img src="{{ $photo->url }}"
                                alt="{{ $photo->alt_text ?: $site->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($site->videos->isNotEmpty())
                <div class="space-y-3 mt-6">
                    <h2 class="text-white font-serif text-xl font-bold flex items-center gap-2">
                        <i class="fas fa-play-circle text-amber-400"></i> Vidéos
                    </h2>
                    @foreach($site->videos as $video)
                    <div class="rounded-2xl overflow-hidden bg-slate-900 border border-slate-800 aspect-video">
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

            {{-- ONGLET : Localisation --}}
            <div id="tab-localisation" class="tab-section space-y-5">
                <h2 class="text-white font-serif text-xl font-bold">Situation géographique</h2>

                {{-- Carte --}}
                @if($site->map_embed_url)
                <div class="rounded-2xl overflow-hidden border border-slate-800" style="aspect-ratio: 16/7;">
                    <iframe src="{{ $site->map_embed_url }}" class="w-full h-full"
                        frameborder="0" allowfullscreen loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                @elseif($site->latitude && $site->longitude)
                <a href="https://maps.google.com/?q={{ $site->latitude }},{{ $site->longitude }}"
                    target="_blank"
                    class="info-block flex items-center gap-4 bg-[#111110] border border-slate-800 hover:border-amber-500/40 rounded-2xl p-6 transition group">
                    <div class="w-14 h-14 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0 group-hover:bg-amber-500/25 transition">
                        <i class="fas fa-map-location-dot text-2xl text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-white font-semibold group-hover:text-amber-400 transition">Voir sur Google Maps</p>
                        <p class="text-slate-500 text-sm font-mono mt-0.5">{{ $site->latitude }}, {{ $site->longitude }}</p>
                    </div>
                    <i class="fas fa-arrow-up-right-from-square text-slate-700 group-hover:text-amber-400 transition ml-auto"></i>
                </a>
                @endif

                {{-- Détails géographiques en grille --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if($site->departement)
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-xl p-4">
                        <p class="text-slate-600 text-xs uppercase tracking-widest mb-1">Département</p>
                        <p class="text-white font-medium">{{ $site->departement }}</p>
                    </div>
                    @endif
                    @if($site->sous_prefecture)
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-xl p-4">
                        <p class="text-slate-600 text-xs uppercase tracking-widest mb-1">Sous-préfecture</p>
                        <p class="text-white font-medium">{{ $site->sous_prefecture }}</p>
                    </div>
                    @endif
                    @if($site->localite)
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-xl p-4">
                        <p class="text-slate-600 text-xs uppercase tracking-widest mb-1">Localité</p>
                        <p class="text-white font-medium">{{ $site->localite }}</p>
                    </div>
                    @endif
                    @if($site->latitude && $site->longitude)
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-xl p-4">
                        <p class="text-slate-600 text-xs uppercase tracking-widest mb-1">Coordonnées GPS</p>
                        <p class="text-white font-mono text-sm">{{ $site->latitude }}, {{ $site->longitude }}</p>
                    </div>
                    @endif
                </div>

                @if($site->point_repere || $site->acces_description)
                <div class="info-block bg-[#111110] border border-slate-800 rounded-2xl p-5 space-y-4">
                    @if($site->point_repere)
                    <div>
                        <p class="text-amber-400/80 text-xs font-semibold uppercase tracking-widest mb-2">
                            <i class="fas fa-crosshairs mr-1.5"></i>Point de repère
                        </p>
                        <p class="text-slate-300 text-sm leading-relaxed">{{ $site->point_repere }}</p>
                    </div>
                    @endif
                    @if($site->acces_description)
                    @if($site->point_repere)<div class="border-t border-slate-800"></div>@endif
                    <div>
                        <p class="text-amber-400/80 text-xs font-semibold uppercase tracking-widest mb-2">
                            <i class="fas fa-route mr-1.5"></i>Comment s'y rendre
                        </p>
                        <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ $site->acces_description }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- ONGLET : Infos pratiques --}}
            <div id="tab-pratique" class="tab-section space-y-6">

                {{-- Horaires --}}
                @if(!empty($site->schedules))
                <div>
                    <h2 class="text-white font-serif text-xl font-bold mb-4">
                        <i class="fas fa-clock text-amber-400 mr-2"></i>Horaires d'ouverture
                    </h2>
                    <div class="info-block bg-[#111110] border border-slate-800 rounded-2xl divide-y divide-slate-800 overflow-hidden">
                        @foreach($site->schedules as $s)
                        <div class="flex items-center justify-between px-5 py-3">
                            <span class="text-slate-300 text-sm font-medium">{{ $s['day'] }}</span>
                            @if($s['closed'] ?? false)
                            <span class="px-3 py-1 rounded-full text-xs font-medium schedule-closed">Fermé</span>
                            @else
                            <span class="px-3 py-1 rounded-full text-xs font-medium schedule-open font-mono">
                                {{ $s['opens'] ?? '—' }} – {{ $s['closes'] ?? '—' }}
                            </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Infos pratiques --}}
                @if(!empty($site->practical_info))
                <div>
                    <h2 class="text-white font-serif text-xl font-bold mb-4">
                        <i class="fas fa-circle-info text-amber-400 mr-2"></i>Informations pratiques
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($site->practical_info as $info)
                        <div class="info-block bg-[#111110] border border-slate-800 rounded-xl p-4 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/15 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="{{ $info['icon'] ?? 'fas fa-circle-dot' }} text-amber-400 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-slate-500 text-xs mb-0.5">{{ $info['label'] }}</p>
                                <p class="text-white text-sm font-medium">{{ $info['value'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

        </div>

        {{-- ── PANNEAU STICKY DROITE ──────────────────────────────────────── --}}
        <div class="sticky-panel">
            <div class="sticky-panel-inner bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden">

                {{-- En-tête du panneau --}}
                <div class="relative h-40 overflow-hidden">
                    @if($heroPhoto)
                    <img src="{{ $heroPhoto->url }}" alt="" class="w-full h-full object-cover">
                    @elseif($site->thumbnail)
                    <img src="{{ $site->thumbnail }}" alt="" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full bg-linear-to-br from-amber-900/40 to-slate-900"></div>
                    @endif
                    <div class="absolute inset-0 bg-linear-to-t from-[#111110] to-transparent"></div>
                    <div class="absolute bottom-3 left-4">
                        <p class="text-slate-400 text-xs">{{ $site->city->name }} · {{ $site->category->name }}</p>
                    </div>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Tarif en vedette --}}
                    @if($site->entrance_fee)
                    <div class="flex items-center justify-between py-2.5 px-4 rounded-xl bg-amber-500/10 border border-amber-500/25">
                        <span class="text-amber-300/80 text-xs font-medium flex items-center gap-1.5">
                            <i class="fas fa-ticket text-[10px]"></i> Entrée
                        </span>
                        <span class="text-amber-300 font-bold text-sm">{{ $site->entrance_fee }}</span>
                    </div>
                    @endif

                    {{-- Contacts --}}
                    @if($site->phone || $site->email || $site->website)
                    <div class="space-y-2">
                        @if($site->website)
                        <a href="{{ $site->website }}" target="_blank" rel="noopener"
                            class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-amber-500/15 border border-slate-700 hover:border-amber-500/30 transition group">
                            <i class="fas fa-globe text-amber-400 text-sm w-4 text-center"></i>
                            <span class="text-slate-300 text-sm truncate group-hover:text-white transition">Site officiel</span>
                            <i class="fas fa-arrow-up-right-from-square text-slate-600 text-xs ml-auto group-hover:text-amber-400 transition"></i>
                        </a>
                        @endif
                        @if($site->phone)
                        <a href="tel:{{ $site->phone }}"
                            class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 transition">
                            <i class="fas fa-phone text-amber-400 text-sm w-4 text-center"></i>
                            <span class="text-slate-300 text-sm">{{ $site->phone }}</span>
                        </a>
                        @endif
                        @if($site->email)
                        <a href="mailto:{{ $site->email }}"
                            class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 transition">
                            <i class="fas fa-envelope text-amber-400 text-sm w-4 text-center"></i>
                            <span class="text-slate-300 text-sm truncate">{{ $site->email }}</span>
                        </a>
                        @endif
                    </div>
                    @endif

                    {{-- Adresse --}}
                    @if($site->localite || $site->departement)
                    <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-800">
                        <i class="fas fa-map-marker-alt text-amber-400/70 text-sm mt-0.5 w-4 text-center shrink-0"></i>
                        <div class="text-sm text-slate-400 leading-relaxed">
                            @if($site->localite)<span class="text-white">{{ $site->localite }}</span>@endif
                            @if($site->departement)<br>{{ $site->departement }}@endif
                            @if($site->city->name)<br>{{ $site->city->name }}@endif
                        </div>
                    </div>
                    @endif

                    {{-- Prochaine ouverture (dernier horaire) --}}
                    @if(!empty($site->schedules))
                    @php
                        $today = now()->locale('fr')->dayName;
                        $todaySchedule = collect($site->schedules)->firstWhere('day', ucfirst($today));
                    @endphp
                    @if($todaySchedule)
                    <div class="flex items-center gap-2 text-xs">
                        @if($todaySchedule['closed'] ?? false)
                        <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                        <span class="text-red-400 font-medium">Fermé aujourd'hui</span>
                        @else
                        <span class="w-2 h-2 rounded-full bg-green-500 shrink-0 animate-pulse"></span>
                        <span class="text-green-400 font-medium">
                            Ouvert · {{ $todaySchedule['opens'] ?? '' }}–{{ $todaySchedule['closes'] ?? '' }}
                        </span>
                        @endif
                    </div>
                    @endif
                    @endif

                    {{-- Bouton Google Maps --}}
                    @if($site->latitude && $site->longitude)
                    <a href="https://maps.google.com/?q={{ $site->latitude }},{{ $site->longitude }}"
                        target="_blank"
                        class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm transition">
                        <i class="fas fa-map-location-dot"></i>
                        Itinéraire Google Maps
                    </a>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══ SITES SIMILAIRES ═════════════════════════════════════════════════════ --}}
@if($related->isNotEmpty())
<section class="max-w-7xl mx-auto px-6 pb-20 mt-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-white font-serif text-2xl font-bold">À voir aussi</h2>
        <a href="{{ route('tourist.category', [$site->city->slug, $site->category->slug]) }}"
            class="text-amber-400 hover:text-amber-300 text-sm flex items-center gap-1.5 transition">
            Voir tout <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($related as $r)
        @php $rPhoto = $r->media->first(); @endphp
        <a href="{{ route('tourist.site', $r->slug) }}"
            class="group block bg-[#111110] border border-slate-800 hover:border-amber-500/30 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1">
            <div class="relative overflow-hidden" style="aspect-ratio: 4/3;">
                @if($rPhoto)
                <img src="{{ $rPhoto->url }}" alt="{{ $r->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @elseif($r->thumbnail)
                <img src="{{ $r->thumbnail }}" alt="{{ $r->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center bg-slate-800">
                    <i class="fas fa-image text-2xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-linear-to-t from-black/70 to-transparent"></div>
                @if($r->entrance_fee)
                <span class="absolute top-2 right-2 px-2 py-0.5 bg-black/70 text-white text-[10px] rounded-full">
                    {{ $r->entrance_fee }}
                </span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="text-white text-sm font-semibold group-hover:text-amber-400 transition line-clamp-2 mb-1">
                    {{ $r->name }}
                </h3>
                @if($r->localite || $r->departement)
                <p class="text-slate-500 text-xs flex items-center gap-1">
                    <i class="fas fa-map-marker-alt text-amber-400/50 text-[10px]"></i>
                    {{ $r->localite ?? $r->departement }}
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
// ── Onglets ───────────────────────────────────────────────────────────────
function showTab(name, btn) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    const section = document.getElementById('tab-' + name);
    if (section) section.classList.add('active');
    btn.classList.add('active');
    window.scrollTo({ top: document.querySelector('.tabs-bar').offsetTop - 10, behavior: 'smooth' });
}

// ── Photo hero ────────────────────────────────────────────────────────────
function changeHeroPhoto(url) {
    const img = document.getElementById('mainPhoto');
    if (img) img.src = url;
    document.querySelectorAll('[data-photo]').forEach(btn => {
        const isActive = btn.dataset.photo === url;
        btn.classList.toggle('border-amber-500', isActive);
        btn.classList.toggle('opacity-100', isActive);
        btn.classList.toggle('border-transparent', !isActive);
        btn.classList.toggle('opacity-60', !isActive);
    });
}

// ── Scroll vers galerie ───────────────────────────────────────────────────
function scrollToGallery() {
    document.querySelectorAll('.tab-btn').forEach(b => {
        if (b.textContent.includes('Galerie')) b.click();
    });
}

// ── Lightbox ──────────────────────────────────────────────────────────────
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
