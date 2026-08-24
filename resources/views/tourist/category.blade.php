<!DOCTYPE html>
<html lang="fr" id="html-root" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        html:not(.dark) body { background:#e9e5d9; color: #1c1915; }
        html:not(.dark) .site-card { background:#f0ece1 !important; border-color: rgba(0,0,0,.12) !important; box-shadow: 0 8px 20px rgba(0,0,0,.05); }
        /* Image de fond configurable (back-office, par catégorie) du bandeau —
           même modèle que .cultural-hero / .tourist-hero. */
        .category-hero {
            background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55)), var(--category-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="bg-[#ffffff] text-white min-h-screen">

@include('partials.public-top-nav')

{{-- Hero --}}
<section class="category-hero relative py-20 overflow-hidden"
    @if($category->hero_image_url)
        style="--category-hero-bg-image: url('{{ $category->hero_image_url }}');"
    @endif
>
    <div class="absolute inset-0 bg-gradient-to-b from-orange-900/20 to-transparent pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('tourist.cities') }}" class="hover:text-orange-400 transition">Régions</a>
            <span class="mx-2 text-slate-600">/</span>
            <a href="{{ route('tourist.city', $city->slug) }}" class="hover:text-orange-400 transition">{{ $city->name }}</a>
            <span class="mx-2 text-slate-600">/</span>
            <span class="text-white">{{ $category->name }}</span>
        </nav>
        <p class="text-orange-400 text-sm font-medium uppercase tracking-widest mb-3">{{ $city->name }}</p>
        <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
            {{ $category->name }}
        </h1>
        <p class="text-[#1c1915] text-xl max-w-2xl mx-auto">
            @if($category->description)
                {{ $category->description }}
            @else
                {{ $sites->count() }} site(s) touristique(s)
                @if(isset($accommodations) && $accommodations->count() > 0)
                    · {{ $accommodations->count() }} hébergement(s)
                @endif
            @endif
        </p>
    </div>
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
            class="site-card block bg-[#ffffff] border border-slate-800 rounded-2xl overflow-hidden group">
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
                <div class="absolute inset-0 bg-gradient-to-t from-green-950/50 to-transparent"></div>
                @if($site->is_featured)
                <span class="absolute top-3 left-3 px-2 py-0.5 bg-orange-500 text-black text-[10px] font-bold rounded-full">
                    <i class="fas fa-star mr-0.5"></i>Vedette
                </span>
                @endif
                @if($site->entrance_fee)
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-green-950/60 text-white text-[10px] rounded-full">
                    {{ $site->entrance_fee }}
                </span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="text-white font-semibold group-hover:text-orange-400 transition line-clamp-1 mb-1">
                    {{ $site->name }}
                </h3>
                @if($site->localite || $site->departement)
                <p class="text-orange-400/70 text-xs mb-2">
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
        <div class="w-1 h-6 rounded-full bg-orange-500 shrink-0"></div>
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
    <div class="relative bg-[#ffffff] border border-orange-500/20 rounded-2xl p-5 mb-8 shadow-2xl overflow-hidden">

        {{-- Lueur décorative --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-16 -left-16 w-56 h-56 bg-orange-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 right-0 w-48 h-48 bg-orange-500/4 rounded-full blur-3xl"></div>
        </div>

        <div class="relative">
            {{-- En-tête widget --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-orange-500/15 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-orange-400 text-xs"></i>
                    </div>
                    <span class="text-white font-semibold text-sm">Votre séjour</span>
                </div>
                <div id="bk-summary" class="hidden items-center gap-2">
                    <span id="bk-nights-label"
                          class="px-3 py-1 bg-orange-500/20 border border-orange-500/30 text-orange-300 text-xs font-bold rounded-full">
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
                        <i class="fas fa-plane-arrival text-orange-400/60"></i> Arrivée
                    </label>
                    <div class="relative">
                        <input type="date" id="bk-checkin"
                               min="{{ date('Y-m-d') }}"
                               class="bk-date-input w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition cursor-pointer"
                               style="color-scheme:dark">
                    </div>
                </div>

                {{-- Départ --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-plane-departure text-orange-400/60"></i> Départ
                    </label>
                    <div class="relative">
                        <input type="date" id="bk-checkout"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="bk-date-input w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition cursor-pointer"
                               style="color-scheme:dark">
                    </div>
                </div>

                {{-- Chambres --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-door-open text-orange-400/60"></i> Chambres
                    </label>
                    <div class="flex items-center gap-0 bg-green-900 border border-slate-700 rounded-xl overflow-hidden">
                        <button type="button" onclick="bkStep('rooms',-1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span id="bk-rooms-val"
                              class="flex-1 text-center text-white font-semibold text-sm">1</span>
                        <button type="button" onclick="bkStep('rooms',+1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Personnes --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-user-group text-orange-400/60"></i> Personnes
                    </label>
                    <div class="flex items-center gap-0 bg-green-900 border border-slate-700 rounded-xl overflow-hidden">
                        <button type="button" onclick="bkStep('guests',-1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span id="bk-guests-val"
                              class="flex-1 text-center text-white font-semibold text-sm">2</span>
                        <button type="button" onclick="bkStep('guests',+1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
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
        <div class="accom-card flex flex-col bg-[#ffffff] border border-slate-800 hover:border-orange-500/30 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-green-950/50 group"
             data-acc-id="{{ $acc->id }}"
             data-acc-name="{{ $acc->name }}"
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
                <div class="absolute inset-0 bg-linear-to-t from-green-950/70 via-green-950/10 to-transparent"></div>

                {{-- Badges type + étoiles --}}
                <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950/60 backdrop-blur-sm border border-white/10 text-white">
                        {{ $acc->type_label }}
                    </span>
                    @if($acc->stars > 0)
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-500/90 text-black">
                        @for($s=0;$s<$acc->stars;$s++)<i class="fas fa-star text-[7px]"></i>@endfor
                    </span>
                    @endif
                </div>

                @if($acc->is_featured)
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-orange-500 text-black text-[10px] font-bold rounded-full">
                    <i class="fas fa-star text-[8px] mr-0.5"></i>Vedette
                </span>
                @endif

                {{-- Prix dynamique --}}
                <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between">
                    @if($acc->starting_price_xof)
                    <span class="acc-price-display text-[11px] font-semibold text-white bg-green-950/60 backdrop-blur-sm px-2.5 py-1 rounded-full border border-white/10"
                          data-price-xof="{{ $acc->starting_price_xof }}"
                          data-price-eur="{{ $acc->starting_price_eur ?? '' }}">
                        À partir de {{ number_format($acc->starting_price_xof, 0, ',', ' ') }} XOF<span class="opacity-60">/nuit</span>
                    </span>
                    @endif
                    @if($acc->check_in_time)
                    <span class="text-[10px] text-slate-400/80 bg-green-950/50 px-2 py-0.5 rounded-full border border-white/10">
                        <i class="fas fa-clock text-[8px] mr-0.5"></i>{{ $acc->check_in_time }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Contenu --}}
            <div class="p-4 flex flex-col flex-1">

                {{-- Localisation --}}
                <div class="flex items-center gap-1 text-slate-500 text-[11px] mb-1.5">
                    <i class="fas fa-location-dot text-orange-400/50 text-[9px]"></i>
                    <span>{{ $acc->city?->name }}</span>
                    @if($acc->quartier)
                        <span class="text-slate-700 mx-0.5">·</span>
                        <span>{{ $acc->quartier }}</span>
                    @endif
                </div>

                <h3 class="text-white font-semibold group-hover:text-orange-400 transition line-clamp-1 mb-1 leading-tight">
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
                        <i class="{{ $am['icon'] ?? 'fas fa-check' }} text-[7px] text-orange-400/50"></i>
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
                <div class="acc-stay-summary hidden mb-3 px-3 py-2 bg-orange-500/10 border border-orange-500/20 rounded-xl text-xs text-orange-300/90">
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
                                 ? 'bg-orange-500/20 border border-orange-500/35 text-orange-300 hover:bg-orange-500/30'
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
                            <i class="fas fa-certificate text-[9px] text-orange-400" title="Site officiel"></i>
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
    <div class="absolute inset-0 bg-green-950/85 backdrop-blur-sm" onclick="closeRoomModal()"></div>

    {{-- Panneau --}}
    <div class="relative z-10 w-full sm:max-w-4xl max-h-[95vh] bg-[#ffffff] border border-slate-800 rounded-t-2xl sm:rounded-2xl overflow-hidden flex flex-col shadow-2xl">

        {{-- Bouton fermer --}}
        <button onclick="closeRoomModal()"
                class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-green-950/60 backdrop-blur-sm border border-white/10 text-white hover:bg-slate-700 flex items-center justify-center transition">
            <i class="fas fa-times text-sm"></i>
        </button>

        {{-- Hero photo --}}
        <div class="relative shrink-0 overflow-hidden bg-green-900" style="height:460px">
            <img id="rm-hero-img" src="" alt=""
                 class="w-full h-full object-cover transition duration-300">
            <div class="absolute inset-0 bg-linear-to-t from-green-950/80 via-green-950/20 to-transparent"></div>
            <div class="absolute bottom-4 left-5 right-14">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold rounded-full mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>Disponible
                </span>
                <h3 id="rm-name" class="font-serif text-2xl font-bold text-white leading-tight"></h3>
            </div>
        </div>

        {{-- Strip galerie photos --}}
        <div id="rm-gallery"
             class="hidden gap-2 px-4 py-3 bg-green-950/40 overflow-x-auto shrink-0"
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
                    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 space-y-3 sticky top-4">
                        <div>
                            <div id="rm-price-ppn" class="text-xl font-bold text-white leading-tight"></div>
                            <div id="rm-price-total" class="text-orange-400 text-sm font-semibold mt-0.5"></div>
                            <div id="rm-stay-info" class="text-slate-500 text-xs mt-1 leading-relaxed"></div>
                        </div>
                        <a id="rm-book-btn" href="#" target="_blank" rel="noopener nofollow"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-orange-500 hover:bg-orange-400 text-black font-bold rounded-xl transition text-sm">
                            <i class="fas fa-calendar-check"></i>Réserver cette chambre
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     Modal liste des chambres disponibles (par hébergement)
════════════════════════════════════════════════════════ --}}
<div id="rooms-list-modal"
     class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4"
     role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-green-950/85 backdrop-blur-sm" onclick="closeRoomsListModal()"></div>

    {{-- Panneau --}}
    <div class="relative z-10 w-full sm:max-w-6xl h-[95vh] sm:h-auto sm:max-h-[95vh] bg-[#ffffff] border border-slate-800 rounded-t-2xl sm:rounded-2xl overflow-hidden flex flex-col shadow-2xl">

        {{-- En-tête --}}
        <div class="flex items-center justify-between gap-3 px-6 py-5 border-b border-slate-800 shrink-0">
            <div class="min-w-0">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Chambres disponibles</p>
                <h3 id="rlm-acc-name" class="text-white font-semibold text-lg truncate"></h3>
            </div>
            <button onclick="closeRoomsListModal()"
                    class="shrink-0 w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 flex items-center justify-center transition">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        {{-- Corps scrollable --}}
        <div id="rlm-body" class="overflow-y-auto flex-1 p-6"></div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     Modal demande de réservation (envoi vers le back-office)
════════════════════════════════════════════════════════ --}}
<div id="reservation-modal"
     class="fixed inset-0 z-[60] hidden items-end sm:items-center justify-center p-0 sm:p-4"
     role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-green-950/85 backdrop-blur-sm" onclick="closeReservationModal()"></div>

    {{-- Panneau --}}
    <div class="relative z-10 w-full sm:max-w-md max-h-[92vh] bg-[#ffffff] border border-slate-800 rounded-t-2xl sm:rounded-2xl overflow-hidden flex flex-col shadow-2xl">

        {{-- En-tête --}}
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-800 shrink-0">
            <div class="min-w-0">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Demande de réservation</p>
                <h3 id="rsv-title" class="text-white font-semibold text-sm truncate"></h3>
            </div>
            <button onclick="closeReservationModal()"
                    class="shrink-0 w-9 h-9 rounded-full bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 flex items-center justify-center transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Corps --}}
        <div class="overflow-y-auto flex-1 p-5">

            {{-- Récap séjour --}}
            <div id="rsv-summary" class="mb-4 px-3 py-2.5 bg-orange-500/10 border border-orange-500/20 rounded-xl text-xs text-orange-300/90"></div>

            {{-- Formulaire --}}
            <form id="rsv-form" class="space-y-3">
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1">Nom complet</label>
                    <input type="text" name="full_name" required maxlength="255"
                           class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-slate-500 mb-1">E-mail</label>
                        <input type="email" name="email" required maxlength="255"
                               class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-500 mb-1">Téléphone</label>
                        <input type="tel" name="phone" maxlength="50"
                               class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1">Message (optionnel)</label>
                    <textarea name="message" rows="3" maxlength="2000"
                              class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition resize-none"
                              placeholder="Précisions sur votre demande..."></textarea>
                </div>

                <div id="rsv-error" class="hidden text-xs text-rose-400 bg-rose-500/10 border border-rose-500/25 rounded-lg px-3 py-2"></div>

                <button type="submit" id="rsv-submit-btn"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-orange-500 hover:bg-orange-400 text-black font-bold rounded-xl transition text-sm">
                    <i class="fas fa-paper-plane"></i>Envoyer la demande
                </button>
            </form>

            {{-- État succès --}}
            <div id="rsv-success" class="hidden text-center py-6">
                <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mb-3">
                    <i class="fas fa-check text-emerald-400 text-xl"></i>
                </div>
                <p class="text-white font-semibold text-sm mb-1">Demande envoyée</p>
                <p id="rsv-success-msg" class="text-slate-400 text-xs"></p>
            </div>
        </div>
    </div>
</div>

<style>
/* Forcer le thème sombre sur le picker natif */
.bk-date-input::-webkit-calendar-picker-indicator { filter: invert(.7) sepia(1) hue-rotate(10deg) saturate(.8); cursor: pointer; }
.bk-date-input::-webkit-datetime-edit { color: #44413a; }
</style>

<script>
(function () {
    let bkRooms  = 1;
    let bkGuests = 2;

    const fmt     = n => new Intl.NumberFormat('fr-FR').format(n);
    const fmtDate = d => { const [y,m,j] = d.split('-'); return j+'/'+m+'/'+y; };

    // Registre des données de chambre pour le modal (clé → données)
    const _rooms = {};

    // Registre du HTML de la grille de chambres par hébergement (accId → html)
    const _roomsLists = {};

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
                    + (i === 0 ? 'border-orange-500' : 'border-transparent opacity-60 hover:opacity-100') + '">'
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
        if (r.max_adults)   meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-user text-orange-400/70"></i>' + r.max_adults   + ' adulte'  + (r.max_adults   > 1 ? 's' : '') + ' max</span>');
        if (r.max_children) meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-child text-orange-400/70"></i>' + r.max_children + ' enfant' + (r.max_children > 1 ? 's' : '') + ' max</span>');
        if (r.area_m2)      meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-vector-square text-orange-400/70"></i>' + r.area_m2 + ' m²</span>');
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
                    + '<i class="fas fa-check text-orange-400/60 text-[8px]"></i>' + a + '</span>';
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

    // ── Modal liste des chambres disponibles ───────────────────────────────
    window.openRoomsListModal = function (accId) {
        const d = _roomsLists[accId];
        if (!d) return;
        document.getElementById('rlm-acc-name').textContent = d.name || '';
        document.getElementById('rlm-body').innerHTML = d.html || '';
        document.getElementById('rooms-list-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeRoomsListModal = function () {
        document.getElementById('rooms-list-modal').style.display = 'none';
        document.body.style.overflow = '';
    };

    // ── Modal demande de réservation ───────────────────────────────────────
    let _rsvCurrent = null;

    window.openReservationModal = function (key) {
        const d = _rooms[key];
        if (!d) return;
        _rsvCurrent = { key, d };

        const { r, nights, ci, co, accId, accName, rooms, guests } = d;

        document.getElementById('rsv-title').textContent = (accName || '') + ' — ' + (r.name || 'Chambre');

        const ppn = parseInt(r.price_xof || 0);
        const total = ppn ? ppn * nights * rooms : 0;
        document.getElementById('rsv-summary').innerHTML =
            '<i class="fas fa-calendar-check mr-1.5"></i>' + fmtDate(ci) + ' → ' + fmtDate(co)
            + '&nbsp;&nbsp;·&nbsp;&nbsp;' + nights + ' nuit' + (nights > 1 ? 's' : '')
            + '&nbsp;&nbsp;·&nbsp;&nbsp;' + rooms + ' ch. · ' + guests + ' pers.'
            + (total ? '<br><span class="text-white font-semibold">' + fmt(total) + ' XOF</span> estimé' : '');

        const form = document.getElementById('rsv-form');
        form.reset();
        form.classList.remove('hidden');
        document.getElementById('rsv-error').classList.add('hidden');
        document.getElementById('rsv-success').classList.add('hidden');

        const btn = document.getElementById('rsv-submit-btn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i>Envoyer la demande';

        document.getElementById('reservation-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeReservationModal = function () {
        document.getElementById('reservation-modal').style.display = 'none';
        document.body.style.overflow = '';
        _rsvCurrent = null;
    };

    document.getElementById('rsv-form').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!_rsvCurrent) return;

        const { d } = _rsvCurrent;
        const { r, nights, ci, co, accId, rooms } = d;
        const form = e.target;
        const errorEl = document.getElementById('rsv-error');
        const btn = document.getElementById('rsv-submit-btn');

        errorEl.classList.add('hidden');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Envoi...';

        const payload = {
            accommodation_id: accId,
            room_name: r.name || 'Chambre',
            room_price_xof: r.price_xof || null,
            check_in: ci,
            check_out: co,
            rooms_count: rooms,
            guests_count: d.guests,
            full_name: form.full_name.value,
            email: form.email.value,
            phone: form.phone.value,
            message: form.message.value,
        };

        fetch('{{ route('reservations.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok) {
                    const msg = result.data && result.data.errors
                        ? Object.values(result.data.errors).flat().join(' ')
                        : (result.data && result.data.message) || 'Une erreur est survenue. Veuillez réessayer.';
                    throw new Error(msg);
                }
                form.classList.add('hidden');
                document.getElementById('rsv-success-msg').textContent = result.data.message || 'Votre demande a bien été envoyée.';
                document.getElementById('rsv-success').classList.remove('hidden');
            })
            .catch(function (err) {
                errorEl.textContent = err.message || 'Une erreur est survenue. Veuillez réessayer.';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i>Envoyer la demande';
            });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        window.closeRoomModal();
        window.closeRoomsListModal();
        window.closeReservationModal();
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
                + '<span class="text-orange-600/70"><i class="fas fa-exclamation-circle mr-1"></i>'
                + 'Aucune chambre pour ' + gPR + ' adulte' + (gPR > 1 ? 's' : '') + '/chambre.</span><br>'
                + '<span class="text-slate-600 text-[10px]">Ajustez le nombre de personnes ou de chambres.</span></div>';
            return;
        }

        // Lien officiel ou premier lien disponible pour le bouton "Réserver"
        const bkLink = links.find(l => l.is_official) || links[0] || null;
        const accId   = card.dataset.accId || '';
        const accName = card.dataset.accName || '';

        let html = '<div class="flex flex-wrap gap-5">';

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
            _rooms[key] = { r, bookUrl, nights, ci, co, accCoverUrl, accId, accName, rooms: bkRooms, guests: bkGuests };

            let photos = Array.isArray(r.photos) ? r.photos.filter(Boolean) : [];
            const thumbSrc = photos[0] || r.thumbnail || accCoverUrl || '';

            const metaItems = [];
            if (r.max_adults)   metaItems.push('<i class="fas fa-user text-[8px] mr-0.5"></i>' + r.max_adults);
            if (r.max_children) metaItems.push('<i class="fas fa-child text-[8px] mr-0.5"></i>' + r.max_children);
            if (r.area_m2)      metaItems.push('<i class="fas fa-vector-square text-[8px] mr-0.5"></i>' + r.area_m2 + 'm²');

            html +=
                '<div class="w-full sm:w-[300px] bg-slate-800/50 border border-slate-700/60 rounded-xl overflow-hidden flex flex-col">'
                +   '<button type="button" onclick="openRoomModal(\'' + key + '\')" class="relative h-44 w-full bg-green-900 block">'
                +     (thumbSrc
                        ? '<img src="' + thumbSrc + '" alt="' + (r.name || 'Chambre') + '" loading="lazy" class="w-full h-full object-cover">'
                        : '<div class="w-full h-full flex items-center justify-center"><i class="fas fa-bed text-slate-700 text-3xl"></i></div>')
                +     '<span class="absolute top-2.5 left-2.5 inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-500/90 text-black text-xs font-bold rounded-full">'
                +       '<span class="w-1.5 h-1.5 rounded-full bg-green-950/70 inline-block"></span>Dispo'
                +     '</span>'
                +   '</button>'
                +   '<div class="p-4 flex flex-col flex-1">'
                +     '<span class="text-white text-base font-semibold leading-tight line-clamp-1 mb-2">' + (r.name || 'Chambre') + '</span>'
                +     (metaItems.length
                        ? '<div class="flex flex-wrap gap-3 text-xs text-slate-500 mb-3">'
                          + metaItems.map(function(i){ return '<span>' + i + '</span>'; }).join('')
                          + '</div>'
                        : '')
                +     (ppn
                          ? '<div class="mb-3">'
                            + '<div class="text-white text-lg font-bold leading-tight">' + fmt(ppn) + '<span class="text-xs font-normal text-slate-500"> XOF/nuit</span></div>'
                            + (totalXof ? '<div class="text-orange-400/80 text-xs leading-tight">' + fmt(totalXof) + ' XOF total</div>' : '')
                            + '</div>'
                          : '')
                +     '<div class="mt-auto space-y-2">'
                +       (bookUrl !== '#'
                            ? '<a href="' + bookUrl + '" target="_blank" rel="noopener nofollow"'
                              + ' class="block text-center px-4 py-2.5 bg-orange-500 hover:bg-orange-400 text-black text-sm font-bold rounded-lg transition">'
                              + '<i class="fas fa-calendar-check text-xs mr-1.5"></i>Reserver'
                              + '</a>'
                            : '')
                +       '<button type="button" onclick="openReservationModal(\'' + key + '\')"'
                +         ' class="w-full text-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition">'
                +         '<i class="fas fa-paper-plane text-xs mr-1.5"></i>Demander cette chambre'
                +         '</button>'
                +     '</div>'
                +   '</div>'
                + '</div>';
        });

        html += '</div>';

        _roomsLists[accId] = { name: accName, html: html };

        panel.innerHTML =
            '<div class="mt-3 pt-3 border-t border-slate-800/80">'
            + '<button type="button" onclick="openRoomsListModal(\'' + accId + '\')"'
            + ' class="w-full flex items-center justify-between gap-2 px-3 py-2.5 bg-emerald-500/10 border border-emerald-500/25 rounded-xl text-left transition hover:bg-emerald-500/15">'
            +   '<span class="flex items-center gap-1.5 text-emerald-400 text-xs font-semibold">'
            +     '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>'
            +     avail.length + ' chambre' + (avail.length > 1 ? 's' : '') + ' disponible' + (avail.length > 1 ? 's' : '')
            +   '</span>'
            +   '<span class="text-slate-400 text-[10px] font-medium">Voir <i class="fas fa-chevron-right text-[8px] ml-0.5"></i></span>'
            + '</button>'
            + '</div>';
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
