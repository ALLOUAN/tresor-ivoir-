<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $accommodation->name }} — Résidences &amp; Hôtels — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/fr.js"></script>
    <style>
        /* ── Calendrier de disponibilité (chambre) — thème aux couleurs du site ── */
        .flatpickr-calendar { box-shadow: 0 20px 45px rgba(20,18,14,0.18); border-radius: 1rem; font-family: 'Inter', sans-serif; }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #f2790f; border-color: #f2790f; color: #fff;
        }
        .flatpickr-day.inRange {
            background: rgba(242,121,15,0.14); border-color: rgba(242,121,15,0.14); box-shadow: -5px 0 0 rgba(242,121,15,0.14), 5px 0 0 rgba(242,121,15,0.14);
        }
        .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover { background: #d4630a; border-color: #d4630a; }
        .flatpickr-day.today { border-color: #f2790f; }
        .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
            color: #cfc7b8; text-decoration: line-through; cursor: not-allowed;
        }
        .bkp-date-loading { position: relative; }
        .bkp-date-loading::after {
            content: ''; position: absolute; inset: 0; border-radius: .75rem;
            background: repeating-linear-gradient(45deg, rgba(0,0,0,.03) 0, rgba(0,0,0,.03) 8px, transparent 8px, transparent 16px);
            pointer-events: none;
        }
    </style>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .reveal { opacity: 0; transform: translateY(26px); transition: opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1); }
        .reveal.is-visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .reveal, .reveal.is-visible { opacity:1!important; transform:none!important; transition:none!important; } }

        .btn-primary {
            background: #f2790f;
            box-shadow: 0 12px 28px rgba(242,121,15,0.35);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
            color: #1b1408;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .btn-primary:disabled { transform:none!important; filter:none!important; }

        .hero-cover { position: relative; overflow: hidden; background: #14130f; }
        .hero-cover img { transition: transform .6s ease; }
        .hero-cover:hover img { transform: scale(1.05); }
        .glass-arrow { border: 1px solid rgba(255,255,255,0.18); background: rgba(20,18,14,0.35); backdrop-filter: blur(10px); transition: transform .2s ease, background .2s ease; }
        .glass-arrow:hover { background: rgba(20,18,14,0.6); transform: scale(1.06); }
        .gallery-tile { position: relative; overflow: hidden; cursor: pointer; border-radius: .85rem; }
        .gallery-tile img { transition: transform .4s ease; }
        .gallery-tile:hover img { transform: scale(1.08); }

        .bento-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
        }
        .bento-icon {
            width: 2.2rem; height: 2.2rem; border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, rgba(242,121,15,0.18), rgba(242,121,15,0.06));
            color: #d4630a;
        }
        .section-kicker { letter-spacing: .22em; }
        .review-card { border: 1px solid rgba(0,0,0,0.07); background: #ffffff; box-shadow: 0 8px 22px rgba(20,18,12,0.05); }
        .related-tile { border: 1px solid rgba(0,0,0,0.07); background: #ffffff; box-shadow: 0 10px 26px rgba(20,18,12,0.06); transition: transform .25s ease, box-shadow .25s ease; }
        .related-tile:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(194,94,10,0.16); }

        .booking-card { border: 1px solid rgba(0,0,0,0.08); background: linear-gradient(180deg, #ffffff, #fbf7ee); box-shadow: 0 20px 50px rgba(20,18,12,0.1); }
        .bkp-room-card { transition: transform .18s ease, border-color .18s ease, background .18s ease; }
        .bkp-room-card:hover { transform: translateY(-2px); }
        .bkp-room-card.is-selected { border-color: rgba(242,121,15,0.55) !important; background: rgba(242,121,15,0.07) !important; box-shadow: 0 10px 22px rgba(242,121,15,0.14); }
        .bkp-pay-method { transition: border-color .18s ease, background .18s ease; }
        .bkp-pay-method.is-active { border-color: rgba(242,121,15,0.55) !important; background: rgba(242,121,15,0.07) !important; }

        .mobile-cta-bar {
            border-top: 1px solid rgba(0,0,0,0.08); background: rgba(255,255,255,0.92); backdrop-filter: blur(14px);
            box-shadow: 0 -10px 26px rgba(20,18,12,0.1); padding-bottom: env(safe-area-inset-bottom, 0);
        }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('accommodations.index') }}" class="inline-flex items-center gap-2 text-[#c25e0a] hover:text-[#a24d08] transition text-sm font-semibold">
            <i class="fas fa-arrow-left text-[11px]"></i> Résidences &amp; Hôtels
        </a>

        @php
            $photos = $accommodation->photos;
            $mainPhoto = $photos->first()?->url ?: $accommodation->cover_image;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start mt-4">
            <div class="lg:col-span-3 space-y-14">

                {{-- ── GALERIE ────────────────────────────────────────────── --}}
                @php
                    $heroGalleryItems = collect();
                    if ($mainPhoto) {
                        $heroGalleryItems->push(['type' => 'photo', 'url' => $mainPhoto]);
                    }
                    foreach ($photos->skip(1)->take(4) as $p) {
                        $heroGalleryItems->push(['type' => 'photo', 'url' => $p->url]);
                    }
                    foreach ($accommodation->videos as $v) {
                        $heroGalleryItems->push(['type' => 'video', 'embedUrl' => $v->embedUrl(), 'url' => $v->url]);
                    }
                @endphp
                <script id="hero-gallery-json" type="application/json">@json($heroGalleryItems->values())</script>
                <div class="space-y-3 reveal is-visible">
                    <div class="hero-cover rounded-3xl relative" id="hero-cover" style="height:clamp(320px,55vh,520px)">
                        @if($mainPhoto)
                            <img id="hero-cover-img" src="{{ $mainPhoto }}" alt="{{ $accommodation->name }}"
                                 class="w-full h-full object-cover cursor-zoom-in" loading="lazy"
                                 onclick="openLightbox(this.src)">
                            <span id="hero-zoom-hint" class="zoom-hint absolute bottom-4 right-4 w-10 h-10 rounded-full bg-black/55 border border-white/20 backdrop-blur flex items-center justify-center text-white pointer-events-none">
                                <i class="fas fa-magnifying-glass-plus text-sm"></i>
                            </span>
                        @else
                            <div id="hero-cover-empty" class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-hotel text-4xl"></i></div>
                        @endif
                        <div id="hero-cover-video-wrap" class="absolute inset-0 hidden">
                            <iframe id="hero-cover-video-iframe" src="" class="w-full h-full hidden" loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            <video id="hero-cover-video-native" src="" controls preload="metadata" class="w-full h-full object-cover hidden"></video>
                        </div>
                        <div class="absolute top-5 left-5 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3.5 py-1.5 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur">
                                {{ $accommodation->type_label }}
                            </span>
                            @if($accommodation->stars)
                            <span class="inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3.5 py-1.5 text-[10px] text-orange-100 font-bold backdrop-blur">
                                {{ str_repeat('★', $accommodation->stars) }}
                            </span>
                            @endif
                        </div>

                        @if($heroGalleryItems->count() > 1)
                        <button onclick="heroSlide(-1)" class="glass-arrow absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-3 pr-4 rounded-full text-white z-10 active:scale-95">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span class="text-[11px] text-white/60 hidden sm:inline">Préc.</span>
                        </button>
                        <button onclick="heroSlide(+1)" class="glass-arrow absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-4 pr-3 rounded-full text-white z-10 active:scale-95">
                            <span class="text-[11px] text-white/60 hidden sm:inline">Suiv.</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                        @endif
                    </div>

                    @if($photos->count() > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($photos->skip(1)->take(4) as $i => $p)
                        <div class="gallery-tile aspect-square" onclick="showHeroItem({{ $i + 1 }})">
                            <img src="{{ $p->url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($accommodation->videos->isNotEmpty())
                    @php $videoIndexOffset = 1 + min($photos->count() - 1, 4); @endphp
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($accommodation->videos as $i => $v)
                        <div class="gallery-tile aspect-square relative" onclick="showHeroItem({{ $videoIndexOffset + $i }})">
                            @if($v->videoThumbnailUrl())
                                <img src="{{ $v->videoThumbnailUrl() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full bg-black/80"></div>
                            @endif
                            <span class="absolute inset-0 flex items-center justify-center bg-black/35">
                                <i class="fas fa-play text-white text-lg"></i>
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- ── EN-TÊTE + DESCRIPTION ──────────────────────────────── --}}
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">{{ $accommodation->type_label }}</p>
                    <h1 class="font-serif text-3xl sm:text-4xl font-bold mb-2">{{ $accommodation->name }}</h1>
                    <p class="text-[#8a7f6b] text-sm mb-6">
                        <i class="fas fa-location-dot text-[#d4630a]/70 mr-1"></i>
                        {{ $accommodation->city?->name }}{{ $accommodation->city?->region_administrative ? ' · '.$accommodation->city->region_administrative : '' }}
                        @if($accommodation->provider && $accommodation->provider->rating_avg)
                        <span class="ml-2 inline-flex items-center gap-1 text-[#c25e0a] font-bold"><i class="fas fa-star text-[10px]"></i> {{ number_format((float) $accommodation->provider->rating_avg, 1) }}</span>
                        @endif
                    </p>

                    @if($accommodation->short_description)
                    <p class="text-[#3d372c] text-base font-medium mb-3">{{ $accommodation->short_description }}</p>
                    @endif
                    <p class="text-[#3d372c] leading-relaxed text-[15px] whitespace-pre-line">{{ $accommodation->description ?: 'Description non disponible.' }}</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
                        @if($accommodation->check_in_time)
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-right-to-bracket text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Arrivée</p>
                            <p class="text-sm font-bold mt-0.5">{{ $accommodation->check_in_time }}</p>
                        </div>
                        @endif
                        @if($accommodation->check_out_time)
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-right-from-bracket text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Départ</p>
                            <p class="text-sm font-bold mt-0.5">{{ $accommodation->check_out_time }}</p>
                        </div>
                        @endif
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-city text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Ville</p>
                            <p class="text-sm font-bold mt-0.5">{{ $accommodation->city?->name ?: 'N/A' }}</p>
                        </div>
                        <div class="bento-card rounded-xl p-3.5">
                            <div class="bento-icon mb-2"><i class="fas fa-phone text-[11px]"></i></div>
                            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Contact</p>
                            <p class="text-sm font-bold mt-0.5 truncate">{{ $accommodation->phone ?: 'Via réservation' }}</p>
                        </div>
                    </div>
                </section>

                {{-- ── ÉQUIPEMENTS ────────────────────────────────────────── --}}
                @if(!empty($accommodation->amenities))
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Confort</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">Équipements &amp; services</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($accommodation->amenities as $am)
                        <div class="flex items-center gap-2.5 bento-card rounded-xl p-3">
                            <i class="{{ $am['icon'] ?? 'fas fa-check' }} text-[#d4630a] text-sm shrink-0"></i>
                            <span class="text-sm font-medium truncate">{{ $am['label'] ?? '' }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ── CHAMBRES ───────────────────────────────────────────── --}}
                @if(!empty($accommodation->room_types))
                <section class="reveal" id="chambres-disponibles">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Hébergement</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">Chambres &amp; logements disponibles</h2>
                    <div class="space-y-3">
                        @foreach($accommodation->room_types as $room)
                        @php $roomPhotos = array_values(array_filter((array) ($room['photos'] ?? []))); @endphp
                        <div class="bento-card rounded-2xl p-4">
                            <div class="flex items-center gap-4">
                                @if(!empty($roomPhotos))
                                <div class="flex gap-1.5 shrink-0">
                                    @foreach(array_slice($roomPhotos, 0, 3) as $ri => $photoUrl)
                                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-black/5 cursor-zoom-in" onclick="openLightbox('{{ $photoUrl }}')">
                                        <img src="{{ $photoUrl }}" alt="" class="w-full h-full object-cover">
                                        @if($ri === 2 && count($roomPhotos) > 3)
                                        <div class="absolute inset-0 bg-black/55 flex items-center justify-center text-white text-xs font-bold">
                                            +{{ count($roomPhotos) - 3 }}
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold">{{ $room['name'] ?? 'Chambre' }}</p>
                                    <p class="text-[#8a7f6b] text-xs mt-1">
                                        <i class="fas fa-user mr-1"></i>{{ $room['max_adults'] ?? 2 }} pers. max
                                        @if(!empty($room['beds'])) · <i class="fas fa-bed mr-0.5"></i>{{ $room['beds'] }} @endif
                                        @if(!empty($room['area_m2'])) · {{ $room['area_m2'] }} m² @endif
                                        @if(!empty($room['amenities'])) · {{ is_array($room['amenities']) ? implode(', ', $room['amenities']) : $room['amenities'] }} @endif
                                    </p>
                                    @if(!empty($room['description']))
                                    <p class="text-[#5c5548] text-xs mt-1.5 leading-relaxed">{{ $room['description'] }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-[#c25e0a] font-extrabold text-sm whitespace-nowrap">
                                        {{ number_format((int) ($room['price_xof'] ?? 0), 0, ',', ' ') }} XOF<span class="text-[#8a7f6b] font-normal text-xs">/nuit</span>
                                    </p>
                                    @if(!empty($room['price_xof']))
                                    <button type="button" onclick="reserveThisRoom('{{ addslashes($room['name'] ?? 'Chambre') }}')"
                                            class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#c25e0a]/10 hover:bg-[#c25e0a]/20 text-[#c25e0a] text-[11px] font-bold transition whitespace-nowrap">
                                        <i class="fas fa-bed text-[10px]"></i> Réserver cette chambre
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($room['conditions']))
                            <p class="text-[#8a7f6b] text-[11px] mt-2.5 pt-2.5 border-t border-black/5 leading-relaxed">
                                <i class="fas fa-circle-info mr-1"></i>{{ $room['conditions'] }}
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('accommodations.rooms', $accommodation->slug) }}"
                       class="inline-flex items-center gap-1.5 mt-4 text-[#c25e0a] text-xs font-bold hover:text-[#a24d08] transition">
                        Voir sur une page dédiée <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </section>
                @endif

                {{-- ── CONDITIONS ─────────────────────────────────────────── --}}
                @if($accommodation->cancellation_policy)
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">À savoir</p>
                    <h2 class="font-serif text-2xl font-bold mb-4">Conditions de réservation &amp; d'annulation</h2>
                    <div class="bento-card rounded-2xl p-5 text-[#3d372c] text-sm leading-relaxed whitespace-pre-line">{{ $accommodation->cancellation_policy }}</div>
                </section>
                @endif

                {{-- ── LOCALISATION (floutée avant réservation payée) ─────── --}}
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Où se trouve l'établissement</p>
                    <h2 class="font-serif text-2xl font-bold mb-4">Localisation</h2>
                    @if($accommodation->city?->name)
                    <div class="bento-card rounded-2xl overflow-hidden mb-3">
                        <iframe src="https://www.google.com/maps?q={{ urlencode($accommodation->city->name) }}&z=12&output=embed"
                                class="w-full" style="height:220px; border:0;" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    @endif
                    <div class="bento-card rounded-2xl p-4 flex items-start gap-3">
                        <div class="bento-icon shrink-0"><i class="fas fa-lock text-[11px]"></i></div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">Adresse exacte et itinéraire précis</p>
                            <p class="text-[#8a7f6b] text-xs mt-1 leading-relaxed">
                                Pour des raisons de sécurité, l'adresse précise n'est communiquée qu'aux voyageurs ayant réservé et payé l'acompte en ligne. Elle apparaîtra automatiquement sur votre page de confirmation.
                            </p>
                            <a href="{{ route('accommodations.rooms', $accommodation->slug) }}" class="inline-flex items-center gap-1.5 mt-2 text-[#c25e0a] text-xs font-bold hover:text-[#a24d08] transition">
                                Réserver pour débloquer <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </section>

                {{-- ── AVIS ────────────────────────────────────────────────── --}}
                @if($reviews->isNotEmpty())
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Retours d'expérience</p>
                    <h2 class="font-serif text-2xl font-bold mb-5">Avis clients</h2>
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                        <div class="review-card rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-semibold text-sm">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }}</p>
                                <span class="text-[#c25e0a] text-sm font-bold">{{ $review->rating }} ★</span>
                            </div>
                            @if($review->title)<p class="text-sm font-medium">{{ $review->title }}</p>@endif
                            <p class="text-[#5c5548] text-sm mt-1">{{ $review->comment }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            {{-- ═══ SIDEBAR RÉSERVATION (sticky) ═══════════════════════════ --}}
            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24 space-y-3 reveal" id="booking-anchor">
                    @if(!empty($accommodation->room_types))
                    @php
                        $depositPercent = app(\App\Services\ReservationPricingService::class)->depositPercent();
                        $cheapestRoom = collect($accommodation->room_types)->min('price_xof');
                        $canBook = auth()->check() && auth()->user()->hasVerifiedEmail();
                        // Lecture seule : ne crée pas de wallet juste pour afficher un solde à 0.
                        $walletBalanceXof = $canBook ? (int) (\App\Models\Wallet::where('user_id', auth()->id())->value('balance_available_xof') ?? 0) : 0;
                    @endphp
                    <div class="booking-card rounded-3xl p-5 relative" id="booking-module" data-deposit-percent="{{ $depositPercent }}">
                        <h2 class="font-serif text-xl font-bold mb-1">Réserver une chambre</h2>
                        <p class="text-[#8a7f6b] text-xs mb-4">Acompte de {{ rtrim(rtrim(number_format($depositPercent, 1), '0'), '.') }}% payé en ligne · solde réglé sur place à l'hôtel.</p>

                        @unless($canBook)
                        <div class="absolute inset-0 z-20 rounded-3xl bg-white/90 backdrop-blur-[2px] flex flex-col items-center justify-center text-center px-6 py-8">
                            <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center mb-3">
                                <i class="fas fa-lock text-[#c25e0a]"></i>
                            </div>
                            @auth
                                <p class="text-[#1c1915] font-bold text-sm mb-1">Vérifiez votre e-mail pour réserver</p>
                                <p class="text-[#8a7f6b] text-xs mb-4 max-w-[220px]">Un code de vérification vous a été envoyé. Confirmez-le pour finaliser vos réservations.</p>
                                <a href="{{ route('verification.notice') }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs">
                                    <i class="fas fa-envelope-circle-check text-xs"></i> Vérifier mon e-mail
                                </a>
                            @else
                                <p class="text-[#1c1915] font-bold text-sm mb-1">Connectez-vous pour réserver</p>
                                <p class="text-[#8a7f6b] text-xs mb-4 max-w-[220px]">La création d'un compte est nécessaire avant toute réservation d'hôtel ou de résidence.</p>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                        <i class="fas fa-right-to-bracket text-xs"></i> Se connecter
                                    </a>
                                    <a href="{{ route('register', ['role' => 'visitor', 'redirect' => url()->current()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-orange-400/50 text-[#1c1915] transition">
                                        Créer un compte
                                    </a>
                                </div>
                            @endauth
                        </div>
                        @endunless

                        <div class="{{ $canBook ? '' : 'pointer-events-none select-none' }}">
                        <div class="grid grid-cols-1 gap-3 mb-5">
                            @foreach($accommodation->room_types as $room)
                            <button type="button"
                                    class="bkp-room-card text-left rounded-2xl border border-black/8 bg-white hover:border-orange-400/50 p-3.5 flex items-center gap-3"
                                    data-room-id="{{ $room['id'] ?? '' }}"
                                    data-room-name="{{ $room['name'] ?? 'Chambre' }}"
                                    data-room-price="{{ (int) ($room['price_xof'] ?? 0) }}">
                                @if(!empty($room['thumbnail']))
                                <div class="w-16 h-16 shrink-0 rounded-xl overflow-hidden bg-black/5">
                                    <img src="{{ $room['thumbnail'] }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[#1c1915] text-sm font-semibold truncate">{{ $room['name'] ?? 'Chambre' }}</p>
                                    <p class="text-[#8a7f6b] text-xs mt-0.5">
                                        <i class="fas fa-user text-[#d4630a]/70 mr-1"></i>{{ $room['max_adults'] ?? 2 }} pers. max
                                        @if(!empty($room['area_m2'])) · {{ $room['area_m2'] }} m² @endif
                                    </p>
                                    <p class="text-[#c25e0a] text-sm font-bold mt-1">
                                        {{ number_format((int) ($room['price_xof'] ?? 0), 0, ',', ' ') }} XOF<span class="text-[#8a7f6b] font-normal text-xs">/nuit</span>
                                    </p>
                                </div>
                            </button>
                            @endforeach
                        </div>

                        {{-- Placé AVANT les champs de date : le calendrier s'ouvre juste en
                             dessous d'eux et recouvrirait ce message s'il était positionné
                             après (constaté : invisible une fois le calendrier ouvert). --}}
                        <p id="bkp-dates-hint" class="text-sm text-[#5c5548] font-medium leading-snug mb-2 flex items-start gap-2">
                            <i class="fas fa-calendar-days text-[#c25e0a] mt-0.5"></i>
                            <span>Choisissez d'abord une chambre pour voir ses disponibilités.</span>
                        </p>
                        <div id="bkp-dates-legend" class="hidden items-center gap-2.5 mb-3 flex-wrap">
                            <span class="inline-flex items-center gap-2 pl-1.5 pr-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                                <span class="w-5 h-5 rounded-md border border-emerald-300 bg-white flex items-center justify-center text-[10px] font-bold text-emerald-700">12</span>
                                <i class="fas fa-circle-check text-emerald-500"></i>
                                Disponible
                            </span>
                            <span class="inline-flex items-center gap-2 pl-1.5 pr-3 py-1 rounded-full bg-red-50 border border-red-200 text-xs font-semibold text-red-600">
                                <span class="w-5 h-5 rounded-md bg-red-100 border border-red-200 flex items-center justify-center text-[10px] font-bold text-red-400 line-through">12</span>
                                <i class="fas fa-ban text-red-400"></i>
                                Indisponible
                            </span>
                        </div>
                        <div id="bkp-dates-wrap" class="grid grid-cols-2 gap-2.5 mb-3">
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#8a7f6b] mb-1">Arrivée</label>
                                <input type="text" id="bkp-checkin" readonly placeholder="Sélectionner"
                                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2 text-sm cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#8a7f6b] mb-1">Départ</label>
                                <input type="text" id="bkp-checkout" readonly placeholder="Sélectionner"
                                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2 text-sm cursor-pointer">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 mb-4">
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#8a7f6b] mb-1">Chambres</label>
                                <div class="flex items-center bg-white border border-black/10 rounded-xl overflow-hidden">
                                    <button type="button" class="bkp-step w-9 h-9 text-[#8a7f6b] hover:text-[#c25e0a]" data-target="bkp-rooms" data-step="-1">−</button>
                                    <span id="bkp-rooms" class="flex-1 text-center text-sm font-semibold" data-value="1">1</span>
                                    <button type="button" class="bkp-step w-9 h-9 text-[#8a7f6b] hover:text-[#c25e0a]" data-target="bkp-rooms" data-step="1">+</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#8a7f6b] mb-1">Voyageurs</label>
                                <div class="flex items-center bg-white border border-black/10 rounded-xl overflow-hidden">
                                    <button type="button" class="bkp-step w-9 h-9 text-[#8a7f6b] hover:text-[#c25e0a]" data-target="bkp-guests" data-step="-1">−</button>
                                    <span id="bkp-guests" class="flex-1 text-center text-sm font-semibold" data-value="2">2</span>
                                    <button type="button" class="bkp-step w-9 h-9 text-[#8a7f6b] hover:text-[#c25e0a]" data-target="bkp-guests" data-step="1">+</button>
                                </div>
                            </div>
                        </div>

                        <div id="bkp-breakdown" class="hidden rounded-2xl border border-orange-400/25 bg-orange-50 px-4 py-3.5 mb-4 text-sm">
                            <div class="flex items-center justify-between text-[#5c5548] mb-1">
                                <span id="bkp-nights-label"></span>
                                <span id="bkp-total" class="text-[#1c1915] font-medium"></span>
                            </div>
                            <div class="flex items-center justify-between pt-1.5 mt-1.5 border-t border-orange-400/20">
                                <span class="text-[#3d372c]">Acompte à payer maintenant</span>
                                <span id="bkp-deposit" class="text-[#c25e0a] font-bold"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2.5 mb-3">
                            <input type="text" id="bkp-full-name" placeholder="Nom complet" value="{{ $canBook ? auth()->user()->full_name : '' }}" {{ $canBook ? 'readonly' : '' }} class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm placeholder:text-[#a89f8f] {{ $canBook ? 'bg-black/[0.03] text-[#5c5548]' : '' }}">
                            <input type="tel" id="bkp-phone" placeholder="Téléphone" value="{{ $canBook ? auth()->user()->phone : '' }}" {{ $canBook && auth()->user()->phone ? 'readonly' : '' }} class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm placeholder:text-[#a89f8f] {{ $canBook && auth()->user()->phone ? 'bg-black/[0.03] text-[#5c5548]' : '' }}">
                            <input type="email" id="bkp-email" placeholder="Email" value="{{ $canBook ? auth()->user()->email : '' }}" {{ $canBook ? 'readonly' : '' }} class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm placeholder:text-[#a89f8f] {{ $canBook ? 'bg-black/[0.03] text-[#5c5548]' : '' }}">
                        </div>

                        @if($canBook)
                        <div class="mb-3">
                            <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#8a7f6b] mb-1.5">Moyen de paiement</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" id="bkp-pay-cinetpay" data-method="cinetpay"
                                        class="bkp-pay-method is-active text-left rounded-xl border-2 border-black/8 bg-white px-3 py-2.5">
                                    <span class="block text-[#1c1915] text-xs font-bold"><i class="fas fa-mobile-screen mr-1"></i>CinetPay</span>
                                    <span class="block text-[#8a7f6b] text-[10px] mt-0.5">Mobile Money / Carte</span>
                                </button>
                                <button type="button" id="bkp-pay-wallet" data-method="wallet"
                                        class="bkp-pay-method text-left rounded-xl border-2 border-black/8 bg-white px-3 py-2.5">
                                    <span class="block text-[#1c1915] text-xs font-bold"><i class="fas fa-wallet mr-1"></i>Mon solde Wallet</span>
                                    <span class="block text-[#8a7f6b] text-[10px] mt-0.5">{{ number_format($walletBalanceXof, 0, ',', ' ') }} XOF disponible</span>
                                </button>
                            </div>
                        </div>
                        @endif

                        <div id="bkp-error" class="hidden mb-3 px-3 py-2 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs"></div>

                        <button type="button" id="bkp-submit" disabled
                                class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-4 rounded-2xl font-extrabold text-[15px] disabled:opacity-50 disabled:pointer-events-none">
                            <i class="fas fa-lock text-sm"></i>
                            <span id="bkp-submit-label">Sélectionnez une chambre</span>
                        </button>
                        <p class="text-center text-xs text-[#a89f8f] py-1 mt-2">Paiement sécurisé via CinetPay · solde réglé sur place</p>
                        </div>
                    </div>
                    @elseif($accommodation->provider && ($accommodation->provider->reserve_url || $accommodation->provider->website))
                    <div class="booking-card rounded-3xl p-5">
                        <h2 class="font-serif text-xl font-bold mb-1">Contacter cet établissement</h2>
                        <p class="text-[#8a7f6b] text-xs mb-4">Réservation directement auprès du prestataire.</p>
                        <a href="{{ $accommodation->provider->reserve_url ?: $accommodation->provider->website }}"
                           target="_blank" rel="noopener noreferrer"
                           class="btn-primary btn-shine flex items-center justify-between w-full px-5 py-4 rounded-2xl font-extrabold text-[15px] group">
                            <span class="flex items-center gap-2.5"><i class="fas fa-hotel text-sm"></i> Effectuer une réservation</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($related->isNotEmpty())
        <section class="mt-16 reveal">
            <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Dans la même ville</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Autres hébergements</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($related as $r)
                <a href="{{ route('accommodations.show', $r->slug) }}" class="related-tile rounded-2xl p-4">
                    <p class="text-[#c25e0a] text-[11px] uppercase tracking-[0.16em] font-bold">{{ $r->type_label }}</p>
                    <p class="font-semibold mt-1.5 leading-snug">{{ $r->name }}</p>
                    <p class="text-[#8a7f6b] text-sm mt-1.5"><i class="fas fa-location-dot text-[#d4630a]/60 mr-1"></i>{{ $r->city?->name }}</p>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>

    @if(!empty($accommodation->room_types))
    <div class="mobile-cta-bar fixed bottom-0 inset-x-0 z-40 lg:hidden px-4 py-3 flex items-center gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">À partir de</p>
            <p class="font-bold text-sm truncate">{{ number_format((int) ($cheapestRoom ?? 0), 0, ',', ' ') }} XOF<span class="text-[#8a7f6b] font-normal text-xs">/nuit</span></p>
        </div>
        <a href="{{ route('accommodations.rooms', $accommodation->slug) }}" class="btn-primary btn-shine shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-extrabold text-xs">
            <i class="fas fa-bed"></i> Réserver
        </a>
    </div>
    @endif

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

    <div class="pb-20 lg:pb-0">
@include('partials.homepage-footer')
    </div>
@include('partials.image-protection')
<script>
function openLightbox(url) {
    const img = document.getElementById('lightbox-img');
    img.src = url;
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}

// ── Galerie héro : affiche la photo/vidéo cliquée dans le cadre principal ──
function setHeroPhoto(url) {
    const img = document.getElementById('hero-cover-img');
    const empty = document.getElementById('hero-cover-empty');
    const videoWrap = document.getElementById('hero-cover-video-wrap');
    const iframe = document.getElementById('hero-cover-video-iframe');
    const native = document.getElementById('hero-cover-video-native');

    videoWrap.classList.add('hidden');
    if (iframe) iframe.src = '';
    if (native) native.src = '';

    if (empty) empty.classList.add('hidden');
    if (img) {
        img.src = url;
        img.classList.remove('hidden');
        img.setAttribute('onclick', "openLightbox('" + url + "')");
    }
    document.getElementById('hero-zoom-hint')?.classList.remove('hidden');
}

function setHeroVideo(embedUrl, directUrl) {
    const img = document.getElementById('hero-cover-img');
    const empty = document.getElementById('hero-cover-empty');
    const videoWrap = document.getElementById('hero-cover-video-wrap');
    const iframe = document.getElementById('hero-cover-video-iframe');
    const native = document.getElementById('hero-cover-video-native');

    if (img) img.classList.add('hidden');
    if (empty) empty.classList.add('hidden');
    document.getElementById('hero-zoom-hint')?.classList.add('hidden');
    videoWrap.classList.remove('hidden');

    if (embedUrl) {
        iframe.src = embedUrl + '?autoplay=1&mute=1&muted=1&playsinline=1';
        iframe.classList.remove('hidden');
        native.classList.add('hidden');
        native.src = '';
    } else {
        native.src = directUrl;
        native.muted = true;
        native.classList.remove('hidden');
        iframe.classList.add('hidden');
        iframe.src = '';
        native.play?.().catch(() => {});
    }
}

// ── Défilement automatique de la galerie héro, sans clic ──────────────────
(function () {
    const jsonEl = document.getElementById('hero-gallery-json');
    if (!jsonEl) return;
    const items = JSON.parse(jsonEl.textContent);
    if (items.length <= 1) return;

    let heroIndex = 0;

    window.showHeroItem = function (i) {
        heroIndex = ((i % items.length) + items.length) % items.length;
        const item = items[heroIndex];
        if (item.type === 'video') {
            setHeroVideo(item.embedUrl, item.url);
        } else {
            setHeroPhoto(item.url);
        }
    };

    window.heroSlide = function (dir) {
        window.showHeroItem(heroIndex + dir);
    };

    setInterval(function () {
        window.showHeroItem(heroIndex + 1);
    }, 5000);
})();
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Booking module (chambre + acompte en ligne) ────────────────────────────
(function () {
    const module = document.getElementById('booking-module');
    if (!module) return;

    const depositPercent = parseFloat(module.dataset.depositPercent || '30');
    let selectedRoom = null;
    let paymentMethod = 'cinetpay';
    let blockedRanges = []; // périodes déjà réservées pour la chambre sélectionnée
    let availabilityToken = 0; // ignore les réponses obsolètes si l'utilisateur change vite de chambre

    const checkin = document.getElementById('bkp-checkin');
    const checkout = document.getElementById('bkp-checkout');
    const datesWrap = document.getElementById('bkp-dates-wrap');
    const datesHint = document.getElementById('bkp-dates-hint');
    const datesHintText = datesHint.querySelector('span'); // le <p> a aussi une icône, on ne touche qu'au texte
    const datesLegend = document.getElementById('bkp-dates-legend');
    const submitBtn = document.getElementById('bkp-submit');
    const submitLabel = document.getElementById('bkp-submit-label');
    const breakdown = document.getElementById('bkp-breakdown');
    const errorBox = document.getElementById('bkp-error');

    // Formatage local (pas toISOString(), qui décale selon le fuseau horaire local
    // — un décalage d'un jour ici recréerait exactement le bug qu'on cherche à éviter).
    function toISO(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function addDays(iso, n) {
        const d = new Date(iso + 'T00:00:00');
        d.setDate(d.getDate() + n);
        return toISO(d);
    }

    // Chevauchement [aStart, aEnd) / [bStart, bEnd) — même règle que côté serveur
    // (Reservation::hasConflict) : le jour de départ d'une réservation reste libre
    // pour une arrivée le même jour (usage hôtelier standard).
    function rangesOverlap(aStart, aEnd, bStart, bEnd) {
        return aStart < bEnd && aEnd > bStart;
    }

    function selectionOverlapsBlocked(inIso, outIso) {
        return blockedRanges.some((r) => rangesOverlap(inIso, outIso, r.check_in, r.check_out));
    }

    function formatFr(date) {
        return String(date.getDate()).padStart(2, '0') + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + date.getFullYear();
    }

    // Calendrier de disponibilité — une seule instance flatpickr en mode "range",
    // partagée par les deux champs Arrivée/Départ (cliquer sur l'un ou l'autre
    // ouvre le même calendrier). Désactivé tant qu'aucune chambre n'est choisie.
    // Pas d'altInput ici : nos deux champs stylés (Arrivée/Départ) jouent déjà ce
    // rôle — on écrit nous-mêmes leur affichage (jj/mm/aaaa) et leur valeur ISO
    // exploitable (dataset.iso), plutôt que de laisser flatpickr imposer sa propre
    // chaîne "date1 to date2" dans le champ Arrivée en mode range.
    const picker = window.flatpickr(checkin, {
        mode: 'range',
        locale: 'fr',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        disable: [],
        // Toujours en dessous du champ, jamais au-dessus : le positionnement
        // automatique de flatpickr peut sinon basculer le calendrier vers le
        // haut par manque de place visible, recouvrant le message et la
        // légende juste au-dessus des champs de date.
        position: 'below',
        static: true,
        onChange: function (selectedDates) {
            if (selectedDates.length >= 1) {
                checkin.value = formatFr(selectedDates[0]);
                checkin.dataset.iso = toISO(selectedDates[0]);
            }
            if (selectedDates.length === 2) {
                const inIso = checkin.dataset.iso;
                const outIso = toISO(selectedDates[1]);
                if (selectionOverlapsBlocked(inIso, outIso)) {
                    // Sécurité en plus du calendrier lui-même (qui empêche déjà de
                    // sélectionner à travers une date désactivée) : si malgré tout la
                    // période chevauche une réservation, on refuse et on réinitialise.
                    errorBox.textContent = "Cette période n'est plus disponible pour cette chambre. Merci de choisir d'autres dates.";
                    errorBox.classList.remove('hidden');
                    picker.clear();
                    checkin.value = '';
                    checkin.dataset.iso = '';
                    checkout.value = '';
                    checkout.dataset.iso = '';
                    recompute();
                    return;
                }
                errorBox.classList.add('hidden');
                checkout.value = formatFr(selectedDates[1]);
                checkout.dataset.iso = outIso;
            } else {
                checkout.value = '';
                checkout.dataset.iso = '';
            }
            recompute();
        },
    });
    checkout.addEventListener('click', () => picker.open());

    function setDatesEnabled(enabled) {
        datesWrap.classList.toggle('opacity-50', !enabled);
        datesWrap.classList.toggle('pointer-events-none', !enabled);
        picker.set('clickOpens', enabled);
    }
    setDatesEnabled(false);

    document.querySelectorAll('.bkp-pay-method').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.bkp-pay-method').forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            paymentMethod = btn.dataset.method;
        });
    });

    document.querySelectorAll('.bkp-room-card').forEach((card) => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.bkp-room-card').forEach((c) => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            selectedRoom = {
                id: card.dataset.roomId || null,
                name: card.dataset.roomName,
                price: parseInt(card.dataset.roomPrice, 10) || 0,
            };

            // Chaque chambre a son propre calendrier : on repart d'une sélection
            // vierge et on recharge ses disponibilités avant de rouvrir le calendrier.
            picker.clear();
            checkin.value = '';
            checkin.dataset.iso = '';
            checkout.value = '';
            checkout.dataset.iso = '';
            blockedRanges = [];
            picker.set('disable', []);
            setDatesEnabled(false);
            datesHintText.textContent = 'Chargement des disponibilités...';
            datesHint.classList.remove('hidden');
            datesLegend.classList.add('hidden');
            datesWrap.classList.add('bkp-date-loading');
            recompute();

            const myToken = ++availabilityToken;
            const params = new URLSearchParams({ accommodation_id: '{{ $accommodation->id }}' });
            if (selectedRoom.id) params.set('room_id', selectedRoom.id);
            else params.set('room_name', selectedRoom.name);

            fetch('{{ route("reservations.availability") }}?' + params.toString(), { headers: { Accept: 'application/json' } })
                .then((res) => res.json())
                .then((data) => {
                    if (myToken !== availabilityToken) return; // réponse obsolète (chambre changée depuis)
                    datesWrap.classList.remove('bkp-date-loading');
                    setDatesEnabled(true);
                    if (data.success) {
                        blockedRanges = data.blocked_ranges || [];
                        // La date de départ d'une réservation existante reste sélectionnable
                        // (arrivée le même jour) : on désactive donc jusqu'à la veille du départ.
                        picker.set('disable', blockedRanges.map((r) => ({ from: r.check_in, to: addDays(r.check_out, -1) })));
                        datesHintText.textContent = blockedRanges.length
                            ? 'Voici les jours disponibles pour cette chambre : les dates déjà réservées sont grisées et barrées ci-dessous.'
                            : 'Voici les jours disponibles pour cette chambre : toutes les dates sont libres.';
                        datesLegend.classList.remove('hidden');
                        datesLegend.classList.add('flex');
                    } else {
                        datesHintText.textContent = 'Disponibilités indisponibles pour le moment — vérification faite à la réservation.';
                    }
                    // Le calendrier s'ouvre automatiquement après la sélection d'une chambre.
                    picker.open();
                })
                .catch(() => {
                    if (myToken !== availabilityToken) return;
                    datesWrap.classList.remove('bkp-date-loading');
                    setDatesEnabled(true);
                    datesHintText.textContent = 'Disponibilités indisponibles pour le moment — vérification faite à la réservation.';
                    picker.open();
                });
        });
    });

    document.querySelectorAll('.bkp-step').forEach((btn) => {
        btn.addEventListener('click', () => {
            const el = document.getElementById(btn.dataset.target);
            if (!el) return;
            const step = parseInt(btn.dataset.step, 10);
            const current = parseInt(el.dataset.value || '1', 10);
            const next = Math.max(1, current + step);
            el.dataset.value = String(next);
            el.textContent = String(next);
            recompute();
        });
    });

    function nightsBetween(inStr, outStr) {
        if (!inStr || !outStr) return 0;
        const diff = Math.round((new Date(outStr + 'T00:00:00') - new Date(inStr + 'T00:00:00')) / 86400000);
        return diff > 0 ? diff : 0;
    }

    function recompute() {
        if (!selectedRoom) {
            submitLabel.textContent = 'Sélectionnez une chambre';
            submitBtn.disabled = true;
            breakdown.classList.add('hidden');
            return;
        }
        const nights = nightsBetween(checkin.dataset.iso, checkout.dataset.iso);
        const rooms = parseInt(document.getElementById('bkp-rooms').dataset.value || '1', 10);
        if (nights <= 0) {
            submitLabel.textContent = 'Choisissez vos dates';
            submitBtn.disabled = true;
            breakdown.classList.add('hidden');
            return;
        }
        const total = selectedRoom.price * nights * rooms;
        const deposit = Math.round(total * depositPercent / 100);
        document.getElementById('bkp-nights-label').textContent =
            nights + ' nuit' + (nights > 1 ? 's' : '') + ' × ' + rooms + ' chambre' + (rooms > 1 ? 's' : '');
        document.getElementById('bkp-total').textContent = total.toLocaleString('fr-FR') + ' XOF';
        document.getElementById('bkp-deposit').textContent = deposit.toLocaleString('fr-FR') + ' XOF';
        breakdown.classList.remove('hidden');
        submitLabel.textContent = "Réserver et payer l'acompte (" + deposit.toLocaleString('fr-FR') + ' XOF)';
        submitBtn.disabled = false;
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            errorBox.classList.add('hidden');
            const fullName = document.getElementById('bkp-full-name').value.trim();
            const email = document.getElementById('bkp-email').value.trim();
            const phone = document.getElementById('bkp-phone').value.trim();
            if (!fullName || !email) {
                errorBox.textContent = 'Merci de renseigner votre nom complet et votre email.';
                errorBox.classList.remove('hidden');
                return;
            }
            const originalLabel = submitLabel.textContent;
            submitBtn.disabled = true;
            submitLabel.textContent = 'Traitement en cours...';
            try {
                const res = await fetch('{{ route("reservations.payment.initiate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        accommodation_id: {{ $accommodation->id }},
                        room_id: selectedRoom.id,
                        room_name: selectedRoom.name,
                        room_price_xof: selectedRoom.price,
                        check_in: checkin.dataset.iso,
                        check_out: checkout.dataset.iso,
                        rooms_count: parseInt(document.getElementById('bkp-rooms').dataset.value || '1', 10),
                        guests_count: parseInt(document.getElementById('bkp-guests').dataset.value || '2', 10),
                        full_name: fullName,
                        email: email,
                        phone: phone,
                        payment_method: paymentMethod,
                    }),
                });
                const data = await res.json();
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }
                errorBox.textContent = data.message || 'Une erreur est survenue, merci de réessayer.';
                errorBox.classList.remove('hidden');
            } catch (e) {
                errorBox.textContent = 'Erreur réseau, merci de réessayer.';
                errorBox.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitLabel.textContent = originalLabel;
            }
        });
    }
})();

