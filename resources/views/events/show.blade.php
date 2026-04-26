<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title_fr }} — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white">
    @include('partials.public-top-nav')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <a href="{{ route('events.index') }}" class="text-amber-400 hover:text-amber-300 transition text-sm">← Retour à l'agenda</a>

        <div class="mt-4 bg-[#141410] border border-white/8 rounded-xl p-6">
            <p class="text-amber-400 text-xs uppercase">{{ $event->category->name_fr ?? 'Événement' }}</p>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold mt-2">{{ $event->title_fr }}</h1>
            @auth
                @if(auth()->user()->role === 'visitor')
                    <div class="mt-3">
                        @if(!($isFavorited ?? false))
                            <form method="POST" action="{{ route('visitor.favorites.store') }}">
                                @csrf
                                <input type="hidden" name="type" value="event">
                                <input type="hidden" name="id" value="{{ $event->id }}">
                                <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs hover:bg-amber-500/30 transition">
                                    <i class="fas fa-heart"></i> Ajouter à ma wishlist
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs">
                                <i class="fas fa-heart-circle-check"></i> Déjà dans vos favoris
                            </span>
                        @endif
                    </div>
                @endif
            @endauth
            <p class="text-gray-500 mt-2">{{ $event->starts_at?->format('d/m/Y H:i') }} @if($event->ends_at) - {{ $event->ends_at->format('d/m/Y H:i') }} @endif</p>
            <p class="text-gray-500">{{ $event->location_name ?: 'Lieu non précisé' }} · {{ $event->city ?: 'Côte d\'Ivoire' }}</p>
            <div class="mt-4 leading-relaxed text-gray-300 prose-content max-w-none">{!! \App\Support\HtmlSanitizer::articleBody($event->description_fr) !!}</div>
            @if($event->ticket_url)
                <a href="{{ $event->ticket_url }}" target="_blank" class="inline-flex mt-5 bg-amber-500 hover:bg-amber-600 text-black font-semibold px-4 py-2 rounded-lg text-sm transition">
                    Réserver / Billetterie
                </a>
            @endif
        </div>

        @if($related->isNotEmpty())
            <div class="mt-8">
                <h2 class="font-serif text-xl font-semibold mb-3">Événements liés</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($related as $r)
                        <a href="{{ route('events.show', $r->slug) }}" class="bg-[#141410] border border-white/5 rounded-xl p-4 hover:border-amber-500/40 transition">
                            <p class="text-amber-400 text-xs uppercase">{{ $r->category->name_fr ?? 'Événement' }}</p>
                            <p class="font-semibold mt-1">{{ $r->title_fr }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <footer class="border-t border-white/5 bg-[#0d0d0b] py-6 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-gray-600">
            <span>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</span>
            <a href="{{ route('events.index') }}" class="hover:text-amber-400 transition">← Retour aux événements</a>
        </div>
    </footer>
</body>
</html>
