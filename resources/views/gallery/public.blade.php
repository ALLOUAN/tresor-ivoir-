<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Galerie Trésors d'Ivoire — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Découvrez la galerie photo {{ $siteBrand['site_name'] }} : paysages, culture et art de vivre en Côte d'Ivoire.">
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
                        gold: { 300:'#fcd68a', 400:'#f5b942', 500:'#e8a020', 600:'#c4811a' },
                        dark: { 600:'#252520', 700:'#1c1c16', 800:'#141410', 900:'#0d0d0b' },
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
        ::-webkit-scrollbar-track { background: #0d0d0b; }
        ::-webkit-scrollbar-thumb { background: #e8a020; border-radius: 3px; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .gold-line::after {
            content: '';
            display: block;
            width: 52px;
            height: 2px;
            background: linear-gradient(90deg, #e8a020, #f5b942);
            margin-top: 10px;
        }
        .gallery-card:hover .gallery-img { transform: scale(1.04); }
        .gallery-img { transition: transform .5s ease; }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">

@include('partials.public-top-nav')

<section class="relative pt-24 sm:pt-32 pb-14 sm:pb-16 overflow-hidden" style="background: linear-gradient(135deg, #0d0d0b 0%, #1a1506 42%, #151208 100%);">
    <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: repeating-linear-gradient(45deg,#e8a020 0,#e8a020 1px,transparent 0,transparent 50%); background-size: 20px 20px;"></div>
    <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full blur-3xl pointer-events-none" style="background:rgba(232,160,32,0.07)"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <nav class="text-xs text-gray-500 mb-4 font-plus">
            <a href="{{ route('home') }}" class="hover:text-gold-400 transition">Accueil</a>
            <span class="mx-2 opacity-50">/</span>
            <span class="text-gold-500/90">Galerie</span>
        </nav>
        <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3 reveal">Photographie</p>
        <h1 class="font-serif text-4xl sm:text-5xl font-bold mb-4 leading-tight reveal">
            Galerie Trésors d'Ivoire
        </h1>
        <p class="text-gray-400 font-elegant text-lg sm:text-xl font-light leading-relaxed max-w-2xl reveal">
            Un regard sur la Côte d'Ivoire : instants choisis par la rédaction, disponibles en pleine page.
        </p>
    </div>
</section>

<section class="py-12 sm:py-16 bg-dark-800 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        @if(($galleryImages ?? collect())->isEmpty())
            <div class="text-center py-20 rounded-2xl border border-dashed border-white/10 bg-dark-900/40 reveal">
                <i class="fas fa-images text-5xl text-gold-600/25 mb-5"></i>
                <p class="text-gray-300 font-medium">La galerie sera bientôt enrichie.</p>
                <p class="text-gray-600 text-sm mt-2 max-w-md mx-auto">Publiez des images depuis l’administration (section « Accueil - Galerie »), actives et datées.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mt-8 px-5 py-2.5 rounded-xl border border-gold-500/35 text-gold-300 hover:bg-gold-500/10 text-sm font-medium transition">
                    <i class="fas fa-arrow-left text-xs"></i> Retour à l’accueil
                </a>
            </div>
        @else
            <div class="mb-10 sm:mb-12 reveal">
                <p class="text-gold-400 text-xs tracking-[.2em] uppercase font-elegant mb-2">{{ $galleryImages->count() }} visuel{{ $galleryImages->count() > 1 ? 's' : '' }}</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold gold-line">Sélection</h2>
            </div>
            <div class="columns-1 sm:columns-2 lg:columns-3 gap-4 sm:gap-5 space-y-4 sm:space-y-5">
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
                    <article class="gallery-card reveal break-inside-avoid rounded-xl border border-white/10 bg-dark-900/60 overflow-hidden shadow-lg shadow-black/20 hover:border-gold-500/30 transition">
                        <a href="{{ filled($img->uuid) ? route('gallery.public.show', $img->uuid) : url($img->url) }}"
                           @if(!filled($img->uuid)) target="_blank" rel="noopener noreferrer" @endif
                           class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-dark-800">
                            <div class="aspect-[4/3] overflow-hidden bg-black/50">
                                <img src="{{ url($img->url) }}" alt="{{ $alt }}" loading="lazy" decoding="async"
                                     class="gallery-img w-full h-full object-cover">
                            </div>
                            <div class="p-4 text-left border-t border-white/5">
                                @if($title !== '')
                                    <p class="text-gold-200 font-serif text-sm font-semibold leading-snug">{{ $title }}</p>
                                @endif
                                @if($caption !== '')
                                    <p class="text-gray-500 text-xs mt-1.5 leading-relaxed line-clamp-3">{{ $caption }}</p>
                                @endif
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    @if($credit !== '')
                                        <span class="text-[10px] text-gray-600"><i class="fas fa-camera text-gold-600/50 mr-1"></i>{{ $credit }}</span>
                                    @endif
                                    @if($price)
                                        <span class="text-[10px] font-medium text-emerald-300/90 bg-emerald-950/50 border border-emerald-500/20 rounded px-1.5 py-0.5">{{ $price }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<footer class="bg-dark-800 border-t border-white/5 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-600">
        <p>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés</p>
        <div class="flex items-center gap-5">
            <a href="{{ route('home') }}" class="hover:text-gold-400 transition">Accueil</a>
            <a href="{{ route('articles.index') }}" class="hover:text-gold-400 transition">Articles</a>
            <a href="{{ route('events.index') }}" class="hover:text-gold-400 transition">Événements</a>
        </div>
    </div>
</footer>

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

</body>
</html>