// ── Depuis la page dédiée aux chambres : présélectionne la chambre choisie
// (?room=...) en réutilisant le sélecteur de carte déjà câblé ci-dessus.
(function () {
    const roomName = new URLSearchParams(window.location.search).get('room');
    if (!roomName) return;
    const card = document.querySelector('.bkp-room-card[data-room-name="' + roomName.replace(/"/g, '\\"') + '"]');
    if (card) card.click();
})();

// ── Depuis la liste illustrée des chambres (sur cette même page) : présélectionne
// la chambre dans le module de réservation puis y amène.
function reserveThisRoom(roomName) {
    const card = document.querySelector('.bkp-room-card[data-room-name="' + roomName.replace(/"/g, '\\"') + '"]');
    if (card) card.click();
    document.getElementById('booking-module')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

(function () {
    // Arrivée directe via ancre (#chambres-disponibles, #booking-module...) : le navigateur
    // y saute instantanément, souvent avant que l'IntersectionObserver n'ait eu la main —
    // la section resterait invisible (opacity:0) malgré le saut. On révèle tout d'un coup
    // dans ce cas précis plutôt que de laisser l'utilisateur atterrir sur du contenu masqué.
    if (window.location.hash) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }
    const items = document.querySelectorAll('.reveal:not(.is-visible)');
    if (!('IntersectionObserver' in window) || !items.length) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    items.forEach(el => io.observe(el));
})();
</script>
</body>
</html>
