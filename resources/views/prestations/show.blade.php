<!DOCTYPE html>
<html lang="fr" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->banner_title ?: 'Nos Prestations' }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif:   ['Playfair Display', 'Georgia', 'serif'],
                        elegant: ['Cormorant Garamond', 'Georgia', 'serif'],
                    },
                    colors: {
                        gold: { 300:'#fdbe7b', 400:'#fa9a3c', 500:'#f2790f', 600:'#d4630a' },
                        dark: { 500:'#e9e5d9', 600:'#e9e5d9', 700:'#e9e5d9', 800:'#e9e5d9', 900:'#e9e5d9' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        .prestation-cover {
            position: relative;
            background: linear-gradient(180deg, #fdfbf6 0%, #f8f4ec 100%);
            overflow: hidden;
        }
        .prestation-cover::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(60% 50% at 50% 0%, rgba(242,121,15,0.07), transparent 70%);
            pointer-events: none;
        }
        .prestation-cover-frame {
            position: relative;
            max-width: 80rem;
            aspect-ratio: 16 / 7;
            margin-left: auto;
            margin-right: auto;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 30px 60px -15px rgba(28,25,21,0.35), 0 12px 24px -10px rgba(28,25,21,0.18);
            background: #1c1310;
        }
        @media (max-width: 640px) {
            .prestation-cover-frame { aspect-ratio: 3 / 4; }
        }
        .prestation-banner-slide {
            position: absolute; inset: 0;
        }
        .prestation-banner-slide img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .prestation-banner-scrim {
            position: absolute; inset: 0;
            background: linear-gradient(0deg, rgba(10,7,5,0.92) 0%, rgba(10,7,5,0.55) 40%, rgba(10,7,5,0.05) 75%);
        }
        .prestation-banner-copy {
            position: absolute;
            inset-inline: 0;
            bottom: 0;
            padding: 1.5rem 1.5rem 4rem;
        }
        @media (min-width: 640px) {
            .prestation-banner-copy { padding: 2.5rem 3rem 4.5rem; }
        }
        .prestation-cover-eyebrow-rule {
            width: 2.5rem; height: 2px;
            margin-bottom: 1rem;
            background: linear-gradient(90deg, #fb923c, transparent);
        }
        .prestation-cta-primary {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .75rem 1.6rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #fb923c, #d4630a);
            color: #1c1310;
            font-weight: 700; font-size: .8rem;
            text-transform: uppercase; letter-spacing: .03em;
            box-shadow: 0 12px 26px rgba(212,99,10,0.4);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .prestation-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(212,99,10,0.5);
            color: #1c1310;
        }
        .prestation-cta-secondary {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .75rem 1.6rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.35);
            color: #fff;
            font-weight: 600; font-size: .8rem;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(6px);
            transition: background .2s ease, border-color .2s ease;
        }
        .prestation-cta-secondary:hover {
            background: rgba(255,255,255,0.16);
            border-color: rgba(255,255,255,0.6);
            color: #fff;
        }
        .prestation-banner-dots {
            position: absolute;
            left: 50%; bottom: 1.5rem;
            transform: translateX(-50%);
            display: flex; align-items: center; gap: .5rem;
            z-index: 5;
        }
        .prestation-banner-dot {
            width: 8px; height: 8px; border-radius: 999px;
            background: rgba(255,255,255,0.35);
            border: none;
            cursor: pointer;
            transition: width .25s ease, background .25s ease;
        }
        .prestation-banner-dot.is-active {
            width: 26px;
            background: #fb923c;
        }
        .prestation-banner-arrows {
            position: absolute;
            right: 1.25rem; bottom: 1.25rem;
            display: flex; align-items: center; gap: .5rem;
            z-index: 5;
        }
        @media (min-width: 640px) {
            .prestation-banner-arrows { right: 2rem; bottom: 2rem; }
        }
        .prestation-banner-arrow {
            width: 38px; height: 38px;
            border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(28,25,21,0.55);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(6px);
            font-size: .75rem;
            cursor: pointer;
            transition: background .2s ease, transform .2s ease;
        }
        .prestation-banner-arrow:hover {
            background: rgba(212,99,10,0.9);
            transform: scale(1.06);
        }
        .prestation-card {
            position: relative;
            border: 1px solid rgba(0,0,0,0.08);
            background: linear-gradient(180deg, #ffffff, #f8f4ec);
            box-shadow: 0 12px 30px rgba(0,0,0,0.06);
            transition: transform .25s cubic-bezier(0.2,0.8,0.2,1), box-shadow .25s ease, border-color .25s ease;
        }
        .prestation-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: rgba(194,94,10,0.3);
        }
        .prestation-icon-wrap {
            width: 3.25rem; height: 3.25rem;
            border-radius: 0.9rem;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, rgba(242,121,15,0.14), rgba(242,121,15,0.04));
            border: 1px solid rgba(242,121,15,0.25);
            color: #d4630a;
        }
        .df-catalog-frame {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 1rem;
            background: #f8f4ec;
            min-height: 480px;
        }
    </style>
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
@include('partials.public-top-nav')

{{-- ══════════════════════════════════════════════════════════
     BANNIÈRE
══════════════════════════════════════════════════════════ --}}
@if($banners->isNotEmpty())
<section class="prestation-cover" id="prestation-banner-carousel">
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <div class="prestation-cover-frame">
            @foreach($banners as $i => $banner)
            <div class="prestation-banner-slide {{ $i === 0 ? '' : 'hidden' }}">
                @if($banner->image_url)
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?: 'Nos Prestations' }}">
                @endif
                <div class="prestation-banner-scrim"></div>

                <div class="prestation-banner-copy">
                    <p class="text-gold-300 text-xs font-semibold tracking-[0.28em] uppercase mb-3">Trésors Ivoire</p>
                    <h1 class="font-serif text-2xl sm:text-4xl font-bold text-white leading-tight max-w-xl">
                        {{ $banner->title ?: 'Nos Prestations' }}
                    </h1>
                    <div class="prestation-cover-eyebrow-rule"></div>
                    @if($banner->content)
                        <p class="text-gray-200 text-sm sm:text-base max-w-lg leading-relaxed">
                            {{ $banner->content }}
                        </p>
                    @endif
                    <div class="flex flex-wrap items-center gap-3 mt-6">
                        @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" class="prestation-cta-primary">
                            {{ $banner->link_label ?: 'Demander un devis' }}
                        </a>
                        @endif
                        <a href="#prestation-items" class="prestation-cta-secondary">Nos services</a>
                    </div>
                </div>
            </div>
            @endforeach

            @if($banners->count() > 1)
                <div class="prestation-banner-dots">
                    @foreach($banners as $i => $banner)
                    <button type="button" class="prestation-banner-dot {{ $i === 0 ? 'is-active' : '' }}" aria-label="Bannière {{ $i + 1 }}"></button>
                    @endforeach
                </div>
                <div class="prestation-banner-arrows">
                    <button type="button" class="prestation-banner-arrow" id="prestation-banner-prev" aria-label="Bannière précédente">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="prestation-banner-arrow" id="prestation-banner-next" aria-label="Bannière suivante">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</section>

