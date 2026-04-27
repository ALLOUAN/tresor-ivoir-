<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles — {{ $siteBrand['site_name'] }}</title>
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
                        gold: { 300:'#fcd68a', 400:'#f5b942', 500:'#e8a020', 600:'#c4811a' },
                        dark: { 500:'#2e2e26', 600:'#252520', 700:'#1c1c16', 800:'#141410', 900:'#0d0d0b' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }
        .article-card:hover .card-img { transform: scale(1.05); }
        .card-img { transition: transform .5s ease; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">
@include('partials.public-top-nav')

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div class="bg-[#141410] border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-amber-400 text-xs tracking-[.25em] uppercase mb-2">Magazine</p>
                <h1 class="font-serif text-3xl sm:text-4xl font-bold">
                    @if($active_category)
                        {{ $active_category->name_fr }}
                    @elseif($search)
                        Résultats pour "{{ $search }}"
                    @else
                        Tous les articles
                    @endif
                </h1>
                <p class="text-gray-500 text-sm mt-2">{{ $articles->total() }} article{{ $articles->total() > 1 ? 's' : '' }}</p>
            </div>
            {{-- Search --}}
            <form method="GET" action="{{ route('articles.index') }}" class="flex gap-2 w-full sm:w-auto">
                <input name="q" value="{{ $search }}" placeholder="Rechercher…"
                    class="flex-1 sm:w-64 bg-[#1c1c16] border border-white/10 focus:border-amber-400/40 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition">
                <button class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-black rounded-xl transition">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </form>
        </div>

        {{-- Category tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto mt-6 pb-1">
            <a href="{{ route('articles.index') }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium transition {{ !$active_category && !$search ? 'bg-amber-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/8 hover:text-white' }}">
                Tous
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium transition {{ $active_category?->id === $cat->id ? 'bg-amber-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/8 hover:text-white' }}">
                {{ $cat->name_fr }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

    {{-- ── Featured (if no filter) ──────────────────────────────── --}}
    @if(!$active_category && !$search && $featured->isNotEmpty())
    <div class="mb-12">
        <h2 class="font-serif text-xl font-bold mb-6 flex items-center gap-2">
            <i class="fas fa-star text-amber-400 text-base"></i> À la une
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach($featured as $i => $art)
            @php $contributors = $art->display_uploaders; @endphp
            <a href="{{ route('articles.show', $art->slug_fr) }}"
               class="article-card group {{ $i === 0 ? 'lg:col-span-2 lg:row-span-2' : '' }} block relative overflow-hidden rounded-2xl bg-[#1c1c16]">
                <div class="overflow-hidden {{ $i === 0 ? 'h-72 lg:h-full min-h-[280px]' : 'h-44' }}">
                    @if($art->cover_url)
                    <img src="{{ $art->cover_url }}" alt="{{ $art->cover_alt }}"
                         class="card-img w-full h-full object-cover">
                    @else
                    <div class="card-img w-full h-full bg-gradient-to-br from-[#1c1c16] to-[#252520] flex items-center justify-center">
                        <i class="fas fa-image text-[#333] text-3xl"></i>
                    </div>
                    @endif
                </div>
                <div class="absolute inset-0 bg-linear-to-t from-black/95 via-black/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-5">
                    <span class="text-amber-400 text-[11px] uppercase tracking-widest">{{ $art->category->name_fr ?? '—' }}</span>
                    <h3 class="font-serif font-bold mt-1 group-hover:text-amber-300 transition leading-snug {{ $i === 0 ? 'text-lg sm:text-xl' : 'text-sm' }}">
                        {{ $art->title_fr }}
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-2">
                        <span>{{ $art->author->first_name ?? 'Rédaction' }}</span>
                        <span>·</span>
                        <span>{{ $art->published_at?->diffForHumans() }}</span>
                        @if($art->reading_time)
                        <span>· {{ $art->reading_time }} min</span>
                        @endif
                    </div>
                    @if($contributors->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($contributors->take(3) as $contributor)
                        <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-2 py-0.5 text-[10px] font-medium text-amber-200">
                            {{ $contributor->full_name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @if($art->is_featured)
                <span class="absolute top-3 left-3 bg-amber-500 text-black text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wide">
                    <i class="fas fa-star mr-0.5 text-[9px]"></i>À la une
                </span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Article grid ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @forelse($articles as $article)
        @php $contributors = $article->display_uploaders; @endphp
        <a href="{{ route('articles.show', $article->slug_fr) }}"
           class="article-card group bg-dark-700/40 hover:bg-dark-700 border border-white/5 hover:border-gold-500/20 rounded-xl overflow-hidden transition-all duration-300 reveal">
            <div class="h-32 bg-dark-600 relative overflow-hidden">
                @if($article->cover_url)
                    <img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt ?? $article->title_fr }}"
                         class="card-img absolute inset-0 w-full h-full object-cover">
                @else
                    <div class="card-img absolute inset-0 bg-gradient-to-br from-dark-700 to-dark-500 flex items-center justify-center">
                        <i class="fas fa-image text-dark-400 text-lg"></i>
                    </div>
                @endif
                @if($article->is_featured)
                <span class="absolute top-2 left-2 bg-gold-500 text-dark-900 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">À la une</span>
                @endif
                @if($article->is_sponsored)
                <span class="absolute top-2 right-2 bg-white/10 backdrop-blur-sm text-white text-[9px] px-2 py-0.5 rounded-full">Sponsorisé</span>
                @endif
            </div>
            <div class="p-3">
                <span class="text-gold-400/70 text-[10px] uppercase tracking-wider font-elegant">{{ $article->category?->name_fr ?? '—' }}</span>
                <h3 class="font-serif text-xs font-semibold mt-1 line-clamp-2 group-hover:text-gold-300 transition leading-snug">
                    {{ $article->title_fr }}
                </h3>
                @if($contributors->isNotEmpty())
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($contributors->take(2) as $contributor)
                    <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-medium text-amber-200">
                        {{ $contributor->first_name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </a>
        @empty
        <div class="col-span-full py-20 text-center text-gray-600">
            <i class="fas fa-newspaper text-4xl mb-3 block opacity-20"></i>
            <p class="text-lg font-serif">Aucun article trouvé</p>
            <p class="text-sm mt-1">Essayez une autre recherche ou catégorie.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($articles->hasPages())
    <div class="flex justify-center mt-10">
        <div class="flex items-center gap-1">
            @if($articles->onFirstPage())
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] text-gray-600 text-sm cursor-not-allowed">
                <i class="fas fa-chevron-left text-xs"></i>
            </span>
            @else
            <a href="{{ $articles->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] hover:bg-[#252520] text-gray-300 text-sm transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            @endif

            @foreach($articles->getUrlRange(max(1, $articles->currentPage()-2), min($articles->lastPage(), $articles->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-lg text-sm transition {{ $page === $articles->currentPage() ? 'bg-amber-500 text-black font-bold' : 'bg-[#1c1c16] hover:bg-[#252520] text-gray-300' }}">
                {{ $page }}
            </a>
            @endforeach

            @if($articles->hasMorePages())
            <a href="{{ $articles->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] hover:bg-[#252520] text-gray-300 text-sm transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
            @else
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] text-gray-600 text-sm cursor-not-allowed">
                <i class="fas fa-chevron-right text-xs"></i>
            </span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Footer minimal --}}
<footer class="border-t border-white/5 bg-[#141410] py-6 mt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-gray-600">
        <span>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</span>
        <a href="{{ route('login') }}" class="hover:text-amber-400 transition">Espace membres</a>
    </div>
</footer>

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
</body>
</html>
