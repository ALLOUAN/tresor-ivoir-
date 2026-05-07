<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Découvertes — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Explorez la Côte d'Ivoire rubrique par rubrique : patrimoine, art de vivre, gastronomie, nature et destinations d'exception.">
    @include('partials.theme-init')
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
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .cat-card:hover .cat-img { transform: scale(1.06); }
        .cat-img { transition: transform .6s ease; }
        .article-card:hover .art-img { transform: scale(1.05); }
        .art-img { transition: transform .5s ease; }
        .gold-line::after {
            content: '';
            display: block;
            width: 52px;
            height: 2px;
            background: linear-gradient(90deg, #e8a020, #f5b942);
            margin-top: 10px;
        }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">

@include('partials.public-top-nav')

{{-- ── HERO ──────────────────────────────────────────────────────────────── --}}
<section class="relative pt-24 sm:pt-32 pb-16 sm:pb-20 overflow-hidden" style="background: linear-gradient(135deg, #0d0d0b 0%, #1a1506 40%, #151208 100%);">
    <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(45deg,#e8a020 0,#e8a020 1px,transparent 0,transparent 50%); background-size: 20px 20px;"></div>
    <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full blur-3xl pointer-events-none" style="background:rgba(232,160,32,0.06)"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="max-w-2xl">
            <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">Explorer par thème</p>
            <h1 class="font-serif text-4xl sm:text-5xl font-bold mb-5 leading-tight">
                Découvertes
            </h1>
            <p class="text-gray-400 font-elegant text-xl font-light leading-relaxed mb-8">
                Plongez dans la richesse et la diversité de la Côte d'Ivoire à travers nos rubriques thématiques — patrimoine, culture, gastronomie, nature et art de vivre.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('articles.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gold-500/30 text-gold-300 hover:text-gold-200 hover:border-gold-400/60 hover:bg-gold-500/5 transition text-sm font-medium">
                    <i class="fas fa-newspaper text-xs"></i>Tous les articles
                </a>
                <a href="{{ route('events.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 text-gray-400 hover:text-white hover:border-white/25 hover:bg-white/5 transition text-sm font-medium">
                    <i class="fas fa-calendar-days text-xs"></i>Agenda culturel
                </a>
                <a href="{{ route('providers.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 text-gray-400 hover:text-white hover:border-white/25 hover:bg-white/5 transition text-sm font-medium">
                    <i class="fas fa-map-pin text-xs"></i>Annuaire
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ── RUBRIQUES (CATÉGORIES) ────────────────────────────────────────────── --}}
@if(($discoverCategories ?? collect())->isNotEmpty())
<section class="py-16 sm:py-20 bg-dark-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="mb-10 sm:mb-12 reveal">
            <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-2">Nos rubriques</p>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold gold-line">Explorer par catégorie</h2>
        </div>

        @php
            $defaultIcons  = ['fa-landmark','fa-palette','fa-leaf','fa-utensils','fa-map-location-dot','fa-gem','fa-camera','fa-music','fa-heart','fa-star'];
            $defaultGrads  = [
                ['from-amber-900/50 to-amber-800/20','border-amber-700/30'],
                ['from-rose-900/40 to-rose-800/15','border-rose-700/25'],
                ['from-green-900/50 to-green-800/20','border-green-700/30'],
                ['from-orange-900/40 to-orange-800/15','border-orange-700/25'],
                ['from-blue-900/40 to-blue-800/15','border-blue-700/25'],
                ['from-violet-900/40 to-violet-800/15','border-violet-700/25'],
                ['from-teal-900/40 to-teal-800/15','border-teal-700/25'],
                ['from-pink-900/40 to-pink-800/15','border-pink-700/25'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
            @foreach($discoverCategories as $i => $cat)
            @php
                $icon  = $cat->icon ?: $defaultIcons[$i % count($defaultIcons)];
                $grad  = $defaultGrads[$i % count($defaultGrads)];
            @endphp
            <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
               class="cat-card group relative overflow-hidden rounded-2xl border {{ $grad[1] }} bg-gradient-to-br {{ $grad[0] }} p-6 hover:border-gold-500/40 transition-all duration-300 reveal">

                {{-- Icône --}}
                <div class="w-14 h-14 rounded-xl bg-white/5 group-hover:bg-gold-500/15 flex items-center justify-center mb-5 transition-all duration-300">
                    <i class="fas {{ $icon }} text-gold-400 text-2xl group-hover:scale-110 transition-transform duration-300"></i>
                </div>

                {{-- Contenu --}}
                <h3 class="font-serif text-lg font-bold mb-1 group-hover:text-gold-300 transition">{{ $cat->name_fr }}</h3>
                @if($cat->description_fr)
                <p class="text-gray-500 text-sm leading-relaxed mb-4 line-clamp-2">{{ $cat->description_fr }}</p>
                @else
                <p class="text-gray-600 text-sm mb-4">Explorez cette rubrique</p>
                @endif

                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">
                        {{ $cat->articles_count > 0
                            ? $cat->articles_count.' article'.($cat->articles_count > 1 ? 's' : '')
                            : 'Bientôt disponible' }}
                    </span>
                    <span class="inline-flex items-center gap-1 text-gold-400/70 group-hover:text-gold-400 text-xs font-medium transition">
                        Explorer <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform duration-300"></i>
                    </span>
                </div>

                {{-- Déco fond --}}
                <div class="absolute -bottom-4 -right-4 w-24 h-24 rounded-full opacity-10 group-hover:opacity-20 transition"
                     style="background: radial-gradient(circle, #e8a020, transparent 70%)"></div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── DERNIERS ARTICLES ─────────────────────────────────────────────────── --}}