@if($banners->count() > 1)
<script>
    (function () {
        const section = document.getElementById('prestation-banner-carousel');
        const slides = section.querySelectorAll('.prestation-banner-slide');
        const dots = section.querySelectorAll('.prestation-banner-dot');
        const prev = document.getElementById('prestation-banner-prev');
        const next = document.getElementById('prestation-banner-next');
        let current = 0;
        let timer = null;

        function goTo(index) {
            slides[current].classList.add('hidden');
            dots[current].classList.remove('is-active');
            current = (index + slides.length) % slides.length;
            slides[current].classList.remove('hidden');
            dots[current].classList.add('is-active');
        }

        function start() {
            timer = setInterval(() => goTo(current + 1), 6000);
        }
        function stop() {
            clearInterval(timer);
        }
        function restart() {
            stop();
            start();
        }

        dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); restart(); }));
        prev.addEventListener('click', () => { goTo(current - 1); restart(); });
        next.addEventListener('click', () => { goTo(current + 1); restart(); });
        section.addEventListener('mouseenter', stop);
        section.addEventListener('mouseleave', start);
        start();
    })();
</script>
@endif
@endif

{{-- ══════════════════════════════════════════════════════════
     VIDÉO DE PRÉSENTATION
══════════════════════════════════════════════════════════ --}}
@if($settings->hasVideo())
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center reveal">
        <p class="text-gold-500 text-xs font-semibold tracking-[0.28em] uppercase mb-3">Découvrir</p>
        <h2 class="font-serif text-2xl sm:text-3xl font-bold text-gray-900 mb-8">Notre vidéo de présentation</h2>

        <div class="rounded-2xl overflow-hidden shadow-2xl shadow-black/10 border border-black/5 aspect-video bg-black">
            @if($settings->video_source === 'upload')
                <video src="{{ $settings->video_url }}"
                       @if($settings->video_poster_url) poster="{{ $settings->video_poster_url }}" @endif
                       controls class="w-full h-full object-cover"></video>
            @else
                <iframe src="{{ $settings->video_embed_src }}" class="w-full h-full" title="Vidéo de présentation — Nos Prestations"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     CATALOGUE INTERACTIF (DearFlip)
