<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category->name }} à {{ $city->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .site-card { transition: transform .2s ease, box-shadow .2s ease; }
        .site-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,.4); }
        html:not(.dark) body { background: #f8f5f0; color: #1a1a1a; }
        html:not(.dark) .site-card { background: #fff !important; border-color: rgba(0,0,0,.08) !important; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

@include('partials.public-top-nav')

{{-- Header --}}
<section class="max-w-6xl mx-auto px-6 py-12">
    <nav class="text-xs text-slate-400 mb-5">
        <a href="{{ route('tourist.cities') }}" class="hover:text-amber-400 transition">Régions</a>
        <span class="mx-2 text-slate-600">/</span>
        <a href="{{ route('tourist.city', $city->slug) }}" class="hover:text-amber-400 transition">{{ $city->name }}</a>
        <span class="mx-2 text-slate-600">/</span>
        <span class="text-white">{{ $category->name }}</span>
    </nav>

    <div class="flex items-center gap-4 mb-2">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0"
            style="{{ $category->color ? 'background:' . $category->color . '22; color:' . $category->color : 'background:#1e293b; color:#94a3b8' }}">
            <i class="{{ $category->icon ?: 'fas fa-tag' }}"></i>
        </div>
        <div>
            <h1 class="font-serif text-3xl font-bold text-white">{{ $category->name }}</h1>
            <p class="text-slate-400 text-sm">
                {{ $city->name }}
                @if($sites->count() > 0)
                — {{ $sites->count() }} site(s)
                @endif
                @if(isset($accommodations) && $accommodations->count() > 0)
                — {{ $accommodations->count() }} hébergement(s)
                @endif
            </p>
        </div>
    </div>
    @if($category->description)
    <p class="text-slate-400 mt-4 max-w-2xl">{{ $category->description }}</p>
    @endif
</section>

{{-- Sites list --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    @if($sites->isEmpty())
    <div class="text-center py-16 text-slate-500 border border-slate-800 rounded-2xl">
        <i class="fas fa-map-pin text-4xl mb-3 block text-slate-700"></i>
        Aucun site disponible dans cette catégorie pour le moment.
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($sites as $site)
        @php $firstPhoto = $site->media->first(); @endphp
        <a href="{{ route('tourist.site', $site->slug) }}"
            class="site-card block bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden group">
            <div class="relative h-44 bg-slate-800">
                @if($firstPhoto)
                <img src="{{ $firstPhoto->url }}" alt="{{ $firstPhoto->alt_text ?: $site->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @elseif($site->thumbnail)
                <img src="{{ $site->thumbnail }}" alt="{{ $site->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                @if($site->is_featured)
                <span class="absolute top-3 left-3 px-2 py-0.5 bg-amber-500 text-black text-[10px] font-bold rounded-full">
                    <i class="fas fa-star mr-0.5"></i>Vedette
                </span>
                @endif
                @if($site->entrance_fee)
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-black/60 text-white text-[10px] rounded-full">
                    {{ $site->entrance_fee }}
                </span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="text-white font-semibold group-hover:text-amber-400 transition line-clamp-1 mb-1">
                    {{ $site->name }}
                </h3>
                @if($site->localite || $site->departement)
                <p class="text-amber-400/70 text-xs mb-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    {{ $site->localite ?? $site->departement }}
                </p>
                @endif
                @if($site->short_description)
                <p class="text-slate-500 text-xs line-clamp-2 mb-3">{{ $site->short_description }}</p>
                @endif
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span><i class="fas fa-eye mr-1"></i>{{ number_format($site->views_count) }} vues</span>
                    @if($site->distance_centre_km)
                    <span><i class="fas fa-route mr-1"></i>{{ $site->distance_centre_km }} km</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</section>

{{-- ── Hébergements ──────────────────────────────────────────────────────────── --}}
@if(isset($accommodations) && $accommodations->isNotEmpty())
<section class="max-w-6xl mx-auto px-6 pb-24" id="hebergements">

    {{-- Titre section --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-1 h-6 rounded-full bg-amber-500 shrink-0"></div>
        <h2 class="font-serif text-2xl font-bold text-white">Hébergements</h2>
        <span class="text-slate-500 text-sm">{{ $accommodations->count() }} disponible(s)</span>
        <span id="bk-avail-hint"
              class="hidden ml-auto text-xs text-emerald-400 items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
            Chambres disponibles affichées
        </span>
    </div>

    {{-- ════════════════════════════════════════════════════════
         Widget de recherche / réservation
    ════════════════════════════════════════════════════════ --}}
    <div class="relative bg-[#0e0e0c] border border-amber-500/20 rounded-2xl p-5 mb-8 shadow-2xl overflow-hidden">

        {{-- Lueur décorative --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-16 -left-16 w-56 h-56 bg-amber-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 right-0 w-48 h-48 bg-amber-500/4 rounded-full blur-3xl"></div>
        </div>

        <div class="relative">
            {{-- En-tête widget --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-amber-500/15 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-amber-400 text-xs"></i>
                    </div>
                    <span class="text-white font-semibold text-sm">Votre séjour</span>
                </div>
                <div id="bk-summary" class="hidden items-center gap-2">
                    <span id="bk-nights-label"
                          class="px-3 py-1 bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-bold rounded-full">
                    </span>
                    <span id="bk-guests-label"
                          class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 text-xs rounded-full">
                    </span>
                </div>
            </div>

            {{-- Champs --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                {{-- Arrivée --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-plane-arrival text-amber-400/60"></i> Arrivée
                    </label>
                    <div class="relative">
                        <input type="date" id="bk-checkin"
                               min="{{ date('Y-m-d') }}"
                               class="bk-date-input w-full bg-slate-900 border border-slate-700 focus:border-amber-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition cursor-pointer"
                               style="color-scheme:dark">
                    </div>
                </div>

                {{-- Départ --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-plane-departure text-amber-400/60"></i> Départ
                    </label>
                    <div class="relative">
                        <input type="date" id="bk-checkout"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="bk-date-input w-full bg-slate-900 border border-slate-700 focus:border-amber-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition cursor-pointer"
                               style="color-scheme:dark">
                    </div>
                </div>

                {{-- Chambres --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-door-open text-amber-400/60"></i> Chambres
                    </label>
                    <div class="flex items-center gap-0 bg-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                        <button type="button" onclick="bkStep('rooms',-1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span id="bk-rooms-val"
                              class="flex-1 text-center text-white font-semibold text-sm">1</span>
                        <button type="button" onclick="bkStep('rooms',+1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Personnes --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-user-group text-amber-400/60"></i> Personnes
                    </label>
                    <div class="flex items-center gap-0 bg-slate-900 border border-slate-700 rounded-xl overflow-hidden">
                        <button type="button" onclick="bkStep('guests',-1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span id="bk-guests-val"
                              class="flex-1 text-center text-white font-semibold text-sm">2</span>
                        <button type="button" onclick="bkStep('guests',+1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-amber-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Ligne du bas : horaires de la destination --}}
            @php $firstCheckIn = $accommodations->first()?->check_in_time; @endphp
            @if($firstCheckIn)
            <div class="mt-3 flex items-center gap-3 text-[11px] text-slate-600">
                <i class="fas fa-clock text-[9px]"></i>
                Check-in généralement à partir de {{ $firstCheckIn }}
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         Grille des cartes hébergements
    ════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($accommodations as $acc)
        @php
            $photo  = $acc->media->first();
            $imgSrc = $photo?->url ?? $acc->cover_image ?? $acc->thumbnail;
            $links  = $acc->booking_links ?? [];
        @endphp
        <div class="accom-card flex flex-col bg-[#111110] border border-slate-800 hover:border-amber-500/30 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/50 group"
             data-acc-rooms='@json($acc->room_types ?? [])'
             data-acc-links='@json($acc->booking_links ?? [])'
             data-acc-cover="{{ $imgSrc ?? '' }}">

            {{-- Photo --}}
            <div class="relative h-44 bg-slate-800 shrink-0 overflow-hidden">
                @if($imgSrc)
                <img src="{{ $imgSrc }}" alt="{{ $acc->name }}" loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-hotel text-3xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-linear-to-t from-black/70 via-black/10 to-transparent"></div>

                {{-- Badges type + étoiles --}}
                <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/60 backdrop-blur-sm border border-white/10 text-white">
                        {{ $acc->type_label }}
                    </span>
                    @if($acc->stars > 0)
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/90 text-black">
                        @for($s=0;$s<$acc->stars;$s++)<i class="fas fa-star text-[7px]"></i>@endfor
                    </span>
                    @endif
                </div>

                @if($acc->is_featured)
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-amber-500 text-black text-[10px] font-bold rounded-full">
                    <i class="fas fa-star text-[8px] mr-0.5"></i>Vedette
                </span>
                @endif

                {{-- Prix dynamique --}}
                <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between">
                    @if($acc->starting_price_xof)
                    <span class="acc-price-display text-[11px] font-semibold text-white bg-black/60 backdrop-blur-sm px-2.5 py-1 rounded-full border border-white/10"
                          data-price-xof="{{ $acc->starting_price_xof }}"
                          data-price-eur="{{ $acc->starting_price_eur ?? '' }}">
                        À partir de {{ number_format($acc->starting_price_xof, 0, ',', ' ') }} XOF<span class="opacity-60">/nuit</span>
                    </span>
                    @endif
                    @if($acc->check_in_time)
                    <span class="text-[10px] text-slate-400/80 bg-black/50 px-2 py-0.5 rounded-full border border-white/10">
                        <i class="fas fa-clock text-[8px] mr-0.5"></i>{{ $acc->check_in_time }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Contenu --}}
            <div class="p-4 flex flex-col flex-1">

                {{-- Localisation --}}
                <div class="flex items-center gap-1 text-slate-500 text-[11px] mb-1.5">
                    <i class="fas fa-location-dot text-amber-400/50 text-[9px]"></i>
                    <span>{{ $acc->city?->name }}</span>
                    @if($acc->quartier)
                        <span class="text-slate-700 mx-0.5">·</span>
                        <span>{{ $acc->quartier }}</span>
                    @endif
                </div>

                <h3 class="text-white font-semibold group-hover:text-amber-400 transition line-clamp-1 mb-1 leading-tight">
                    {{ $acc->name }}
                </h3>

                @if($acc->short_description)
                <p class="text-slate-500 text-xs line-clamp-2 leading-relaxed mb-3">{{ $acc->short_description }}</p>
                @endif

                {{-- Commodités --}}
                @if($acc->amenities)
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach(array_slice($acc->amenities, 0, 4) as $am)
                    <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 bg-slate-800/70 border border-slate-700/50 rounded-md px-1.5 py-0.5">
                        <i class="{{ $am['icon'] ?? 'fas fa-check' }} text-[7px] text-amber-400/50"></i>
                        {{ $am['label'] }}
                    </span>
                    @endforeach
                    @if(count($acc->amenities) > 4)
                    <span class="text-[10px] text-slate-600 self-center">+{{ count($acc->amenities)-4 }}</span>
                    @endif
                </div>
                @endif

                {{-- Espaceur --}}
                <div class="flex-1"></div>

                {{-- Résumé séjour dynamique --}}
                <div class="acc-stay-summary hidden mb-3 px-3 py-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-300/90">
                </div>

                {{-- Panneau chambres disponibles (rempli dynamiquement par JS) --}}
                <div class="acc-rooms-panel hidden"></div>

                {{-- Boutons réservation (masqués quand le panneau chambres est actif) --}}
                @if(count($links) > 0)
                <div class="acc-booking-links pt-3 border-t border-slate-800/80 flex flex-wrap gap-2">
                    @foreach(array_slice($links, 0, 3) as $bl)
                    <a href="{{ $bl['affiliate_url'] ?? '#' }}"
                       data-base-url="{{ $bl['affiliate_url'] ?? '#' }}"
                       data-provider="{{ strtolower($bl['provider_name'] ?? '') }}"
                       target="_blank" rel="noopener nofollow"
                       class="bk-link inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium transition
                              {{ !empty($bl['is_official'])
                                 ? 'bg-amber-500/20 border border-amber-500/35 text-amber-300 hover:bg-amber-500/30'
                                 : 'bg-slate-800 border border-slate-700 text-slate-400 hover:border-slate-600 hover:text-slate-300' }}">
                        @if(!empty($bl['logo_url']))
                            <img src="{{ $bl['logo_url'] }}" alt="{{ $bl['provider_name'] }}" class="h-3.5 w-auto object-contain">
                        @else
                            <i class="fas fa-bookmark text-[9px]"></i>
                            <span>{{ $bl['provider_name'] }}</span>
                        @endif
                        @if(!empty($bl['badge_text']))
                            <span class="opacity-60">· {{ $bl['badge_text'] }}</span>
                        @endif
                        @if(!empty($bl['is_official']))
                            <i class="fas fa-certificate text-[9px] text-amber-400" title="Site officiel"></i>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ════════════════════════════════════════════════════════
     Modal détail chambre
════════════════════════════════════════════════════════ --}}
<div id="room-modal"
     class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4"
     role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/85 backdrop-blur-sm" onclick="closeRoomModal()"></div>

    {{-- Panneau --}}
    <div class="relative z-10 w-full sm:max-w-4xl max-h-[95vh] bg-[#0e0e0c] border border-slate-800 rounded-t-2xl sm:rounded-2xl overflow-hidden flex flex-col shadow-2xl">

        {{-- Bouton fermer --}}
        <button onclick="closeRoomModal()"
                class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-black/60 backdrop-blur-sm border border-white/10 text-white hover:bg-slate-700 flex items-center justify-center transition">
            <i class="fas fa-times text-sm"></i>
        </button>

        {{-- Hero photo --}}
        <div class="relative shrink-0 overflow-hidden bg-slate-900" style="height:460px">
            <img id="rm-hero-img" src="" alt=""
                 class="w-full h-full object-cover transition duration-300">
            <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/20 to-transparent"></div>
            <div class="absolute bottom-4 left-5 right-14">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold rounded-full mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>Disponible
                </span>
                <h3 id="rm-name" class="font-serif text-2xl font-bold text-white leading-tight"></h3>
            </div>
        </div>

        {{-- Strip galerie photos --}}
        <div id="rm-gallery"
             class="hidden gap-2 px-4 py-3 bg-black/40 overflow-x-auto shrink-0"
             style="scrollbar-width:none;display:none">
        </div>

        {{-- Corps scrollable --}}
        <div class="overflow-y-auto flex-1">
            <div class="p-5 grid grid-cols-1 sm:grid-cols-5 gap-5">

                {{-- Colonne gauche : infos --}}
                <div class="sm:col-span-3 space-y-5">

                    {{-- Capacité + surface --}}
                    <div id="rm-meta" class="flex flex-wrap gap-4 text-sm text-slate-300"></div>

                    {{-- Équipements --}}
                    <div id="rm-amenities-section">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-2.5">Équipements</p>
                        <div id="rm-amenities" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>

                {{-- Colonne droite : prix + réservation --}}
                <div class="sm:col-span-2">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-3 sticky top-4">
                        <div>
                            <div id="rm-price-ppn" class="text-xl font-bold text-white leading-tight"></div>
                            <div id="rm-price-total" class="text-amber-400 text-sm font-semibold mt-0.5"></div>
                            <div id="rm-stay-info" class="text-slate-500 text-xs mt-1 leading-relaxed"></div>
                        </div>
                        <a id="rm-book-btn" href="#" target="_blank" rel="noopener nofollow"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-amber-500 hover:bg-amber-400 text-black font-bold rounded-xl transition text-sm">
                            <i class="fas fa-calendar-check"></i>Réserver cette chambre
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Forcer le thème sombre sur le picker natif */
.bk-date-input::-webkit-calendar-picker-indicator { filter: invert(.7) sepia(1) hue-rotate(10deg) saturate(.8); cursor: pointer; }
.bk-date-input::-webkit-datetime-edit { color: #cbd5e1; }
</style>

<script>
(function () {
    let bkRooms  = 1;
    let bkGuests = 2;

    const fmt     = n => new Intl.NumberFormat('fr-FR').format(n);
    const fmtDate = d => { const [y,m,j] = d.split('-'); return j+'/'+m+'/'+y; };

    // Registre des données de chambre pour le modal (clé → données)
    const _rooms = {};

    // ── Steppers ──────────────────────────────────────────────────────────
    window.bkStep = function (field, delta) {
        if (field === 'rooms') {
            bkRooms = Math.max(1, Math.min(10, bkRooms + delta));
            document.getElementById('bk-rooms-val').textContent = bkRooms;
        } else {
            bkGuests = Math.max(1, Math.min(20, bkGuests + delta));
            document.getElementById('bk-guests-val').textContent = bkGuests;
        }
        bkUpdate();
    };

    // ── Construit l'URL d'un provider avec les paramètres de séjour ───────
    function buildBookUrl(base, providerName, ci, co) {
        if (!base || base === '#') return '#';
        const p  = new URLSearchParams();
        const pr = (providerName || '').toLowerCase();
        if (pr.includes('booking')) {
            p.set('checkin', ci);      p.set('checkout', co);
            p.set('group_adults', bkGuests); p.set('no_rooms', bkRooms);
        } else if (pr.includes('airbnb')) {
            p.set('check_in', ci);    p.set('check_out', co);
            p.set('adults', bkGuests);
        } else if (pr.includes('expedia') || pr.includes('hotels')) {
            p.set('startDate', ci);   p.set('endDate', co);
            p.set('adults', bkGuests); p.set('rooms', bkRooms);
        } else {
            p.set('checkin', ci);     p.set('checkout', co);
            p.set('adults', bkGuests); p.set('rooms', bkRooms);
        }
        return base + (base.includes('?') ? '&' : '?') + p.toString();
    }

    // ── Modal détail chambre ───────────────────────────────────────────────
    window.openRoomModal = function (key) {
        const d = _rooms[key];
        if (!d) return;
        const { r, bookUrl, nights, ci, co } = d;

        // Hero photo (première photo de la chambre ou couverture hébergement)
        let photos = Array.isArray(r.photos) ? r.photos.filter(Boolean) : [];
        if (!photos.length && r.thumbnail) photos = [r.thumbnail];
        const heroSrc = photos[0] || d.accCoverUrl || '';
        const heroImg = document.getElementById('rm-hero-img');
        heroImg.src = heroSrc;
        heroImg.alt = r.name || 'Chambre';

        // Nom
        document.getElementById('rm-name').textContent = r.name || 'Chambre';

        // Strip galerie (affichée seulement si > 1 photo)
        const gallery = document.getElementById('rm-gallery');
        if (photos.length > 1) {
            gallery.style.display = 'flex';
            gallery.innerHTML = photos.map(function (url, i) {
                return '<button onclick="document.getElementById(\'rm-hero-img\').src=\'' + url + '\'"'
                    + ' class="flex-shrink-0 w-20 h-14 rounded-lg overflow-hidden border-2 transition '
                    + (i === 0 ? 'border-amber-500' : 'border-transparent opacity-60 hover:opacity-100') + '">'
                    + '<img src="' + url + '" class="w-full h-full object-cover" loading="lazy">'
                    + '</button>';
            }).join('');
        } else {
            gallery.style.display = 'none';
            gallery.innerHTML = '';
        }

        // Méta (capacité + surface)
        const metaEl = document.getElementById('rm-meta');
        const meta   = [];
        if (r.max_adults)   meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-user text-amber-400/70"></i>' + r.max_adults   + ' adulte'  + (r.max_adults   > 1 ? 's' : '') + ' max</span>');
        if (r.max_children) meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-child text-amber-400/70"></i>' + r.max_children + ' enfant' + (r.max_children > 1 ? 's' : '') + ' max</span>');
        if (r.area_m2)      meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-vector-square text-amber-400/70"></i>' + r.area_m2 + ' m²</span>');
        metaEl.innerHTML = meta.join('');

        // Équipements
        let ams = [];
        if (Array.isArray(r.amenities)) ams = r.amenities;
        else if (typeof r.amenities === 'string' && r.amenities)
            ams = r.amenities.split(',').map(function (s) { return s.trim(); }).filter(Boolean);

        const amsEl   = document.getElementById('rm-amenities');
        const amsSect = document.getElementById('rm-amenities-section');
        if (ams.length) {
            amsEl.innerHTML = ams.map(function (a) {
                return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-slate-300">'
                    + '<i class="fas fa-check text-amber-400/60 text-[8px]"></i>' + a + '</span>';
            }).join('');
            amsSect.style.display = '';
        } else {
            amsSect.style.display = 'none';
        }

        // Prix
        const ppn      = parseInt(r.price_xof || 0);
        const totalXof = ppn && nights > 0 ? ppn * nights * bkRooms : 0;
        const ppnEl    = document.getElementById('rm-price-ppn');
        const totEl    = document.getElementById('rm-price-total');
        const stayEl   = document.getElementById('rm-stay-info');

        if (ppn) {
            ppnEl.innerHTML    = fmt(ppn) + ' <span class="text-sm font-normal text-slate-500">XOF / nuit</span>';
            totEl.textContent  = totalXof ? 'Total : ' + fmt(totalXof) + ' XOF' : '';
            stayEl.textContent = nights > 0
                ? nights + ' nuit' + (nights > 1 ? 's' : '') + '  ·  '
                  + bkRooms + ' chambre' + (bkRooms > 1 ? 's' : '') + '  ·  '
                  + bkGuests + ' pers.'
                : '';
        } else {
            ppnEl.textContent  = 'Prix sur demande';
            totEl.textContent  = '';
            stayEl.textContent = '';
        }

        // Bouton réserver
        const btn = document.getElementById('rm-book-btn');
        btn.href                = bookUrl !== '#' ? bookUrl : '#';
        btn.style.opacity       = bookUrl !== '#' ? '' : '0.4';
        btn.style.pointerEvents = bookUrl !== '#' ? '' : 'none';

        // Affichage modal
        document.getElementById('room-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeRoomModal = function () {
        document.getElementById('room-modal').style.display = 'none';
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') window.closeRoomModal();
    });

    // ── Panneau chambres disponibles ──────────────────────────────────────
    function renderRoomsPanel(card, nights, ci, co) {
        const panel   = card.querySelector('.acc-rooms-panel');
        const linksEl = card.querySelector('.acc-booking-links');
        if (!panel) return;

        if (nights <= 0) {
            panel.classList.add('hidden');
            panel.innerHTML = '';
            if (linksEl) linksEl.classList.remove('hidden');
            return;
        }

        let rooms = [];
        try { rooms = JSON.parse(card.dataset.accRooms || '[]'); } catch(e) {}
        let links = [];
        try { links = JSON.parse(card.dataset.accLinks || '[]'); } catch(e) {}

        const accCoverUrl = card.dataset.accCover || '';

        // Masquer les boutons classiques, révéler le panneau
        if (linksEl) linksEl.classList.add('hidden');
        panel.classList.remove('hidden');

        if (!rooms.length) {
            panel.innerHTML =
                '<div class="mt-3 pt-3 border-t border-slate-800/80 text-center text-slate-600 text-xs py-3">'
                + '<i class="fas fa-info-circle mr-1"></i>Aucun type de chambre renseigné.</div>';
            return;
        }

        // Filtrer par capacité : max_adults doit couvrir ceil(bkGuests/bkRooms) par chambre
        const gPR   = Math.max(1, Math.ceil(bkGuests / bkRooms));
        const avail = rooms.filter(r => {
            const maxA = parseInt(r.max_adults || 0);
            return maxA === 0 || maxA >= gPR;
        });

        if (!avail.length) {
            panel.innerHTML =
                '<div class="mt-3 pt-3 border-t border-slate-800/80 text-center text-xs py-3">'
                + '<span class="text-amber-600/70"><i class="fas fa-exclamation-circle mr-1"></i>'
                + 'Aucune chambre pour ' + gPR + ' adulte' + (gPR > 1 ? 's' : '') + '/chambre.</span><br>'
                + '<span class="text-slate-600 text-[10px]">Ajustez le nombre de personnes ou de chambres.</span></div>';
            return;
        }

        // Lien officiel ou premier lien disponible pour le bouton "Réserver"
        const bkLink = links.find(l => l.is_official) || links[0] || null;

        let html =
            '<div class="mt-3 pt-3 border-t border-slate-800/80 space-y-2">'
            + '<p class="text-[10px] text-slate-500 uppercase tracking-wider flex items-center gap-1.5 mb-1">'
            + '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>'
            + avail.length + ' chambre' + (avail.length > 1 ? 's' : '') + ' disponible' + (avail.length > 1 ? 's' : '')
            + '</p>';

        avail.forEach(function (r) {
            const ppn      = parseInt(r.price_xof || 0);
            const totalXof = ppn ? ppn * nights * bkRooms : 0;

            let ams = [];
            if (Array.isArray(r.amenities)) {
                ams = r.amenities;
            } else if (typeof r.amenities === 'string' && r.amenities) {
                ams = r.amenities.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            }

            const bookUrl = bkLink
                ? buildBookUrl(bkLink.affiliate_url, bkLink.provider_name, ci, co)
                : '#';

            // Enregistrement pour le modal
            const key = 'r' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
            _rooms[key] = { r, bookUrl, nights, ci, co, accCoverUrl };

            const metaItems = [];
            if (r.max_adults)   metaItems.push('<i class="fas fa-user text-[8px] mr-0.5"></i>' + r.max_adults + ' adulte' + (r.max_adults > 1 ? 's' : ''));
            if (r.max_children) metaItems.push('<i class="fas fa-child text-[8px] mr-0.5"></i>' + r.max_children + ' enfant' + (r.max_children > 1 ? 's' : ''));
            if (r.area_m2)      metaItems.push('<i class="fas fa-vector-square text-[8px] mr-0.5"></i>' + r.area_m2 + ' m²');

            const amHtml = ams.slice(0, 5).map(function(a){
                return '<span class="text-[9px] text-slate-500 bg-slate-700/50 rounded px-1.5 py-0.5">' + a + '</span>';
            }).join('') + (ams.length > 5 ? '<span class="text-[9px] text-slate-600">+' + (ams.length - 5) + '</span>' : '');

            html +=
                '<div class="bg-slate-800/50 border border-slate-700/60 rounded-xl p-3">'
                + '<div class="flex items-start justify-between gap-2">'
                +   '<div class="flex-1 min-w-0">'
                +     '<div class="flex items-center gap-1.5 flex-wrap">'
                +       '<span class="text-white text-xs font-semibold">' + (r.name || 'Chambre') + '</span>'
                +       '<span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[9px] font-bold rounded-full">'
                +         '<span class="w-1 h-1 rounded-full bg-emerald-400 inline-block"></span>Disponible'
                +       '</span>'
                +     '</div>'
                +     (metaItems.length
                        ? '<div class="flex flex-wrap gap-2 mt-0.5 text-[10px] text-slate-500">'
                          + metaItems.map(function(i){ return '<span>' + i + '</span>'; }).join('')
                          + '</div>'
                        : '')
                +   '</div>'
                +   (ppn
                      ? '<div class="text-right shrink-0">'
                        + '<div class="text-white text-xs font-bold">' + fmt(ppn) + '<span class="text-[9px] font-normal text-slate-500"> XOF/nuit</span></div>'
                        + (totalXof ? '<div class="text-amber-400/80 text-[10px]">Total : ' + fmt(totalXof) + ' XOF</div>' : '')
                        + '</div>'
                      : '')
                + '</div>'
                + (ams.length ? '<div class="flex flex-wrap gap-1 mt-2 mb-2.5">' + amHtml + '</div>' : '<div class="mt-2"></div>')
                + '<div class="flex flex-wrap gap-2">'
                +   '<button onclick="openRoomModal(\'' + key + '\')"'
                +     ' class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-[10px] font-medium rounded-lg transition">'
                +     '<i class="fas fa-images text-[9px]"></i>Voir la chambre'
                +   '</button>'
                +   (bookUrl !== '#'
                        ? '<a href="' + bookUrl + '" target="_blank" rel="noopener nofollow"'
                          + ' class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-black text-[10px] font-bold rounded-lg transition">'
                          + '<i class="fas fa-calendar-check text-[9px]"></i>Réserver · ' + bkRooms + ' ch.'
                          + '</a>'
                        : '')
                + '</div>'
                + '</div>';
        });

        html += '</div>';
        panel.innerHTML = html;
    }

    // ── Calcul nuits + mise à jour UI ─────────────────────────────────────
    function bkUpdate() {
        const ciEl = document.getElementById('bk-checkin');
        const coEl = document.getElementById('bk-checkout');
        const ci = ciEl.value, co = coEl.value;

        let nights = 0;
        if (ci && co && co > ci) {
            nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
            coEl.min = ci;
        }

        // Summary badge + indicateur de disponibilité
        const summaryEl = document.getElementById('bk-summary');
        const nightsEl  = document.getElementById('bk-nights-label');
        const guestsEl  = document.getElementById('bk-guests-label');
        const hintEl    = document.getElementById('bk-avail-hint');

        if (nights > 0) {
            nightsEl.textContent = nights + ' nuit' + (nights > 1 ? 's' : '');
            guestsEl.textContent = bkRooms + ' ch. · ' + bkGuests + ' pers.';
            summaryEl.classList.remove('hidden'); summaryEl.classList.add('flex');
            if (hintEl) { hintEl.classList.remove('hidden'); hintEl.classList.add('flex'); }
        } else {
            summaryEl.classList.add('hidden'); summaryEl.classList.remove('flex');
            if (hintEl) { hintEl.classList.add('hidden'); hintEl.classList.remove('flex'); }
        }

        document.querySelectorAll('.accom-card').forEach(function (card) {

            // ── Prix dynamique ──────────────────────────────────────────
            const priceSpan = card.querySelector('.acc-price-display');
            if (priceSpan) {
                const ppn = parseInt(priceSpan.dataset.priceXof || 0);
                if (nights > 0 && ppn) {
                    const total = ppn * nights * bkRooms;
                    priceSpan.innerHTML = fmt(total) + ' XOF'
                        + '<span class="opacity-60"> · ' + nights + ' nuit' + (nights > 1 ? 's' : '') + '</span>';
                } else if (ppn) {
                    priceSpan.innerHTML = 'À partir de ' + fmt(ppn) + ' XOF'
                        + '<span class="opacity-60">/nuit</span>';
                }
            }

            // ── Résumé séjour ───────────────────────────────────────────
            const stayEl = card.querySelector('.acc-stay-summary');
            if (stayEl) {
                if (nights > 0) {
                    stayEl.innerHTML =
                        '<i class="fas fa-calendar-check mr-1.5"></i>'
                        + fmtDate(ci) + ' → ' + fmtDate(co)
                        + '&nbsp;&nbsp;·&nbsp;&nbsp;<i class="fas fa-door-open mr-1"></i>'
                        + bkRooms + ' chambre' + (bkRooms > 1 ? 's' : '')
                        + '&nbsp;&nbsp;·&nbsp;&nbsp;<i class="fas fa-user-group mr-1"></i>'
                        + bkGuests + ' personne' + (bkGuests > 1 ? 's' : '');
                    stayEl.classList.remove('hidden');
                } else {
                    stayEl.classList.add('hidden');
                }
            }

            // ── Liens réservation classiques (URL dynamiques) ───────────
            card.querySelectorAll('.bk-link').forEach(function (a) {
                const base     = a.dataset.baseUrl || '#';
                const provider = a.dataset.provider || '';
                if (base === '#' || !ci || !co) { a.href = base; return; }
                a.href = buildBookUrl(base, provider, ci, co);
            });

            // ── Panneau chambres disponibles ────────────────────────────
            renderRoomsPanel(card, nights, ci, co);
        });
    }

    // ── Écoute des changements de date ────────────────────────────────────
    document.getElementById('bk-checkin').addEventListener('change', function () {
        const co = document.getElementById('bk-checkout');
        co.min = this.value;
        if (co.value && co.value <= this.value) co.value = '';
        bkUpdate();
    });
    document.getElementById('bk-checkout').addEventListener('change', bkUpdate);
})();
</script>
@endif

@include('partials.homepage-footer')
</body>
</html>
