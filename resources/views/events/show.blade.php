<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .reveal, .reveal.is-visible { opacity: 1 !important; transform: none !important; transition: none !important; }
        }

        /* ── Hero cinématique ─────────────────────────────────────────── */
        .hero-cinematic { background: #14130f; }
        .hero-cinematic img { transition: transform 12s ease; transform: scale(1.02); }
        .hero-cinematic:hover img { transform: scale(1.08); }
        .hero-scrim {
            background:
                linear-gradient(to top, rgba(10,9,7,0.96) 0%, rgba(10,9,7,0.55) 42%, rgba(10,9,7,0.08) 68%, rgba(10,9,7,0.25) 100%),
                linear-gradient(to right, rgba(10,9,7,0.35), transparent 45%);
        }
        .hero-fallback {
            background: #14130f;
        }
        .glass-pill {
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .back-pill { transition: background .2s ease, border-color .2s ease, transform .2s ease; }
        .back-pill:hover { background: rgba(255,255,255,0.16); border-color: rgba(242,121,15,0.5); transform: translateX(-2px); }
        .grad-text {
            background: linear-gradient(100deg, #ffffff 30%, #f8d79a 65%, #fa9a3c 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .countdown-chip {
            border: 1px solid rgba(242,121,15,0.5);
            background: linear-gradient(135deg, rgba(242,121,15,0.28), rgba(0,0,0,0.15));
            backdrop-filter: blur(10px);
        }
        .countdown-dot { animation: blinkDot 1.4s ease-in-out infinite; }
        @keyframes blinkDot { 0%, 100% { opacity: 1; } 50% { opacity: .25; } }
        .scroll-cue { animation: bounceCue 2.2s ease-in-out infinite; }
        @keyframes bounceCue { 0%, 100% { transform: translateY(0); opacity: .7; } 50% { transform: translateY(8px); opacity: 1; } }

        /* ── Boutons ───────────────────────────────────────────────────── */
        .btn-primary {
            background: #f2790f;
            box-shadow: 0 12px 28px rgba(242,121,15,0.35);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
            color: #1b1408;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); box-shadow: 0 16px 36px rgba(242,121,15,0.45); }
        .btn-glass {
            border: 1px solid rgba(255,255,255,0.24);
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            transition: transform .2s ease, background .2s ease, border-color .2s ease;
            color: #f5f0e6;
        }
        .btn-glass:hover { transform: translateY(-2px); background: rgba(255,255,255,0.16); border-color: rgba(242,121,15,0.55); }

        /* ── Scroll-reveal ─────────────────────────────────────────────── */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1); }
        .reveal.is-visible { opacity: 1; transform: none; }

        /* ── Bento infos ───────────────────────────────────────────────── */
        .bento-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 30px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .bento-card:hover { transform: translateY(-4px); box-shadow: 0 16px 38px rgba(194,94,10,0.14); border-color: rgba(242,121,15,0.3); }
        .bento-icon {
            width: 2.4rem; height: 2.4rem;
            display: flex; align-items: center; justify-content: center;
            border-radius: 0.85rem;
            background: linear-gradient(135deg, rgba(242,121,15,0.18), rgba(242,121,15,0.06));
            color: #d4630a;
        }

        /* ── Sections ──────────────────────────────────────────────────── */
        .section-kicker { letter-spacing: .22em; }
        .prose-content { color: #2a2620; line-height: 1.85; }
        .prose-content img { border-radius: 1rem; margin: 1.25rem 0; box-shadow: 0 12px 30px rgba(20,18,12,0.12); }
        .prose-content h2, .prose-content h3 { font-family: 'Playfair Display', serif; color: #171310; margin-top: 1.4em; }
        .prose-content a { color: #c25e0a; text-decoration: underline; text-underline-offset: 3px; }

        /* ── Timeline programme ────────────────────────────────────────── */
        .stepper { position: relative; }
        .stepper::before {
            content: '';
            position: absolute; left: 1.05rem; top: .4rem; bottom: .4rem; width: 2px;
            background: linear-gradient(to bottom, rgba(242,121,15,0.45), rgba(242,121,15,0.05));
        }
        .step-row { position: relative; padding-left: 3rem; }
        .step-num {
            position: absolute; left: 0; top: 0;
            width: 2.15rem; height: 2.15rem;
            border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem; font-weight: 800;
            background: linear-gradient(135deg, #f4c65a, #f2790f);
            color: #1b1408;
            box-shadow: 0 6px 16px rgba(242,121,15,0.35);
        }
        .step-card {
            border: 1px solid rgba(0,0,0,0.06);
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(20,18,12,0.05);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .step-card:hover { transform: translateX(3px); box-shadow: 0 12px 28px rgba(194,94,10,0.12); }

        /* ── Galerie ───────────────────────────────────────────────────── */
        .gallery-tile { position: relative; overflow: hidden; cursor: pointer; border-radius: 1rem; }
        .gallery-tile img { transition: transform .5s ease, filter .5s ease; }
        .gallery-tile:hover img { transform: scale(1.08); }
        .gallery-tile::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.45), transparent 55%);
            opacity: 0; transition: opacity .3s ease;
        }
        .gallery-tile:hover::after { opacity: 1; }
        .gallery-tile .zoom-hint {
            position: absolute; bottom: .6rem; right: .6rem;
            width: 2rem; height: 2rem; border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.9); color: #171310;
            opacity: 0; transform: scale(.7); transition: opacity .25s ease, transform .25s ease;
        }
        .gallery-tile:hover .zoom-hint { opacity: 1; transform: scale(1); }

        /* ── Carte Localisation ────────────────────────────────────────── */
        .map-card { border: 1px solid rgba(0,0,0,0.07); box-shadow: 0 14px 34px rgba(20,18,12,0.08); }

        /* ── Événements liés (scroll horizontal mobile) ───────────────── */
        .related-scroll { scrollbar-width: none; }
        .related-scroll::-webkit-scrollbar { display: none; }
        .related-tile {
            border: 1px solid rgba(0,0,0,0.07);
            background: #ffffff;
            box-shadow: 0 10px 26px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .related-tile:hover { transform: translateY(-4px); border-color: rgba(242,121,15,0.4); box-shadow: 0 16px 34px rgba(194,94,10,0.16); }

        /* ── Sidebar sticky ────────────────────────────────────────────── */
        .booking-card {
            border: 1px solid rgba(0,0,0,0.08);
            background: linear-gradient(180deg, #ffffff, #fbf7ee);
            box-shadow: 0 20px 50px rgba(20,18,12,0.1);
        }
        .share-chip {
            width: 2.35rem; height: 2.35rem;
            border: 1px solid rgba(0,0,0,0.09);
            background: #ffffff;
            transition: transform .2s ease, background .2s ease, color .2s ease, border-color .2s ease;
            color: #52493c;
        }
        .share-chip:hover { transform: translateY(-2px); background: #fdf0de; color: #c25e0a; border-color: rgba(242,121,15,0.4); }

        /* ── Barre mobile fixe ─────────────────────────────────────────── */
        .mobile-cta-bar {
            border-top: 1px solid rgba(0,0,0,0.08);
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(14px);
            box-shadow: 0 -10px 26px rgba(20,18,12,0.1);
            padding-bottom: env(safe-area-inset-bottom, 0);
        }

        /* ── Favoris ───────────────────────────────────────────────────── */
        .fav-btn { transition: transform .2s ease, background .2s ease, color .2s ease; }
        .fav-btn:hover { transform: translateY(-1px); }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    {{-- ══ HERO CINÉMATIQUE ══════════════════════════════════════════════ --}}
    <section class="hero-cinematic relative w-full overflow-hidden" style="height: min(80vh, 660px); min-height: 460px;">
        @if(!empty($event->cover_url))
            <img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?: $event->title_fr }}" class="absolute inset-0 w-full h-full object-cover" decoding="async">
        @else
            <div class="hero-fallback absolute inset-0"></div>
        @endif
        <div class="hero-scrim absolute inset-0"></div>

        <a href="{{ route('events.index') }}" class="back-pill glass-pill absolute top-5 left-4 sm:left-6 z-20 inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold text-white">
            <i class="fas fa-arrow-left text-[10px]"></i> Agenda
        </a>

        <div class="absolute inset-0 flex flex-col justify-end z-10">
            <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 pb-10 sm:pb-14">
                <div class="flex flex-wrap items-center gap-2 mb-4 reveal is-visible">
                    <span class="glass-pill inline-flex items-center rounded-full px-3.5 py-1.5 text-[10px] uppercase tracking-[0.18em] text-orange-100 font-bold">
                        <i class="fas fa-masks-theater mr-1.5"></i>{{ $event->category->name_fr ?? 'Événement' }}
                    </span>
                    <span id="countdownChip" class="countdown-chip hidden items-center rounded-full px-3.5 py-1.5 text-[10px] uppercase tracking-[0.1em] text-orange-100 font-bold">
                        <i class="fas fa-circle countdown-dot text-[6px] mr-2"></i><span id="countdownText"></span>
                    </span>
                </div>

                <h1 class="grad-text font-serif text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.05] max-w-4xl">{{ $event->title_fr }}</h1>
                @if($event->subtitle_fr)
                <p class="mt-4 text-white/85 text-base sm:text-xl font-serif italic max-w-2xl">{{ $event->subtitle_fr }}</p>
                @endif

                <div class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-white/85 text-sm">
                    <span class="inline-flex items-center gap-2"><i class="fas fa-calendar-days text-orange-300"></i>{{ $event->starts_at?->format('d/m/Y à H:i') }}</span>
                    <span class="inline-flex items-center gap-2"><i class="fas fa-location-dot text-orange-300"></i>{{ $event->location_name ?: $event->city ?: 'Côte d\'Ivoire' }}</span>
                </div>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ $event->ticket_url ?: '#details' }}"
                       @if($event->ticket_url) target="_blank" rel="noopener noreferrer" @endif
                       class="btn-primary btn-shine inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-extrabold">
                        <i class="fas fa-ticket-simple"></i>
                        @if($event->ticket_url) Réserver ma place @else En savoir plus @endif
                    </a>
                    <a href="{{ route('events.ics', $event->slug) }}" class="btn-glass inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold">
                        <i class="fas fa-calendar-plus"></i> Mon agenda
                    </a>
                    <a href="#partager" class="btn-glass inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold">
                        <i class="fas fa-share-nodes"></i> Partager
                    </a>

                    @auth
                        @if(auth()->user()->role === 'visitor')
                            @if(!($isFavorited ?? false))
                                <form method="POST" action="{{ route('visitor.favorites.store') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="type" value="event">
                                    <input type="hidden" name="id" value="{{ $event->id }}">
                                    <button type="submit" class="fav-btn glass-pill w-12 h-12 rounded-xl flex items-center justify-center text-white/90" title="Ajouter à ma wishlist">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </form>
                            @else
                                <span class="glass-pill w-12 h-12 rounded-xl flex items-center justify-center text-orange-300" title="Dans vos favoris">
                                    <i class="fas fa-heart"></i>
                                </span>
                            @endif
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <a href="#details" class="scroll-cue absolute bottom-5 left-1/2 -translate-x-1/2 z-20 w-9 h-9 rounded-full glass-pill flex items-center justify-center text-white/80">
            <i class="fas fa-chevron-down text-xs"></i>
        </a>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6">

        {{-- ══ BENTO INFOS (chevauche le hero) ═════════════════════════════ --}}
        @php
            $now = now();
            $eventEnd = $event->ends_at ?? $event->starts_at;
            if ($now->lt($event->starts_at)) {
                $computedStatus = 'À venir';
            } elseif ($now->lte($eventEnd)) {
                $computedStatus = 'En cours';
            } else {
                $computedStatus = 'Terminé';
            }
        @endphp
        <div id="details" class="-mt-10 sm:-mt-14 relative z-20 grid grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 reveal scroll-mt-24">
            <div class="bento-card rounded-2xl p-4">
                <div class="bento-icon mb-3"><i class="fas fa-calendar-days"></i></div>
                <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold">Date &amp; heure</p>
                <p class="font-semibold text-sm mt-1 leading-snug">
                    {{ $event->starts_at?->format('d/m/Y H:i') }}
                    @if($event->ends_at)<br><span class="text-[#8a7f6b] font-normal">au {{ $event->ends_at->format('d/m/Y H:i') }}</span>@endif
                </p>
            </div>
            <div class="bento-card rounded-2xl p-4">
                <div class="bento-icon mb-3"><i class="fas fa-location-dot"></i></div>
                <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold">Lieu</p>
                <p class="font-semibold text-sm mt-1 leading-snug">{{ $event->location_name ?: 'Non précisé' }}<br><span class="text-[#8a7f6b] font-normal">{{ $event->city ?: 'Côte d\'Ivoire' }}</span></p>
            </div>
            <div class="bento-card rounded-2xl p-4">
                <div class="bento-icon mb-3"><i class="fas fa-masks-theater"></i></div>
                <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold">Catégorie</p>
                <p class="font-semibold text-sm mt-1">{{ $event->category->name_fr ?? 'Événement' }}</p>
            </div>
            @if($event->audience)
            <div class="bento-card rounded-2xl p-4">
                <div class="bento-icon mb-3"><i class="fas fa-users"></i></div>
                <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold">Public concerné</p>
                <p class="font-semibold text-sm mt-1">{{ $event->audience }}</p>
            </div>
            @endif
            <div class="bento-card rounded-2xl p-4">
                <div class="bento-icon mb-3"><i class="fas fa-circle-info"></i></div>
                <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold">Statut</p>
                <p class="font-semibold text-sm mt-1">{{ $computedStatus }}</p>
                <p class="text-[#c25e0a] text-xs font-bold mt-1">
                    @if($event->is_free || (float) ($event->price ?? 0) <= 0) Gratuit
                    @else {{ number_format((float) $event->price, 0, ',', ' ') }} FCFA @endif
                </p>
            </div>
        </div>

        {{-- ══ CONTENU + SIDEBAR ═══════════════════════════════════════════ --}}
        <div class="mt-14 grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-12 items-start pb-16">
            <div class="space-y-16 min-w-0">

                {{-- À propos --}}
                @if($event->description_fr)
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Présentation</p>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-5">À propos de cet événement</h2>
                    <div class="prose-content max-w-none">{!! \App\Support\HtmlSanitizer::articleBody($event->description_fr) !!}</div>
                </section>
                @endif

                {{-- Programme --}}
                @if($event->hasProgram())
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Déroulé</p>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-7">Programme</h2>
                    <div class="stepper space-y-5">
                        @foreach($event->program as $item)
                        <div class="step-row">
                            <div class="step-num">{{ $loop->iteration }}</div>
                            <div class="step-card rounded-xl p-4">
                                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                    @if(!empty($item['time']))
                                    <span class="text-[#c25e0a] font-bold text-sm">{{ $item['time'] }}</span>
                                    @endif
                                    <span class="font-semibold text-sm">{{ $item['title'] ?? '' }}</span>
                                </div>
                                @if(!empty($item['description']))
                                <p class="text-[#5c5548] text-sm mt-1.5">{{ $item['description'] }}</p>
                                @endif
                                @if(!empty($item['speaker']) || !empty($item['location']))
                                <p class="text-[#8a7f6b] text-xs mt-2 flex flex-wrap gap-x-4">
                                    @if(!empty($item['speaker']))<span><i class="fas fa-microphone text-[#d4630a]/70 mr-1"></i>{{ $item['speaker'] }}</span>@endif
                                    @if(!empty($item['location']))<span><i class="fas fa-location-dot text-[#d4630a]/70 mr-1"></i>{{ $item['location'] }}</span>@endif
                                </p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- Galerie --}}
                @if($photos->isNotEmpty() || $videos->isNotEmpty())
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Ambiance</p>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Galerie</h2>

                    @if($photos->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                        @foreach($photos as $photo)
                        <div class="gallery-tile {{ $loop->first ? 'col-span-2 row-span-2 aspect-square sm:aspect-[4/3]' : 'aspect-square' }}"
                            onclick="openLightbox('{{ $photo->url }}', '{{ addslashes($photo->caption ?? $event->title_fr) }}')">
                            <img src="{{ $photo->url }}" alt="{{ $photo->alt_text ?: $event->title_fr }}" class="w-full h-full object-cover" loading="lazy">
                            <span class="zoom-hint"><i class="fas fa-expand text-xs"></i></span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($videos->isNotEmpty())
                    <div class="space-y-4 mt-6">
                        @foreach($videos as $video)
                        <div class="rounded-2xl overflow-hidden border border-black/8 aspect-video shadow-lg">
                            <iframe src="{{ $video->url }}" class="w-full h-full" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        </div>
                        @if($video->caption)
                        <p class="text-[#8a7f6b] text-xs">{{ $video->caption }}</p>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </section>
                @endif

                {{-- Localisation --}}
                @if($event->latitude && $event->longitude)
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Accès</p>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Où se déroule l'événement ?</h2>
                    <div class="map-card rounded-2xl overflow-hidden" style="aspect-ratio: 16/8;">
                        <iframe src="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}&output=embed"
                            class="w-full h-full" frameborder="0" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[#5c5548] text-sm">
                            <i class="fas fa-location-dot text-[#d4630a] mr-1.5"></i>
                            {{ $event->address ?: $event->location_name ?: $event->city }}
                        </p>
                        <a href="https://maps.google.com/?q={{ $event->latitude }},{{ $event->longitude }}" target="_blank"
                            class="btn-primary btn-shine inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs">
                            <i class="fas fa-diamond-turn-right"></i> Itinéraire
                        </a>
                    </div>
                </section>
                @endif

                {{-- Événements liés --}}
                @if($related->isNotEmpty())
                <section class="reveal">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">À suivre aussi</p>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-6">Découvrez également</h2>
                    <div class="related-scroll flex lg:grid lg:grid-cols-2 gap-4 overflow-x-auto -mx-4 px-4 lg:mx-0 lg:px-0 pb-2">
                        @foreach($related as $r)
                        <div class="related-tile rounded-2xl overflow-hidden relative shrink-0 w-[78%] sm:w-[46%] lg:w-auto">
                            <a href="{{ route('events.show', $r->slug) }}" class="absolute inset-0 z-[1]" aria-label="Voir {{ $r->title_fr }}"></a>
                            <div class="h-32 bg-[#f0ece1]">
                                @if(!empty($r->cover_url))
                                    <img src="{{ $r->cover_url }}" alt="{{ $r->cover_alt ?: $r->title_fr }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                @else
                                    <div class="hero-fallback h-full w-full flex items-center justify-center">
                                        <i class="fas fa-image text-orange-300/70 text-lg"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-[#c25e0a] text-[10px] uppercase font-bold tracking-wide">{{ $r->category->name_fr ?? 'Événement' }}</p>
                                <p class="font-semibold mt-1 text-sm">{{ $r->title_fr }}</p>
                                <p class="text-[#8a7f6b] text-xs mt-2"><i class="fas fa-calendar text-[#d4630a]/60 mr-1"></i>{{ $r->starts_at?->format('d/m/Y H:i') }}</p>
                                <p class="text-[#8a7f6b] text-xs mt-1"><i class="fas fa-location-dot text-[#d4630a]/60 mr-1"></i>{{ $r->city ?: 'Côte d\'Ivoire' }}</p>
                                <a href="{{ $r->ticket_url ?: route('events.show', $r->slug) }}"
                                   @if($r->ticket_url) target="_blank" rel="noopener noreferrer" @endif
                                   class="btn-primary btn-shine relative z-[2] mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold">
                                    @if($r->ticket_url) <i class="fas fa-ticket-simple text-[10px]"></i> Réserver
                                    @else <i class="fas fa-arrow-right text-[10px]"></i> Voir détails @endif
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- Partage (mobile / bas de page) --}}
                <section id="partager" class="reveal scroll-mt-24">
                    <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">Diffuser</p>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold mb-5">Partager cet événement</h2>
                    <div class="flex items-center gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener" class="share-chip rounded-full flex items-center justify-center" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://wa.me/?text={{ urlencode($event->title_fr . ' - ' . $canonicalUrl) }}" target="_blank" rel="noopener" class="share-chip rounded-full flex items-center justify-center" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($canonicalUrl) }}&text={{ urlencode($event->title_fr) }}" target="_blank" rel="noopener" class="share-chip rounded-full flex items-center justify-center" title="X"><i class="fab fa-x-twitter"></i></a>
                        <button type="button" onclick="navigator.clipboard.writeText(window.location.href); this.title='Lien copié !'" class="share-chip rounded-full flex items-center justify-center" title="Copier le lien"><i class="fas fa-link"></i></button>
                    </div>
                </section>
            </div>

            {{-- ══ SIDEBAR STICKY ══════════════════════════════════════════ --}}
            <aside class="hidden lg:block lg:sticky lg:top-24 reveal">
                <div class="booking-card rounded-2xl p-6">
                    <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold mb-1">Tarif</p>
                    <p class="font-serif text-3xl font-bold mb-4">
                        @if($event->is_free || (float) ($event->price ?? 0) <= 0) Gratuit
                        @else {{ number_format((float) $event->price, 0, ',', ' ') }} <span class="text-base font-sans font-semibold text-[#8a7f6b]">FCFA</span> @endif
                    </p>
                    <a href="{{ $event->ticket_url ?: '#details' }}"
                       @if($event->ticket_url) target="_blank" rel="noopener noreferrer" @endif
                       class="btn-primary btn-shine w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-extrabold text-sm">
                        <i class="fas fa-ticket-simple"></i>
                        @if($event->ticket_url) Réserver ma place @else En savoir plus @endif
                    </a>
                    <a href="{{ route('events.ics', $event->slug) }}" class="mt-2.5 w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm border border-black/10 hover:border-orange-400/50 hover:bg-orange-50 transition">
                        <i class="fas fa-calendar-plus text-[#c25e0a]"></i> Ajouter à mon agenda
                    </a>

                    <div class="mt-5 pt-5 border-t border-black/8 space-y-3 text-sm">
                        <p class="flex items-start gap-2.5"><i class="fas fa-calendar-days text-[#c25e0a] mt-0.5"></i><span>{{ $event->starts_at?->format('d/m/Y H:i') }}@if($event->ends_at)<br>au {{ $event->ends_at->format('d/m/Y H:i') }}@endif</span></p>
                        <p class="flex items-start gap-2.5"><i class="fas fa-location-dot text-[#c25e0a] mt-0.5"></i><span>{{ $event->location_name ?: 'Non précisé' }}<br>{{ $event->city ?: 'Côte d\'Ivoire' }}</span></p>
                        @if($event->organizer_name)
                        <p class="flex items-start gap-2.5"><i class="fas fa-user-tie text-[#c25e0a] mt-0.5"></i><span>{{ $event->organizer_name }}</span></p>
                        @endif
                    </div>

                    <div class="mt-5 pt-5 border-t border-black/8">
                        <p class="text-[#8a7f6b] text-[10px] uppercase tracking-wide font-semibold mb-2.5">Partager</p>
                        <div class="flex items-center gap-2.5">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener" class="share-chip rounded-full flex items-center justify-center" title="Facebook"><i class="fab fa-facebook-f text-xs"></i></a>
                            <a href="https://wa.me/?text={{ urlencode($event->title_fr . ' - ' . $canonicalUrl) }}" target="_blank" rel="noopener" class="share-chip rounded-full flex items-center justify-center" title="WhatsApp"><i class="fab fa-whatsapp text-xs"></i></a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($canonicalUrl) }}&text={{ urlencode($event->title_fr) }}" target="_blank" rel="noopener" class="share-chip rounded-full flex items-center justify-center" title="X"><i class="fab fa-x-twitter text-xs"></i></a>
                            <button type="button" onclick="navigator.clipboard.writeText(window.location.href); this.title='Lien copié !'" class="share-chip rounded-full flex items-center justify-center" title="Copier le lien"><i class="fas fa-link text-xs"></i></button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- ══ BARRE MOBILE FIXE ═══════════════════════════════════════════════ --}}
    <div class="mobile-cta-bar fixed bottom-0 inset-x-0 z-40 lg:hidden px-4 py-3 flex items-center gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-[#8a7f6b] text-[10px] uppercase font-semibold">Tarif</p>
            <p class="font-bold text-sm truncate">
                @if($event->is_free || (float) ($event->price ?? 0) <= 0) Gratuit
                @else {{ number_format((float) $event->price, 0, ',', ' ') }} FCFA @endif
            </p>
        </div>
        <a href="{{ $event->ticket_url ?: '#details' }}"
           @if($event->ticket_url) target="_blank" rel="noopener noreferrer" @endif
           class="btn-primary btn-shine shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-extrabold text-xs">
            <i class="fas fa-ticket-simple"></i>
            @if($event->ticket_url) Réserver @else En savoir plus @endif
        </a>
    </div>

    {{-- ══ LIGHTBOX ═══════════════════════════════════════════════════════ --}}
    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
        <p id="lightbox-caption" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/60 text-sm"></p>
    </div>

    <footer class="border-t border-black/8 bg-white py-6 mt-4 pb-24 lg:pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-[#8a7f6b]">
            <span>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</span>
            <a href="{{ route('events.index') }}" class="hover:text-[#c25e0a] transition">← Retour aux événements</a>
        </div>
    </footer>
@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
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
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    items.forEach(el => io.observe(el));
})();

// ── Compte à rebours ──────────────────────────────────────────────────────
(function () {
    const startsAt = @json($event->starts_at?->toIso8601String());
    if (!startsAt) return;
    const target = new Date(startsAt).getTime();
    const chip = document.getElementById('countdownChip');
    const text = document.getElementById('countdownText');
    if (!chip || !text) return;

    function tick() {
        const diff = target - Date.now();
        if (diff <= 0) { chip.classList.add('hidden'); return; }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        text.textContent = d > 0
            ? `Débute dans ${d} j ${h} h`
            : (h > 0 ? `Débute dans ${h} h ${m} min` : `Débute dans ${m} min`);
        chip.classList.remove('hidden');
        chip.classList.add('inline-flex');
    }
    tick();
    setInterval(tick, 30000);
})();
</script>
</body>
</html>
