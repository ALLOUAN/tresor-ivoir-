<!DOCTYPE html>
<html lang="fr" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Galerie Trésors d'Ivoire — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Découvrez la galerie photo {{ $siteBrand['site_name'] }} : paysages, culture et art de vivre en Côte d'Ivoire.">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:wght@300;400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif:   ['Playfair Display', 'Georgia', 'serif'],
                        elegant: ['Cormorant Garamond', 'Georgia', 'serif'],
                        sans:    ['Inter', 'system-ui', 'sans-serif'],
                        plus:    ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        gold: { 300:'#fdbe7b', 400:'#fa9a3c', 500:'#f2790f', 600:'#d4630a' },
                        dark: { 600:'#e9e5d9', 700:'#e9e5d9', 800:'#e9e5d9', 900:'#e9e5d9' },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }
        .font-plus    { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background:#e9e5d9; }
        ::-webkit-scrollbar-thumb { background: #f2790f; border-radius: 3px; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .gold-line::after {
            content: '';
            display: block;
            width: 52px;
            height: 2px;
            background: linear-gradient(90deg, #f2790f, #fa9a3c);
            margin-top: 10px;
        }
        .gallery-mesh {
            background-color:#e9e5d9;
            background-image:
                radial-gradient(ellipse 120% 80% at 10% -10%, rgba(242, 121, 15, 0.14), transparent 55%),
                radial-gradient(ellipse 90% 60% at 100% 0%, rgba(242, 121, 15, 0.06), transparent 50%),
                radial-gradient(ellipse 70% 50% at 50% 110%, rgba(242, 121, 15, 0.05), transparent 55%);
        }
        .gallery-grid-noise {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
        }
        .gallery-card-modern { transition: transform .55s cubic-bezier(0.22, 1, 0.36, 1), box-shadow .55s ease, border-color .35s ease; }
        .gallery-card-modern:hover { transform: translateY(-6px); }
        .gallery-img { transition: transform .75s cubic-bezier(0.22, 1, 0.36, 1); }
        .gallery-card-modern:hover .gallery-img { transform: scale(1.07); }
        .gallery-shine {
            pointer-events: none;
            position: absolute;
            inset: 0;
            z-index: 2;
            opacity: 0;
            transition: opacity .4s ease;
            background: linear-gradient(125deg, transparent 42%, rgba(255,255,255,0.12) 50%, transparent 58%);
            mix-blend-mode: overlay;
        }
        .gallery-card-modern:hover .gallery-shine { opacity: 1; }
        .gallery-title-gradient {
            background: linear-gradient(135deg, #fff 0%, #fdbe7b 45%, #f2790f 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .gallery-hero-shell {
            background-color: #050403;
            background-image:
                linear-gradient(135deg, rgba(255, 255, 255,0.97) 0%, rgba(26,21,6,0.92) 42%, rgba(21,18,8,0.95) 100%),
                radial-gradient(ellipse 100% 60% at 0% 0%, rgba(242, 121, 15, 0.18), transparent 52%),
                radial-gradient(ellipse 80% 50% at 100% 20%, rgba(242, 121, 15, 0.07), transparent 48%),
                radial-gradient(ellipse 60% 40% at 50% 100%, rgba(242, 121, 15, 0.06), transparent 55%);
        }
        /* Image de fond configurable (back-office) : posée sur le calque assombrissant
           (voir .gallery-hero-bgphoto plus bas) plutôt que sur .gallery-hero-shell,
           qui est entièrement recouvert par ce calque opaque. */
        .gallery-hero-bgphoto {
            background-image:
                linear-gradient(to bottom right, rgba(13,9,4,.78), rgba(7,7,12,.78), rgba(13,9,4,.78)),
                var(--gallery-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .gallery-hero-grid {
            background-image:
                repeating-linear-gradient(45deg, rgba(242, 121, 15, 0.04) 0, rgba(242, 121, 15, 0.04) 1px, transparent 0, transparent 14px);
        }
        .gallery-hero-title {
            background: linear-gradient(125deg, #e9e5d9 0%, #fef9c3 28%, #fa9a3c 52%, #9f4709 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
        }
        .gallery-hero-glow {
            filter: blur(64px);
            opacity: 0.85;
        }
        html:not(.dark) .gallery-hero-shell {
            background-color:#e9e5d9;
            background-image:
                linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(248,244,236,0.96) 42%, rgba(245,239,228,0.98) 100%),
                radial-gradient(ellipse 100% 60% at 0% 0%, rgba(242, 121, 15,0.14), transparent 52%);
            border-bottom-color: rgba(0,0,0,0.08);
        }
        html:not(.dark) .gallery-title-gradient {
            background: none;
            color: #1c1915;
            -webkit-text-fill-color: #1c1915;
        }
        html:not(.dark) .gallery-mesh {
            background-color:#e9e5d9;
            background-image:
                radial-gradient(ellipse 120% 80% at 10% -10%, rgba(242, 121, 15, 0.08), transparent 55%);
        }
        html:not(.dark) .gallery-card-modern {
            border-color: rgba(0,0,0,0.1) !important;
            background:#e9e5d9 !important;
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        }
        html:not(.dark) .gallery-card-modern:hover {
            border-color: rgba(194, 94, 10,0.35) !important;
            box-shadow: 0 16px 28px rgba(194, 94, 10,0.12);
        }
        html:not(.dark) .gallery-card-modern .text-white { color:#1c1915 !important; }
        html:not(.dark) .gallery-card-modern .text-gray-500 { color:#544f47 !important; }
        .gallery-selection-badge {
            border-color: rgba(255,255,255,0.16);
            background: rgba(0,0,0,0.3);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .gallery-selection-copy {
            color: rgba(243,244,246,0.98);
            text-shadow: 0 2px 10px rgba(0,0,0,0.45);
        }
        .gallery-selection-note {
            color: rgba(229,231,235,0.94);
        }
        html:not(.dark) .gallery-selection-badge {
            border-color: rgba(255, 255, 255,0.12);
            background: rgba(233, 229, 217, 0.9);
            box-shadow: 0 8px 24px -16px rgba(255, 255, 255,0.2), inset 0 1px 0 rgba(255,255,255,0.9);
        }
        html:not(.dark) .gallery-selection-copy {
            color: #374151;
            text-shadow: none;
        }
        html:not(.dark) .gallery-selection-note {
            color: #4b5563;
        }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">

@include('partials.public-top-nav')

@php
    $galleryHeroImage = \Illuminate\Support\Facades\Schema::hasTable('gallery_hero_images')
        ? \App\Models\GalleryHeroImage::query()->find(1)
        : null;
@endphp

<section class="relative isolate overflow-hidden border-b border-white/[0.08] gallery-hero-shell pt-12 sm:pt-14 pb-6 sm:pb-8 bg-[#050507]">
    <div class="pointer-events-none absolute inset-0 gallery-hero-bgphoto"
        @if($galleryHeroImage && $galleryHeroImage->isVisible())
            style="--gallery-hero-bg-image: url('{{ $galleryHeroImage->image_url }}');"
        @endif
    ></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_18%_22%,rgba(250,154,60,0.24),transparent_42%),radial-gradient(circle_at_84%_14%,rgba(250,154,60,0.16),transparent_36%),radial-gradient(circle_at_55%_78%,rgba(250,154,60,0.14),transparent_40%)]"></div>
    <div class="pointer-events-none absolute inset-0 gallery-grid-noise opacity-[0.38] mix-blend-soft-light"></div>
    <div class="pointer-events-none absolute inset-0 gallery-hero-grid opacity-[0.24]"></div>
    <div class="pointer-events-none absolute left-1/2 top-0 h-[26rem] w-[min(92%,72rem)] -translate-x-1/2 bg-gradient-to-b from-white/[0.16] via-white/[0.03] to-transparent blur-3xl"></div>
    <div class="pointer-events-none absolute -left-28 top-1/4 h-[24rem] w-[24rem] rounded-full bg-orange-500/25 blur-3xl gallery-hero-glow"></div>
    <div class="pointer-events-none absolute -right-24 top-[-2rem] h-[22rem] w-[22rem] rounded-full bg-orange-500/20 blur-3xl gallery-hero-glow"></div>
    <div class="pointer-events-none absolute bottom-[-5rem] left-1/2 h-56 w-[min(94%,50rem)] -translate-x-1/2 rounded-full bg-orange-400/15 blur-3xl"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6">
        <nav class="reveal mb-5 inline-flex flex-wrap items-center gap-1.5 rounded-full border border-white/[0.14] bg-[#1a0f04]/25 px-1.5 py-1.5 backdrop-blur-md shadow-[inset_0_1px_0_0_rgba(255,255,255,0.08)] font-plus text-[11px] sm:text-xs">
            <a href="{{ route('home') }}" class="rounded-full px-3 py-1.5 text-gray-200 hover:text-white hover:bg-white/[0.08] transition">Accueil</a>
            <span class="text-white/35 select-none" aria-hidden="true">/</span>
            <span class="rounded-full bg-gradient-to-r from-orange-400/30 to-orange-500/20 px-3 py-1.5 font-semibold text-orange-100 border border-orange-300/35">Galerie</span>
        </nav>

        <div class="max-w-3xl relative rounded-3xl border border-white/10 bg-[#1a0f04]/85 backdrop-blur-md px-5 py-5 sm:px-7 sm:py-6 shadow-[0_20px_60px_rgba(0,0,0,0.45)]">
            <div class="pointer-events-none absolute inset-0 rounded-3xl bg-gradient-to-br from-[#1a0f04]/90 via-[#1a0f04]/80 to-[#1a0f04]/90"></div>
            <div class="pointer-events-none absolute inset-0 rounded-3xl shadow-[inset_0_1px_0_rgba(255,255,255,0.12)]"></div>
            <div class="reveal mb-4 inline-flex items-center gap-2.5 rounded-full border border-orange-400/35 bg-gradient-to-r from-orange-500/20 via-orange-400/10 to-orange-500/10 px-4 py-1.5 backdrop-blur-sm">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-50"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-orange-400 shadow-[0_0_12px_rgba(250, 154, 60,0.8)]"></span>
                </span>
                <span class="text-[10px] sm:text-[11px] font-plus font-bold uppercase tracking-[0.28em] text-orange-100 [text-shadow:0_1px_3px_rgba(0,0,0,0.45)]">Photographie</span>
            </div>

            <h1 class="reveal relative z-10 font-serif text-3xl sm:text-4xl md:text-5xl font-semibold leading-[1.05] sm:leading-[1.02] gallery-hero-title pb-1 text-white [text-shadow:0_8px_30px_rgba(0,0,0,0.65)]">
                Galerie Trésors d'Ivoire
            </h1>

            <div class="reveal relative z-10 mt-4 h-px w-16 sm:w-20 rounded-full bg-gradient-to-r from-orange-300 via-orange-400/80 to-transparent"></div>

            <p class="relative z-10 mt-4 sm:mt-5 inline-block rounded-2xl border border-orange-200/40 bg-[#1a0f04]/75 px-4 py-2.5 sm:px-5 sm:py-3 backdrop-blur-md text-sm sm:text-base text-orange-50 font-plus font-bold leading-relaxed max-w-2xl tracking-normal shadow-[0_14px_40px_rgba(0,0,0,0.55),0_0_22px_rgba(250, 154, 60,0.12)] [text-shadow:0_0_14px_rgba(255,235,180,0.28),0_4px_18px_rgba(0,0,0,0.82)]">
                Un regard sur la Côte d'Ivoire : instants choisis par la rédaction, disponibles en pleine page.
            </p>
        </div>
    </div>
</section>

<section class="relative isolate py-16 sm:py-24 border-t border-white/[0.06] gallery-mesh overflow-hidden">
    <div class="pointer-events-none absolute inset-0 gallery-grid-noise opacity-90"></div>
    <div class="pointer-events-none absolute -top-32 left-1/2 h-[28rem] w-[min(100%,56rem)] -translate-x-1/2 rounded-full bg-gradient-to-b from-orange-500/15 via-orange-600/5 to-transparent blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-0 h-64 w-64 rounded-full bg-green-600/5 blur-3xl"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6">
        @if(($galleryImages ?? collect())->isEmpty())
            <div class="text-center py-24 sm:py-28 rounded-[1.75rem] border border-dashed border-white/10 bg-white/[0.02] backdrop-blur-xl shadow-[0_0_0_1px_rgba(255,255,255,0.04)_inset] reveal">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl border border-orange-500/20 bg-gradient-to-br from-orange-500/20 to-transparent">
                    <i class="fas fa-images text-2xl text-orange-400/80"></i>
                </div>
                <p class="text-white/90 font-plus text-lg font-medium tracking-tight">La galerie sera bientôt enrichie.</p>
                <p class="text-gray-300 text-sm mt-3 max-w-md mx-auto leading-relaxed">Publiez des images depuis l’administration (section « Accueil - Galerie »), actives et datées.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mt-10 px-6 py-3 rounded-full bg-gradient-to-r from-orange-400 to-orange-500 text-black text-sm font-bold shadow-lg shadow-orange-900/30 hover:from-orange-300 hover:to-orange-400 transition">
                    <i class="fas fa-arrow-left text-xs"></i> Retour à l’accueil
                </a>
            </div>
        @else
            <div class="mb-12 sm:mb-16 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between reveal">
                <div class="max-w-2xl">
                    <div class="gallery-selection-badge inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 backdrop-blur-md mb-5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-60"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-400"></span>
                        </span>
                        <span class="gallery-selection-copy text-[11px] font-plus font-semibold uppercase tracking-[0.2em]">{{ $galleryImages->count() }} visuel{{ $galleryImages->count() > 1 ? 's' : '' }}</span>
                    </div>
                    <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight leading-[1.1] gallery-title-gradient">
                        Sélection
                    </h2>
                    <p class="gallery-selection-note mt-4 text-sm sm:text-base font-plus leading-relaxed max-w-lg">
                        Cliquez sur une carte pour la fiche détaillée — survolez pour un aperçu dynamique.
                    </p>
                </div>
                <div class="gallery-selection-copy hidden sm:flex items-center gap-2 text-xs font-plus uppercase tracking-widest">
                    <i class="fas fa-grip text-orange-500/40"></i>
                    Grille adaptative
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6 lg:gap-7">
                @foreach($galleryImages as $img)
                    @php
                        $alt = trim((string) ($img->alt_text ?? ''));
                        if ($alt === '') {
                            $alt = trim((string) ($img->title ?? $img->original_name ?? 'Photo'));
                        }
                        $title = trim((string) ($img->title ?? ''));
                        $caption = trim((string) ($img->caption ?? ''));
                        $credit = trim((string) ($img->credit ?? ''));
                        $price = isset($img->price) && $img->price !== null && (float) $img->price > 0
                            ? number_format((float) $img->price, 0, ',', ' ') . ' FCFA'
                            : null;
                    @endphp
                    <article class="gallery-card-modern group reveal relative rounded-[1.35rem] overflow-hidden border border-white/[0.08] bg-white/[0.03] backdrop-blur-2xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.35),0_24px_48px_-12px_rgba(0,0,0,0.55),inset_0_1px_0_0_rgba(255,255,255,0.06)] hover:border-orange-400/25 hover:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.25),0_32px_64px_-16px_rgba(242, 121, 15,0.12),inset_0_1px_0_0_rgba(255,255,255,0.1)]">
                        <a href="{{ filled($img->uuid) ? route('gallery.public.show', $img->uuid) : url($img->url) }}"
                           @if(!filled($img->uuid)) target="_blank" rel="noopener noreferrer" @endif
                           class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-400/60 focus-visible:ring-offset-2 focus-visible:ring-offset-[#ffffff]">
                            <div class="relative aspect-[4/3] overflow-hidden bg-[#1a0f04]">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#1a0f04]/80 via-[#1a0f04]/20 to-transparent z-[1] opacity-90"></div>
                                <div class="gallery-shine"></div>
                                <img src="{{ url($img->url) }}" alt="{{ $alt }}" loading="lazy" decoding="async"
                                     class="gallery-img relative z-0 w-full h-full object-cover">
                                <div class="absolute bottom-3 right-3 z-[3] flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-[#1a0f04]/60 text-white/90 backdrop-blur-md opacity-0 translate-y-1 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0">
                                    <i class="fas fa-arrow-up-right text-xs"></i>
                                </div>
                            </div>
                            <div class="relative px-5 py-4 sm:px-6 sm:py-5 border-t border-white/15 bg-gradient-to-b from-[#1a0f04]/75 via-[#1a0f04]/55 to-[#1a0f04]/30 backdrop-blur-md">
                                @if($title !== '')
                                    <p class="text-white font-serif text-[15px] sm:text-[17px] font-semibold leading-snug tracking-tight [text-shadow:0_3px_12px_rgba(0,0,0,0.6)] group-hover:text-orange-100 transition-colors">{{ $title }}</p>
                                @else
                                    <p class="text-gray-100/90 font-plus text-xs uppercase tracking-widest">Sans titre</p>
                                @endif
                                @if($caption !== '')
                                    <p class="text-gray-100/95 text-[13px] mt-2 leading-relaxed line-clamp-2 font-plus [text-shadow:0_2px_8px_rgba(0,0,0,0.45)]">{{ $caption }}</p>
                                @endif
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($credit !== '')
                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/25 bg-[#1a0f04]/65 px-2.5 py-1 text-[10px] font-semibold text-gray-100 font-plus shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]">
                                                <i class="fas fa-camera text-orange-300 text-[9px]"></i>{{ $credit }}
                                            </span>
                                        @endif
                                        @if($price)
                                            <span class="inline-flex items-center rounded-full border border-orange-300/35 bg-orange-400/20 px-2.5 py-0.5 text-[10px] font-bold tracking-wide text-orange-100 font-plus shadow-[0_0_12px_rgba(250,154,60,0.25)]">{{ $price }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-plus font-bold uppercase tracking-[0.15em] text-orange-200/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden sm:inline [text-shadow:0_2px_8px_rgba(0,0,0,0.45)]">
                                        Fiche
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<script>
    const reveals = document.querySelectorAll('.reveal');
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });
    reveals.forEach(el => obs.observe(el));
</script>

@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