══════════════════════════════════════════════════════════ --}}
@if($settings->catalog_enabled && $settings->catalog_file_url)
<section class="py-16 sm:py-20 bg-[#f8f4ec]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 reveal">
        <div class="text-center mb-10">
            <p class="text-gold-500 text-xs font-semibold tracking-[0.28em] uppercase mb-3">Catalogue</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-gray-900">
                {{ $settings->catalog_title ?: 'Notre catalogue de prestations' }}
            </h2>
        </div>

        {{--
            Feuilletage interactif ("page qui tourne") rendu avec PDF.js (Mozilla) +
            page-flip (StPageFlip) — deux librairies open-source chargées depuis leurs
            CDN officiels. Dégradation progressive : le lien de téléchargement reste
            affiché tant que le feuilletage n'a pas fini de s'initialiser, et redevient
            visible si le rendu échoue (PDF illisible, script bloqué, etc.).
        --}}
        <div class="text-center">
            <div id="pdf-flipbook-wrap" class="hidden justify-center">
                <div id="pdf-flipbook" data-pdf-url="{{ $settings->catalog_file_url }}"></div>
            </div>
            <div id="pdf-flipbook-loading" class="df-catalog-frame flex flex-col items-center justify-center gap-3 py-16">
                <i class="fas fa-circle-notch fa-spin text-gold-500 text-3xl"></i>
                <p class="text-gray-500 text-sm">Chargement du feuilletage…</p>
            </div>
            <div id="pdf-flipbook-fallback" class="df-catalog-frame hidden flex-col items-center justify-center gap-4 py-16 text-center px-6">
                <i class="fas fa-book-open text-gold-500 text-4xl"></i>
                <p class="text-gray-600 max-w-md">
                    Le feuilletage interactif n'a pas pu se charger. Le catalogue reste disponible en téléchargement.
                </p>
                <a href="{{ $settings->catalog_file_url }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-600 text-dark-900 font-semibold px-5 py-2.5 rounded-full text-sm transition">
                    <i class="fas fa-file-pdf"></i> Ouvrir le catalogue (PDF)
                </a>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
        <script type="module">
            (async () => {
                const container = document.getElementById('pdf-flipbook');
                const wrap = document.getElementById('pdf-flipbook-wrap');
                const loading = document.getElementById('pdf-flipbook-loading');
                const fallback = document.getElementById('pdf-flipbook-fallback');
                if (!container) return;

                const showFallback = () => {
                    loading?.remove();
                    fallback?.classList.remove('hidden');
                    fallback?.classList.add('flex');
                };

                try {
                    if (typeof St === 'undefined') throw new Error('page-flip non chargé');

                    const pdfjsLib = await import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/6.2.108/pdf.min.mjs');
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/6.2.108/pdf.worker.min.mjs';

                    const pdf = await pdfjsLib.getDocument({ url: container.dataset.pdfUrl }).promise;
                    const renderScale = 1.6;
                    const images = [];
                    let baseWidth = 500, baseHeight = 700;

                    for (let i = 1; i <= pdf.numPages; i++) {
                        const page = await pdf.getPage(i);
                        if (i === 1) {
                            const baseViewport = page.getViewport({ scale: 1 });
                            baseWidth = baseViewport.width;
                            baseHeight = baseViewport.height;
                        }
                        const viewport = page.getViewport({ scale: renderScale });
                        const canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                        images.push(canvas.toDataURL('image/jpeg', 0.9));
                    }

                    if (!images.length) throw new Error('PDF vide');

                    loading?.remove();
                    wrap.classList.remove('hidden');
                    wrap.classList.add('flex');

                    const pageFlip = new St.PageFlip(container, {
                        width: baseWidth,
                        height: baseHeight,
                        size: 'stretch',
                        minWidth: 280,
                        maxWidth: 1000,
                        minHeight: 360,
                        maxHeight: 1400,
                        showCover: true,
                        mobileScrollSupport: true,
                        maxShadowOpacity: 0.5,
                    });
                    pageFlip.loadFromImages(images);
                } catch (e) {
                    console.error('Feuilletage catalogue : échec du chargement', e);
                    showFallback();
                }
            })();
        </script>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     NOS DIFFÉRENTES PRESTATIONS
══════════════════════════════════════════════════════════ --}}
<section id="prestation-items" class="py-16 sm:py-20 bg-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12 reveal">
            <p class="text-gold-500 text-xs font-semibold tracking-[0.28em] uppercase mb-3">Ce que nous proposons</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-gray-900">Nos différentes prestations</h2>
        </div>

        @if($items->isEmpty())
            <p class="text-center text-gray-500">Aucune prestation publiée pour le moment.</p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($items as $item)
            <div class="prestation-card rounded-2xl p-6 reveal">
                @if($item->image_url)
                    <div class="w-full h-36 rounded-xl overflow-hidden mb-5 border border-black/5">
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="prestation-icon-wrap mb-5">
                        <i class="{{ $item->icon ?: 'fas fa-concierge-bell' }} text-lg"></i>
                    </div>
                @endif

                <h3 class="font-serif text-lg font-bold text-gray-900 mb-2">{{ $item->title }}</h3>
                @if($item->description)
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ $item->description }}</p>
                @endif

                @if($item->link_url)
                    <a href="{{ $item->link_url }}"
                       class="inline-flex items-center gap-1.5 text-gold-600 hover:text-gold-500 font-semibold text-sm transition">
                        {{ $item->link_label ?: 'En savoir plus' }}
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

@include('partials.homepage-footer')

<script>
    const revealEls = document.querySelectorAll('.reveal');
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
                revealObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.06 });
    revealEls.forEach(el => revealObs.observe(el));
</script>
@include('partials.image-protection')
</body>
</html>