@if(($discoverArticles ?? collect())->isNotEmpty())
<section class="py-16 sm:py-20 bg-dark-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="flex items-end justify-between mb-10 sm:mb-12 reveal">
            <div>
                <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-2">Sélection éditoriale</p>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold gold-line">Dernières publications</h2>
            </div>
            <a href="{{ route('articles.index') }}"
               class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gold-500/25 bg-dark-800/70 text-sm text-gold-300 hover:text-gold-200 hover:border-gold-400/50 hover:bg-dark-700/80 transition-all duration-300 font-semibold group hover:-translate-y-0.5">
                <span>Voir tout</span>
                <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($discoverArticles as $article)
            <a href="{{ route('articles.show', $article->slug_fr) }}"
               class="article-card group bg-dark-700/40 hover:bg-dark-700 border border-white/5 hover:border-gold-500/20 rounded-xl overflow-hidden transition-all duration-300 reveal">
                <div class="h-32 bg-dark-600 relative overflow-hidden">
                    @if($article->cover_url)
                        <img src="{{ $article->cover_url }}" alt="{{ $article->title_fr }}"
                             class="art-img absolute inset-0 w-full h-full object-cover">
                    @else
                        <div class="art-img absolute inset-0 bg-gradient-to-br from-dark-700 to-dark-500 flex items-center justify-center">
                            <i class="fas fa-image text-dark-400 text-lg"></i>
                        </div>
                    @endif
                </div>
                <div class="p-3">
                    <span class="text-gold-400/70 text-[10px] uppercase tracking-wider font-elegant">{{ $article->category?->name_fr }}</span>
                    <h3 class="font-serif text-xs font-semibold mt-1 line-clamp-2 group-hover:text-gold-300 transition leading-snug">{{ $article->title_fr }}</h3>
                </div>
            </a>
            @endforeach
        </div>

        <div class="text-center mt-10 sm:hidden reveal">
            <a href="{{ route('articles.index') }}"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-gold-500/25 text-gold-300 hover:text-gold-200 transition font-semibold text-sm">
                Voir tous les articles <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

    </div>
</section>
@else
<section class="py-20 bg-dark-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
        <i class="fas fa-newspaper text-dark-600 text-5xl mb-4"></i>
        <p class="text-gray-500 font-elegant text-lg mb-4">Les premiers articles arrivent bientôt.</p>
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm transition">
            <i class="fas fa-plus text-xs"></i>Publier un article
        </a>
    </div>
</section>
@endif

{{-- ── FOOTER SIMPLIFIÉ ──────────────────────────────────────────────────── --}}
<footer class="bg-dark-800 border-t border-white/5 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-600">
        <p>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés</p>
        <div class="flex items-center gap-5">
            <a href="{{ route('home') }}" class="hover:text-gold-400 transition">Accueil</a>
            <a href="{{ route('articles.index') }}" class="hover:text-gold-400 transition">Articles</a>
            <a href="{{ route('events.index') }}" class="hover:text-gold-400 transition">Événements</a>
            <a href="{{ route('providers.index') }}" class="hover:text-gold-400 transition">Annuaire</a>
        </div>
    </div>
</footer>

<script>
    // Reveal on scroll
    const reveals = document.querySelectorAll('.reveal');
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 70);
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });
    reveals.forEach(el => obs.observe(el));
</script>

@include('partials.homepage-footer')
</body>
</html>
