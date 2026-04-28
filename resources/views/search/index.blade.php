<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche{{ $q ? ' : ' . $q : '' }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="robots" content="noindex">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">
@include('partials.public-top-nav')

<main class="max-w-5xl mx-auto px-4 sm:px-6 py-12 sm:py-16">

    {{-- Search bar --}}
    <div class="mb-10">
        <form action="{{ route('search') }}" method="GET" class="flex gap-2">
            <div class="relative flex-1">
                <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher articles, événements, prestataires…"
                       autofocus
                       class="w-full bg-[#141410] border border-white/10 focus:border-amber-500/50 rounded-xl pl-10 pr-4 py-3.5 text-sm text-white placeholder-gray-500 outline-none transition">
            </div>
            <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-black font-bold px-6 py-3.5 rounded-xl text-sm transition">
                Rechercher
            </button>
        </form>
    </div>

    @if($q && mb_strlen($q) >= 2)

        {{-- Results header --}}
        <p class="text-gray-500 text-sm mb-8">
            @if($total > 0)
                <span class="text-white font-semibold">{{ $total }}</span> résultat{{ $total > 1 ? 's' : '' }} pour
                <span class="text-amber-400 font-semibold">« {{ $q }} »</span>
            @else
                Aucun résultat pour <span class="text-amber-400 font-semibold">« {{ $q }} »</span>.
            @endif
        </p>

        @if($total === 0)
        <div class="text-center py-16">
            <i class="fas fa-search text-4xl text-gray-700 mb-4"></i>
            <p class="text-gray-500">Essayez un autre mot-clé ou vérifiez l'orthographe.</p>
            <div class="flex flex-wrap justify-center gap-2 mt-6 text-xs text-gray-600">
                <span>Suggestions :</span>
                @foreach(['Abidjan', 'Culture', 'Tourisme', 'Festival', 'Restaurant'] as $s)
                <a href="{{ route('search', ['q' => $s]) }}" class="text-amber-500/70 hover:text-amber-400 transition">{{ $s }}</a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Articles --}}
        @if($articles->isNotEmpty())
        <section class="mb-12">
            <div class="flex items-center gap-3 mb-5">
                <i class="fas fa-newspaper text-amber-400 text-sm"></i>
                <h2 class="font-serif text-lg font-bold">Articles <span class="text-gray-600 font-normal text-sm ml-1">{{ $articles->count() }}</span></h2>
                <div class="flex-1 h-px bg-white/5"></div>
            </div>
            <div class="space-y-3">
                @foreach($articles as $article)
                <a href="{{ route('articles.show', $article->slug_fr) }}"
                   class="flex gap-4 p-4 bg-[#141410] border border-white/5 hover:border-amber-500/20 rounded-xl transition group">
                    @if($article->cover_url)
                    <img src="{{ $article->cover_url }}" alt="" class="w-20 h-16 object-cover rounded-lg shrink-0">
                    @else
                    <div class="w-20 h-16 bg-[#1c1c16] rounded-lg shrink-0 flex items-center justify-center">
                        <i class="fas fa-image text-gray-700"></i>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-amber-400/70 text-[10px] uppercase tracking-wider">{{ $article->category->name_fr ?? '—' }}</span>
                            <span class="text-gray-700 text-[10px]">·</span>
                            <span class="text-gray-600 text-[10px]">{{ $article->published_at?->format('d M Y') }}</span>
                        </div>
                        <h3 class="font-serif font-semibold text-sm leading-snug group-hover:text-amber-300 transition line-clamp-1">{{ $article->title_fr }}</h3>
                        @if($article->excerpt_fr)
                        <p class="text-gray-500 text-xs mt-1 line-clamp-1">{{ $article->excerpt_fr }}</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            @if($articles->count() >= 8)
            <a href="{{ route('articles.index', ['q' => $q]) }}"
               class="inline-flex items-center gap-1.5 mt-4 text-amber-400 hover:text-amber-300 text-sm transition">
                Voir tous les articles <i class="fas fa-arrow-right text-xs"></i>
            </a>
            @endif
        </section>
        @endif

        {{-- Événements --}}
        @if($events->isNotEmpty())
        <section class="mb-12">
            <div class="flex items-center gap-3 mb-5">
                <i class="fas fa-calendar-days text-amber-400 text-sm"></i>
                <h2 class="font-serif text-lg font-bold">Événements <span class="text-gray-600 font-normal text-sm ml-1">{{ $events->count() }}</span></h2>
                <div class="flex-1 h-px bg-white/5"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($events as $event)
                <a href="{{ route('events.show', $event->slug) }}"
                   class="flex gap-3 p-4 bg-[#141410] border border-white/5 hover:border-amber-500/20 rounded-xl transition group">
                    <div class="w-12 h-12 bg-amber-500/10 rounded-lg flex flex-col items-center justify-center shrink-0">
                        <span class="text-amber-400 font-bold text-sm leading-none">{{ $event->starts_at?->format('d') }}</span>
                        <span class="text-amber-400/60 text-[10px] uppercase">{{ $event->starts_at?->format('M') }}</span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-sm group-hover:text-amber-300 transition line-clamp-1">{{ $event->title_fr }}</h3>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $event->city ?? '—' }} · {{ $event->category->name_fr ?? '—' }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            @if($events->count() >= 6)
            <a href="{{ route('events.index', ['q' => $q]) }}"
               class="inline-flex items-center gap-1.5 mt-4 text-amber-400 hover:text-amber-300 text-sm transition">
                Voir tous les événements <i class="fas fa-arrow-right text-xs"></i>
            </a>
            @endif
        </section>
        @endif

        {{-- Prestataires --}}
        @if($providers->isNotEmpty())
        <section class="mb-12">
            <div class="flex items-center gap-3 mb-5">
                <i class="fas fa-store text-amber-400 text-sm"></i>
                <h2 class="font-serif text-lg font-bold">Prestataires <span class="text-gray-600 font-normal text-sm ml-1">{{ $providers->count() }}</span></h2>
                <div class="flex-1 h-px bg-white/5"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($providers as $provider)
                <a href="{{ route('providers.show', $provider->slug) }}"
                   class="flex gap-3 p-4 bg-[#141410] border border-white/5 hover:border-amber-500/20 rounded-xl transition group">
                    @if($provider->logo_url)
                    <img src="{{ $provider->logo_url }}" alt="" class="w-12 h-12 object-cover rounded-lg shrink-0">
                    @else
                    <div class="w-12 h-12 bg-[#1c1c16] rounded-lg shrink-0 flex items-center justify-center text-lg font-bold text-amber-400/50">
                        {{ strtoupper(substr($provider->name, 0, 1)) }}
                    </div>
                    @endif
                    <div class="min-w-0">
                        <h3 class="font-semibold text-sm group-hover:text-amber-300 transition line-clamp-1">{{ $provider->name }}</h3>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $provider->city ?? '—' }} · {{ $provider->category->name_fr ?? '—' }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            @if($providers->count() >= 6)
            <a href="{{ route('providers.index', ['q' => $q]) }}"
               class="inline-flex items-center gap-1.5 mt-4 text-amber-400 hover:text-amber-300 text-sm transition">
                Voir tous les prestataires <i class="fas fa-arrow-right text-xs"></i>
            </a>
            @endif
        </section>
        @endif

    @elseif($q && mb_strlen($q) < 2)
        <p class="text-gray-500 text-sm">Saisissez au moins 2 caractères pour lancer la recherche.</p>
    @else
        <div class="text-center py-16">
            <i class="fas fa-magnifying-glass text-4xl text-gray-700 mb-4"></i>
            <p class="text-gray-500">Recherchez parmi les articles, événements et prestataires.</p>
        </div>
    @endif

</main>

<footer class="border-t border-white/5 bg-[#0d0d0b] py-6 mt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center text-xs text-gray-700">
        &copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}
    </div>
</footer>
@include('partials.homepage-footer')
</body>
</html>
