<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $provider->name }} — Annuaire {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .reveal, .reveal.is-visible { opacity: 1 !important; transform: none !important; transition: none !important; }
        }
        @keyframes heroFade { from { opacity:0; transform:scale(1.05) } to { opacity:1; transform:scale(1) } }
        #prov-hero-img { animation: heroFade .6s cubic-bezier(.22,1,.36,1) both; transition: transform 10s ease; }
        #prov-hero-wrap:hover #prov-hero-img { transform: scale(1.05); }

        .reveal { opacity: 0; transform: translateY(26px); transition: opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1); }
        .reveal.is-visible { opacity: 1; transform: none; }

        .glass-pill { border: 1px solid rgba(255,255,255,0.22); background: rgba(20,18,14,0.4); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }
        .glass-arrow { border: 1px solid rgba(255,255,255,0.18); background: rgba(20,18,14,0.35); backdrop-filter: blur(10px); transition: transform .2s ease, background .2s ease; }
        .glass-arrow:hover { background: rgba(20,18,14,0.6); transform: scale(1.06); }

        /* Texte posé sur la photo/vidéo héro : toujours blanc, non affecté par le pont de
           theme clair (qui réécrit .text-white en texte sombre pour le fond de page, ce qui
           le rend illisible ici puisque le fond reste une image/vidéo sombre). */
        #prov-hero-wrap .text-white { color:#ffffff !important; }
        #prov-hero-wrap .text-white\/85 { color:rgba(255,255,255,.85) !important; }
        #prov-hero-wrap .text-white\/60 { color:rgba(255,255,255,.6) !important; }
        #prov-hero-wrap .text-white\/40 { color:rgba(255,255,255,.4) !important; }

        .hero-scrim { background: linear-gradient(to top, rgba(8,7,5,0.92) 0%, rgba(8,7,5,0.35) 45%, rgba(8,7,5,0.05) 70%), linear-gradient(120deg, rgba(8,7,5,0.3), transparent 40%); }
        .rating-badge { border: 1px solid rgba(255,255,255,0.2); background: rgba(20,18,14,0.55); backdrop-filter: blur(16px); }

        .btn-primary {
            background: #f2790f;
            box-shadow: 0 12px 28px rgba(242,121,15,0.35);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
            color: #1b1408;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); box-shadow: 0 16px 36px rgba(242,121,15,0.45); }
        .btn-primary:disabled { transform: none !important; filter: none !important; }

        .bento-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 30px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .bento-card:hover { transform: translateY(-3px); box-shadow: 0 16px 38px rgba(194,94,10,0.13); border-color: rgba(242,121,15,0.28); }
        .bento-icon {
            width: 2.2rem; height: 2.2rem; border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, rgba(242,121,15,0.18), rgba(242,121,15,0.06));
            color: #d4630a;
        }
        .section-kicker { letter-spacing: .22em; }

        .filmstrip-thumb { transition: transform .25s ease, opacity .25s ease, box-shadow .25s ease; }
        .filmstrip-thumb:hover { transform: translateY(-2px); }

        .review-card { border: 1px solid rgba(0,0,0,0.07); background: #ffffff; box-shadow: 0 8px 22px rgba(20,18,12,0.05); transition: transform .2s ease, box-shadow .2s ease; }
        .review-card:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(194,94,10,0.1); }
        .rating-bar-track { height: .4rem; border-radius: 999px; background: rgba(194,94,10,0.1); overflow: hidden; }
        .rating-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #f4c65a, #f2790f); }

        .booking-card {
            border: 1px solid rgba(0,0,0,0.08);
            background: linear-gradient(180deg, #ffffff, #fbf7ee);
            box-shadow: 0 20px 50px rgba(20,18,12,0.1);
        }
        .related-row {
            border: 1px solid rgba(0,0,0,0.07); background: #ffffff;
            box-shadow: 0 6px 18px rgba(20,18,12,0.05);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .related-row:hover { transform: translateX(3px); border-color: rgba(242,121,15,0.35); box-shadow: 0 10px 24px rgba(194,94,10,0.12); }

    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-900/10 border border-emerald-700/30 rounded-xl text-emerald-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-900/10 border border-red-700/30 rounded-xl text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <a href="{{ route('providers.index') }}" class="inline-flex items-center gap-2 text-[#c25e0a] hover:text-[#a24d08] transition text-sm font-semibold">
            <i class="fas fa-arrow-left text-[11px]"></i> Retour à l'annuaire
        </a>

        @php
            $galleryPhotos = collect();
            if ($provider->cover_url) {
                $galleryPhotos->push(['type' => 'photo', 'url' => $provider->cover_url, 'alt' => $provider->name]);
            }
            foreach ($provider->media->where('type', 'image')->sortBy('sort_order') as $gm) {
                $galleryPhotos->push(['type' => 'photo', 'url' => $gm->url, 'alt' => $gm->alt_text ?: $provider->name]);
            }
            if ($accommodation) {
                foreach ($accommodation->videos as $v) {
                    $galleryPhotos->push([
                        'type' => 'video',
                        'url' => $v->url,
                        'embed_url' => $v->embedUrl(),
                        'thumb' => $v->videoThumbnailUrl(),
                        'alt' => $provider->name.' — vidéo',
                    ]);
                }
            }
        @endphp
        <script id="gallery-json" type="application/json">@json($galleryPhotos->values())</script>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start mt-5">
        <div class="lg:col-span-3 space-y-14">

            <div class="space-y-3 reveal is-visible">
                {{-- ── HERO GALERIE CINÉMATIQUE ─────────────────────────────── --}}
                <div class="relative rounded-3xl overflow-hidden bg-[#14130f]"
                     style="height:clamp(360px,64vh,600px)" id="prov-hero-wrap">

                    @if($galleryPhotos->isNotEmpty())
                    @php
                        $firstSlide = $galleryPhotos->first();
                        $firstIsVideo = ($firstSlide['type'] ?? 'photo') === 'video';
                    @endphp
                    <img id="prov-hero-img"
                         src="{{ $firstIsVideo ? ($firstSlide['thumb'] ?? '') : $firstSlide['url'] }}"
                         alt="{{ $firstSlide['alt'] }}"
                         class="w-full h-full object-cover cursor-zoom-in {{ $firstIsVideo ? 'hidden' : '' }}"
                         onclick="openLightbox(this.src)">
                    @php
                        $firstHasEmbed = $firstIsVideo && ! empty($firstSlide['embed_url']);
                        $firstEmbedAutoplay = $firstHasEmbed ? $firstSlide['embed_url'].'?autoplay=1&mute=1&muted=1&playsinline=1' : '';
                    @endphp
                    <div id="prov-hero-video-wrap" class="absolute inset-0 {{ $firstIsVideo ? '' : 'hidden' }}">
                        <iframe id="prov-hero-video-iframe"
                                src="{{ $firstEmbedAutoplay }}"
                                class="w-full h-full {{ $firstHasEmbed ? '' : 'hidden' }}" loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                        <video id="prov-hero-video-native"
                               src="{{ ($firstIsVideo && ! $firstHasEmbed) ? $firstSlide['url'] : '' }}"
                               autoplay muted playsinline controls preload="metadata"
                               class="w-full h-full object-cover {{ ($firstIsVideo && ! $firstHasEmbed) ? '' : 'hidden' }}"></video>
                    </div>
                    <span id="prov-zoom-hint" class="zoom-hint absolute bottom-5 right-5 z-10 w-10 h-10 rounded-full bg-black/55 border border-white/20 backdrop-blur flex items-center justify-center text-white pointer-events-none {{ $firstIsVideo ? 'hidden' : '' }}">
                        <i class="fas fa-magnifying-glass-plus text-sm"></i>
                    </span>

                    <div class="hero-scrim absolute inset-0 pointer-events-none"></div>

                    {{-- Badges top-left --}}
                    <div class="absolute top-5 left-5 flex flex-wrap gap-2 z-10">
                        @if($provider->is_verified)
                        <span class="glass-pill inline-flex items-center gap-1.5 text-emerald-300 text-xs font-bold px-3 py-1.5 rounded-full">
                            <i class="fas fa-circle-check text-[10px]"></i> Vérifié
                        </span>
                        @endif
                        @if($provider->city)
                        <span class="glass-pill inline-flex items-center gap-1.5 text-white/85 text-xs px-3 py-1.5 rounded-full">
                            <i class="fas fa-location-dot text-orange-400 text-[10px]"></i> {{ $provider->city }}
                        </span>
                        @endif
                    </div>

                    {{-- Note --}}
                    <div class="rating-badge absolute top-5 right-5 rounded-2xl px-4 py-3 text-center z-10">
                        <p class="text-4xl font-black text-white tabular-nums leading-none">
                            {{ number_format((float)($provider->rating_avg ?? 0), 1) }}
                        </p>
                        @php $rounded = (int) round($provider->rating_avg ?? 0); @endphp
                        <div class="flex justify-center gap-0.5 mt-1.5">
                            @foreach(range(1,5) as $star)
                            <i class="fas fa-star text-[9px] @if($star <= $rounded) text-orange-400 @else text-white/20 @endif"></i>
                            @endforeach
                        </div>
                        <p class="text-white/40 text-[10px] mt-1.5 tabular-nums">{{ $provider->approvedReviews->count() }} avis</p>
                    </div>

                    {{-- Flèches --}}
                    @if($galleryPhotos->count() > 1)
                    <button onclick="provSlide(-1)"
                            class="glass-arrow absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-3 pr-4 rounded-full text-white z-10 active:scale-95">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span class="text-[11px] text-white/60 hidden sm:inline">Préc.</span>
                    </button>
                    <button onclick="provSlide(+1)"
                            class="glass-arrow absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 h-10 pl-4 pr-3 rounded-full text-white z-10 active:scale-95">
                        <span class="text-[11px] text-white/60 hidden sm:inline">Suiv.</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                    @endif

                    {{-- Titre en overlay --}}
                    <div class="absolute inset-x-0 bottom-0 z-10 p-5 sm:p-7">
                        @if($provider->category)
                        <span class="glass-pill inline-flex items-center rounded-full px-3.5 py-1.5 text-[10px] uppercase tracking-[0.16em] text-orange-100 font-bold mb-3">
                            <i class="fas fa-store mr-1.5"></i>{{ $provider->category->name_fr }}
                        </span>
                        @endif
                        <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-[1.05] text-white [text-shadow:0_4px_18px_rgba(0,0,0,0.5)] max-w-3xl">
                            {{ $provider->name }}
                        </h1>
                    </div>

                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-4">
                        <div class="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center">
                            <i class="fas fa-store text-4xl text-gray-600"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Aucune photo disponible</p>
                    </div>
                    @endif
                </div>

                {{-- ── FILMSTRIP MINIATURES ─────────────────────────────────── --}}
                @if($galleryPhotos->count() > 1)
                <div class="flex items-center gap-3">
                    <div class="flex gap-2 flex-1 overflow-x-auto" id="prov-thumbs" style="scrollbar-width:none;-ms-overflow-style:none">
                        @foreach($galleryPhotos as $gi => $gp)
                        @php
                            $thumbCls = $gi === 0
                                ? 'filmstrip-thumb shrink-0 w-20 h-14 rounded-xl overflow-hidden relative ring-2 ring-orange-400 scale-105 shadow-lg shadow-orange-500/20'
                                : 'filmstrip-thumb shrink-0 w-20 h-14 rounded-xl overflow-hidden relative opacity-45 hover:opacity-80';
                            $gpIsVideo = ($gp['type'] ?? 'photo') === 'video';
                        @endphp
                        <button data-idx="{{ $gi }}" onclick="provGoTo(+this.dataset.idx)"
                                id="prov-thumb-{{ $gi }}"
                                class="{{ $thumbCls }}">
                            @if($gpIsVideo)
                                @if(!empty($gp['thumb']))
                                    <img src="{{ $gp['thumb'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="w-full h-full bg-black/80"></div>
                                @endif
                                <span class="absolute inset-0 flex items-center justify-center bg-black/30">
                                    <i class="fas fa-play text-white text-[10px]"></i>
                                </span>
                            @else
                                <img src="{{ $gp['url'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @endif
                        </button>
                        @endforeach
                    </div>
                    <span id="prov-counter"
                          class="shrink-0 text-xs font-mono text-[#8a7f6b] bg-white border border-black/8 px-2.5 py-1.5 rounded-lg tabular-nums">
                        {{ str_pad(1, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($galleryPhotos->count(), 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                @endif
            </div>

            @if(isset($menuByCategory) && $menuByCategory->isNotEmpty())
            {{-- ── NOTRE CARTE (Restaurants & Gastronomie) ── --}}
            <section class="reveal is-visible">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Au menu</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">Notre carte</h2>

                <div class="space-y-6">
                    @foreach($menuByCategory as $categoryName => $items)
                    <div>
                        <h3 class="text-[#c25e0a] text-sm font-bold uppercase tracking-wide mb-3">{{ $categoryName }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($items as $item)
                            <div class="bento-card rounded-xl p-3.5 flex items-center gap-3">
                                @if(!empty($item->images[0]))
                                <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-black/5">
                                    <img src="{{ $item->images[0] }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[#1c1915] text-sm font-semibold truncate">{{ $item->name }}</p>
                                    @if($item->description)
                                    <p class="text-[#8a7f6b] text-xs mt-0.5 line-clamp-2">{{ $item->description }}</p>
                                    @endif
                                    <p class="text-[#c25e0a] text-sm font-bold mt-1">{{ number_format((int) $item->price_xof, 0, ',', ' ') }} XOF</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            @if(isset($toursByCategory) && $toursByCategory->isNotEmpty())
            {{-- ── NOS CIRCUITS (Agences de Voyages & Tours) ── --}}
            <section class="reveal is-visible">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Nos offres</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">Nos circuits</h2>

                <div class="space-y-6">
                    @foreach($toursByCategory as $categoryName => $tours)
                    <div>
                        <h3 class="text-[#c25e0a] text-sm font-bold uppercase tracking-wide mb-3">{{ $categoryName }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($tours as $tour)
                            <div class="bento-card rounded-xl p-3.5 flex items-center gap-3">
                                @if(!empty($tour->images[0]))
                                <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-black/5">
                                    <img src="{{ $tour->images[0] }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[#1c1915] text-sm font-semibold truncate">{{ $tour->name }}</p>
                                    <p class="text-[#8a7f6b] text-xs mt-0.5">
                                        @if($tour->duration_days) {{ $tour->duration_days }} jour(s) @endif
                                        @if($tour->duration_days && $tour->max_participants) · @endif
                                        @if($tour->max_participants) {{ $tour->max_participants }} pers. max @endif
                                    </p>
                                    <p class="text-[#c25e0a] text-sm font-bold mt-1">{{ number_format((int) $tour->price_xof, 0, ',', ' ') }} XOF</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            @if(isset($activitiesByCategory) && $activitiesByCategory->isNotEmpty())
            {{-- ── NOS ACTIVITÉS (Loisirs & Culture) ── --}}
            <section class="reveal is-visible">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">À vivre sur place</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">Nos activités</h2>

                <div class="space-y-6">
                    @foreach($activitiesByCategory as $categoryName => $activityGroup)
                    <div>
                        <h3 class="text-[#c25e0a] text-sm font-bold uppercase tracking-wide mb-3">{{ $categoryName }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($activityGroup as $activity)
                            <div class="bento-card rounded-xl p-3.5 flex items-center gap-3">
                                @if(!empty($activity->images[0]))
                                <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-black/5">
                                    <img src="{{ $activity->images[0] }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[#1c1915] text-sm font-semibold truncate">{{ $activity->name }}</p>
                                    <p class="text-[#8a7f6b] text-xs mt-0.5">
                                        @if($activity->duration_minutes) {{ $activity->duration_minutes }} min @endif
                                        @if($activity->duration_minutes && $activity->max_participants) · @endif
                                        @if($activity->max_participants) {{ $activity->max_participants }} pers. max @endif
                                    </p>
                                    <p class="text-[#c25e0a] text-sm font-bold mt-1">{{ number_format((int) $activity->price_xof, 0, ',', ' ') }} XOF</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            @if(isset($transportByCategory) && $transportByCategory->isNotEmpty())
            {{-- ── NOS OFFRES DE TRANSPORT (Transports & Mobilité) ── --}}
            <section class="reveal is-visible">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Se déplacer</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">Nos offres de transport</h2>

                <div class="space-y-6">
                    @foreach($transportByCategory as $categoryName => $offerGroup)
                    <div>
                        <h3 class="text-[#c25e0a] text-sm font-bold uppercase tracking-wide mb-3">{{ $categoryName }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($offerGroup as $offer)
                            <div class="bento-card rounded-xl p-3.5 flex items-center gap-3">
                                @if(!empty($offer->images[0]))
                                <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-black/5">
                                    <img src="{{ $offer->images[0] }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[#1c1915] text-sm font-semibold truncate">{{ $offer->name }}</p>
                                    @if($offer->max_passengers)
                                    <p class="text-[#8a7f6b] text-xs mt-0.5">{{ $offer->max_passengers }} pers. max</p>
                                    @endif
                                    <p class="text-[#c25e0a] text-sm font-bold mt-1">{{ number_format((int) $offer->price_xof, 0, ',', ' ') }} XOF</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            @if(isset($artworksByCategory) && $artworksByCategory->isNotEmpty())
            {{-- ── NOS ŒUVRES (Art & Créations) ── --}}
            <section class="reveal is-visible">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Galerie</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">Nos œuvres</h2>

                <div class="space-y-6">
                    @foreach($artworksByCategory as $categoryName => $artworkGroup)
                    <div>
                        <h3 class="text-[#c25e0a] text-sm font-bold uppercase tracking-wide mb-3">{{ $categoryName }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($artworkGroup as $artwork)
                            <a href="{{ route('art.show', $artwork->slug) }}" class="bento-card rounded-xl p-3.5 flex items-center gap-3 hover:border-orange-500/40 transition">
                                @if(!empty($artwork->images[0]))
                                <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-black/5">
                                    <img src="{{ $artwork->images[0] }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[#1c1915] text-sm font-semibold truncate">{{ $artwork->title }}</p>
                                    @if($artwork->medium)
                                    <p class="text-[#8a7f6b] text-xs mt-0.5 truncate">{{ $artwork->medium }}</p>
                                    @endif
                                    <p class="text-[#c25e0a] text-sm font-bold mt-1">{{ number_format((int) $artwork->price_xof, 0, ',', ' ') }} XOF</p>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- ── FAVORIS + INFOS PRATIQUES ────────────────────────────────── --}}
            <section class="reveal">
                @auth
                    @if(auth()->user()->role === 'visitor')
                    <div class="mb-5">
                        @if(!($isFavorited ?? false))
                        <form method="POST" action="{{ route('visitor.favorites.store') }}">
                            @csrf
                            <input type="hidden" name="type" value="provider">
                            <input type="hidden" name="id" value="{{ $provider->id }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-black/10 hover:border-orange-400/50 hover:bg-orange-50 text-[#c25e0a] text-xs font-bold transition">
                                <i class="fas fa-heart text-[10px]"></i> Ajouter à ma wishlist
                            </button>
                        </form>
                        @else
                        <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                            <i class="fas fa-heart-circle-check text-[10px]"></i> Déjà dans vos favoris
                        </span>
                        @endif
                    </div>
                    @endif
                @endauth

                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Présentation</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">À propos</h2>
                <p class="text-[#3d372c] leading-relaxed text-[15px]">
                    {{ $provider->description_fr ?: 'Description non disponible.' }}
                </p>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach(array_filter([
                        ['icon'=>'fa-location-dot','label'=>'Adresse',  'value'=>$provider->address],
                        ['icon'=>'fa-city',        'label'=>'Ville',    'value'=>$provider->city],
                        ['icon'=>'fa-map',         'label'=>'Région',   'value'=>$provider->region],
                        ['icon'=>'fa-phone',       'label'=>'Téléphone','value'=>$provider->phone],
                        ['icon'=>'fa-envelope',    'label'=>'Email',    'value'=>$provider->email],
                    ]) as $row)
                    @if($row['value'])
                    <div class="bento-card rounded-xl p-3.5 flex items-center gap-3">
                        <div class="bento-icon shrink-0"><i class="fas {{ $row['icon'] }} text-[11px]"></i></div>
                        <div class="min-w-0">
                            <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold">{{ $row['label'] }}</p>
                            <p class="text-[#1c1915] text-sm font-medium truncate">{{ $row['value'] }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach

                    @if($provider->website)
                    <div class="bento-card rounded-xl p-3.5 flex items-center gap-3 sm:col-span-2">
                        <div class="bento-icon shrink-0"><i class="fas fa-globe text-[11px]"></i></div>
                        <div class="min-w-0">
                            <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold">Site web</p>
                            <a href="{{ $provider->website }}" target="_blank"
                               class="text-[#c25e0a] hover:text-[#a24d08] text-sm font-medium truncate block transition">
                                {{ $provider->website }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            @if($provider->sponsoredArticles->isNotEmpty())
            {{-- ── ARTICLES ASSOCIÉS ───────────────────────────────────────── --}}
            <section class="reveal">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">À lire</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">Articles associés</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($provider->sponsoredArticles as $article)
                    <a href="{{ route('articles.show', $article->slug_fr) }}" class="bento-card rounded-xl p-3.5 flex items-center gap-3">
                        @if($article->cover_url)
                        <div class="w-16 h-16 shrink-0 rounded-lg overflow-hidden bg-black/5">
                            <img src="{{ $article->cover_url }}" alt="" class="w-full h-full object-cover">
                        </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-[#1c1915] text-sm font-semibold line-clamp-2">{{ $article->title_fr }}</p>
                            @if($article->published_at)
                            <p class="text-[#8a7f6b] text-xs mt-1">{{ $article->published_at->translatedFormat('d M Y') }}</p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- ── LOCALISATION ─────────────────────────────────────────────── --}}
            <section class="reveal">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Où se trouve l'établissement</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-4">Localisation</h2>

                @if($provider->latitude && $provider->longitude)
                    <div class="bento-card rounded-2xl overflow-hidden mb-3">
                        <iframe src="https://www.google.com/maps?q={{ $provider->latitude }},{{ $provider->longitude }}&output=embed"
                                class="w-full" style="height:260px; border:0;" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <a href="https://maps.google.com/?q={{ $provider->latitude }},{{ $provider->longitude }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-black/10 hover:border-orange-400/50 hover:bg-orange-50 text-[#c25e0a] text-xs font-bold transition">
                        <i class="fas fa-diamond-turn-right text-[10px]"></i> Itinéraire
                    </a>
                @else
                    <p class="text-[#8a7f6b] text-sm">Coordonnées non renseignées pour cet établissement.</p>
                @endif
            </section>

            {{-- ── AVIS CLIENTS ──────────────────────────────────────────────── --}}
            <section class="reveal">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Retours d'expérience</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Avis clients</h2>

                @php
                    $sortedReviews = $provider->approvedReviews->sortByDesc('created_at')->values();
                    $visibleReviews = $sortedReviews->take(2);
                    $extraReviews = $sortedReviews->slice(2);
                @endphp
                @if($sortedReviews->isEmpty())
                    <p class="text-[#8a7f6b] text-sm">Aucun avis approuvé pour ce prestataire.</p>
                @else
                    <div class="space-y-4">
                        @foreach($visibleReviews as $review)
                            <div class="review-card rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-semibold text-sm">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }}</p>
                                    <span class="text-[#c25e0a] text-sm font-bold">{{ $review->rating }} ★</span>
                                </div>
                                @if($review->title)
                                    <p class="text-[#1c1915] text-sm font-medium">{{ $review->title }}</p>
                                @endif
                                <p class="text-[#5c5548] text-sm mt-1">{{ $review->comment }}</p>
                                @if($review->reply && $review->reply->is_visible)
                                    <div class="mt-3 bg-[#fbf5e9] rounded-lg p-3 text-sm border border-black/6">
                                        <p class="text-[#c25e0a] text-xs font-bold mb-1">Réponse du prestataire</p>
                                        <p class="text-[#3d372c]">{{ $review->reply->reply_text }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($extraReviews->isNotEmpty())
                        <div id="reviews-more" class="hidden space-y-4 mt-4">
                            @foreach($extraReviews as $review)
                                <div class="review-card rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-semibold text-sm">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }}</p>
                                        <span class="text-[#c25e0a] text-sm font-bold">{{ $review->rating }} ★</span>
                                    </div>
                                    @if($review->title)
                                        <p class="text-[#1c1915] text-sm font-medium">{{ $review->title }}</p>
                                    @endif
                                    <p class="text-[#5c5548] text-sm mt-1">{{ $review->comment }}</p>
                                    @if($review->reply && $review->reply->is_visible)
                                        <div class="mt-3 bg-[#fbf5e9] rounded-lg p-3 text-sm border border-black/6">
                                            <p class="text-[#c25e0a] text-xs font-bold mb-1">Réponse du prestataire</p>
                                            <p class="text-[#3d372c]">{{ $review->reply->reply_text }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <button type="button" id="reviews-toggle"
                                data-more-label="Voir plus ({{ $extraReviews->count() }} avis)"
                                data-less-label="Voir moins"
                                class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-black/10 text-[#c25e0a] text-sm font-bold hover:bg-orange-50 hover:border-orange-400/40 transition">
                            <span>Voir plus ({{ $extraReviews->count() }} avis)</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                    @endif
                @endif
            </section>

            {{-- ── NOTE DÉTAILLÉE + INFOS PRATIQUES ─────────────────────────── --}}
            <section class="reveal grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bento-card rounded-2xl p-5">
                    <h2 class="font-serif text-lg font-bold mb-4">Note détaillée</h2>
                    <div class="space-y-3.5 text-sm">
                        @php
                            $criteria = [
                                'quality' => ['label' => 'Qualité', 'value' => $ratingBreakdown['quality'] ?? 0, 'count' => $ratingBreakdownCounts['quality'] ?? 0],
                                'price' => ['label' => 'Prix', 'value' => $ratingBreakdown['price'] ?? 0, 'count' => $ratingBreakdownCounts['price'] ?? 0],
                                'welcome' => ['label' => 'Accueil', 'value' => $ratingBreakdown['welcome'] ?? 0, 'count' => $ratingBreakdownCounts['welcome'] ?? 0],
                                'clean' => ['label' => 'Propreté', 'value' => $ratingBreakdown['clean'] ?? 0, 'count' => $ratingBreakdownCounts['clean'] ?? 0],
                            ];
                        @endphp
                        @foreach($criteria as $criterion)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[#3d372c] font-medium">{{ $criterion['label'] }}</span>
                                    <span class="text-[#c25e0a] font-bold">{{ number_format((float) $criterion['value'], 1) }}/5</span>
                                </div>
                                <div class="rating-bar-track">
                                    <div class="rating-bar-fill" style="width: {{ min(100, max(0, (float) $criterion['value'] / 5 * 100)) }}%"></div>
                                </div>
                                <p class="text-[#8a7f6b] text-[11px] mt-1">{{ $criterion['count'] }} avis</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bento-card rounded-2xl p-5">
                    <h2 class="font-serif text-lg font-bold mb-4">Infos pratiques</h2>
                    <p class="text-[#3d372c] text-sm">Prix: <span class="uppercase font-semibold">{{ $provider->price_range ?: 'N/A' }}</span></p>
                    <p class="text-[#3d372c] text-sm mt-1">Plage prix: {{ number_format((float) ($provider->price_min ?? 0), 0, ',', ' ') }} - {{ number_format((float) ($provider->price_max ?? 0), 0, ',', ' ') }} FCFA</p>

                    <div class="mt-5">
                        <h3 class="text-[#1c1915] text-sm font-semibold mb-2">Horaires</h3>
                        <div class="space-y-1 text-xs text-[#5c5548]">
                            @php
                                $days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                            @endphp
                            @forelse($provider->hours as $hour)
                                <p>{{ $days[$hour->day_of_week] ?? 'Jour' }}: {{ $hour->is_closed ? 'Fermé' : (($hour->open_time ?: '--:--') . ' - ' . ($hour->close_time ?: '--:--')) }}</p>
                            @empty
                                <p>Horaires non renseignés.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            @auth
                @if($canReview)
                    <section class="bento-card rounded-2xl p-5 reveal">
                        <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Votre avis compte</p>
                        <h2 class="font-serif text-xl font-bold mb-4">Laisser un avis</h2>
                        <form method="POST" action="{{ route('reviews.store', $provider) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @csrf
                            <div>
                                <label class="text-xs text-[#8a7f6b] font-semibold">Note globale *</label>
                                <select name="rating" required class="mt-1 w-full bg-white border border-black/10 rounded-lg px-3 py-2 text-sm">
                                    <option value="">Choisir...</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ $i }} étoile(s)</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-[#8a7f6b] font-semibold">Titre</label>
                                <input type="text" name="title" class="mt-1 w-full bg-white border border-black/10 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs text-[#8a7f6b] font-semibold">Commentaire *</label>
                                <textarea name="comment" rows="4" required class="mt-1 w-full bg-white border border-black/10 rounded-lg px-3 py-2 text-sm"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <button class="btn-primary btn-shine text-sm font-bold px-5 py-2.5 rounded-xl">Envoyer l'avis</button>
                            </div>
                        </form>
                    </section>
                @endif
            @endauth
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             SIDEBAR CONTACT (sticky) — l'Annuaire est un espace d'information
             uniquement : aucune réservation, aucun paiement, aucune date.
        ═══════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2">
            <div class="lg:sticky lg:top-24 space-y-3 reveal">
                <div class="booking-card rounded-3xl p-5">
                    <h2 class="font-serif text-xl font-bold mb-1">Contacter le prestataire</h2>
                    <p class="text-[#8a7f6b] text-xs mb-4">Posez vos questions directement, en toute sécurité, sans quitter la plateforme.</p>

                    @auth
                        @if(auth()->user()->role === 'visitor')
                            <form method="POST" action="{{ route('visitor.conversations.store') }}" class="space-y-2.5">
                                @csrf
                                <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                                <textarea name="message" rows="3" required maxlength="4000"
                                          class="w-full bg-white border border-black/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-[#a89f8f]"
                                          placeholder="Votre message au prestataire..."></textarea>
                                <button type="submit" class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-3.5 rounded-2xl font-extrabold text-[14px]">
                                    <i class="fas fa-paper-plane text-sm"></i> Envoyer le message
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="rounded-2xl bg-black/[0.03] border border-black/8 px-4 py-5 text-center">
                            <p class="text-[#1c1915] font-bold text-sm mb-1">Connectez-vous pour contacter ce prestataire</p>
                            <div class="flex items-center justify-center gap-2 mt-3">
                                <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary btn-shine inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs">
                                    <i class="fas fa-right-to-bracket text-xs"></i> Se connecter
                                </a>
                                <a href="{{ route('register', ['role' => 'visitor', 'redirect' => url()->current()]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs border border-black/10 hover:border-orange-400/50 text-[#1c1915] transition">
                                    Créer un compte
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
        </div>

        @if($related->isNotEmpty())
            <section class="mt-16 reveal">
                <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">À découvrir aussi</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Prestataires similaires</h2>
                <div class="space-y-3">
                    @foreach($related as $r)
                        @php
                            $rCoverImg = $r->cover_url
                                ?: $r->media->where('type', 'image')->sortBy('sort_order')->first()?->url
                                ?: $r->accommodation?->cover_image;
                        @endphp
                        <a href="{{ route('providers.show', $r->slug) }}" class="related-row flex items-center gap-4 rounded-xl p-3">
                            <div class="w-12 h-12 rounded-lg border border-black/8 bg-[#f0ece1] flex items-center justify-center overflow-hidden shrink-0">
                                @if($rCoverImg)
                                    <img src="{{ $rCoverImg }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-store text-[#c25e0a]/70"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold leading-snug truncate">{{ $r->name }}</p>
                                    @if($r->is_verified)
                                    <span class="inline-flex items-center bg-emerald-500/10 text-emerald-700 text-[9px] font-bold px-2 py-0.5 rounded-full border border-emerald-500/25 shrink-0">
                                        <i class="fas fa-badge-check mr-1 text-[8px]"></i>Vérifié
                                    </span>
                                    @endif
                                </div>
                                <p class="text-[#8a7f6b] text-sm mt-0.5 truncate">{{ $r->category->name_fr ?? 'Prestataire' }} · {{ $r->city ?: 'N/A' }}</p>
                            </div>
                            <i class="fas fa-chevron-right text-[#8a7f6b]/50 text-xs shrink-0"></i>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

    <div class="pb-20 lg:pb-0">
@include('partials.homepage-footer')
    </div>

<script>
function openLightbox(url) {
    document.getElementById('lightbox-img').src = url;
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
// ── Provider photo gallery ────────────────────────────────────────────────
(function () {
    const photos = JSON.parse(document.getElementById('gallery-json').textContent);
    if (photos.length <= 1) return;

    let current = 0;
    const hero      = document.getElementById('prov-hero-img');
    const heroVideoWrap   = document.getElementById('prov-hero-video-wrap');
    const heroVideoIframe = document.getElementById('prov-hero-video-iframe');
    const heroVideoNative = document.getElementById('prov-hero-video-native');
    const zoomHint  = document.getElementById('prov-zoom-hint');
    const counter   = document.getElementById('prov-counter');
    const total     = photos.length;
    const pad       = n => String(n).padStart(2, '0');

    const ACTIVE   = ['ring-2','ring-orange-400','scale-105','shadow-lg','shadow-orange-500/20'];
    const INACTIVE = ['opacity-45'];

    function go(idx) {
        current = (idx + total) % total;
        const item = photos[current];

        // Animate image
        hero.style.animation = 'none';
        hero.offsetHeight;
        hero.style.animation = 'heroFade .6s cubic-bezier(.22,1,.36,1) both';

        if (item.type === 'video') {
            heroVideoWrap?.classList.remove('hidden');
            hero.classList.add('hidden');
            zoomHint?.classList.add('hidden');
            if (item.embed_url) {
                heroVideoIframe?.classList.remove('hidden');
                if (heroVideoIframe) heroVideoIframe.src = item.embed_url + '?autoplay=1&mute=1&muted=1&playsinline=1';
                heroVideoNative?.classList.add('hidden');
                if (heroVideoNative) heroVideoNative.src = '';
            } else {
                heroVideoNative?.classList.remove('hidden');
                if (heroVideoNative) { heroVideoNative.src = item.url; heroVideoNative.play?.().catch(() => {}); }
                heroVideoIframe?.classList.add('hidden');
                if (heroVideoIframe) heroVideoIframe.src = '';
            }
        } else {
            heroVideoWrap?.classList.add('hidden');
            if (heroVideoIframe) heroVideoIframe.src = '';
            if (heroVideoNative) heroVideoNative.src = '';
            hero.classList.remove('hidden');
            hero.src = item.url;
            hero.alt = item.alt;
            zoomHint?.classList.remove('hidden');
        }

        if (counter) counter.textContent = pad(current + 1) + ' / ' + pad(total);

        document.querySelectorAll('[id^="prov-thumb-"]').forEach((btn, i) => {
            if (i === current) {
                btn.classList.add(...ACTIVE);
                btn.classList.remove(...INACTIVE);
            } else {
                btn.classList.remove(...ACTIVE);
                btn.classList.add(...INACTIVE);
            }
        });

        document.getElementById('prov-thumb-' + current)
            ?.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
    }

    window.provGoTo  = go;
    window.provSlide = (dir) => go(current + dir);
})();

// ── Avis clients : voir plus / voir moins ───────────────────────────────────
(function () {
    const toggle = document.getElementById('reviews-toggle');
    const more = document.getElementById('reviews-more');
    if (!toggle || !more) return;

    const moreLabel = toggle.dataset.moreLabel;
    const lessLabel = toggle.dataset.lessLabel;
    const label = toggle.querySelector('span');
    const icon = toggle.querySelector('i');

    toggle.addEventListener('click', () => {
        const nowHidden = more.classList.toggle('hidden');
        label.textContent = nowHidden ? moreLabel : lessLabel;
        icon.classList.toggle('fa-chevron-down', nowHidden);
        icon.classList.toggle('fa-chevron-up', !nowHidden);
    });
})();

// ── Révélation au défilement ─────────────────────────────────────────────
(function () {
    const items = document.querySelectorAll('.reveal:not(.is-visible)');
    if (!('IntersectionObserver' in window) || !items.length) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    items.forEach(el => io.observe(el));
})();
</script>
@include('partials.image-protection')
</body>
</html>
