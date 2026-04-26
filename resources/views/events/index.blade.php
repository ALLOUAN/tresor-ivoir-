<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements — {{ $siteBrand['site_name'] }}</title>
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-amber-400 text-xs tracking-[.25em] uppercase mb-2">Agenda culturel</p>
                <h1 class="font-serif text-3xl sm:text-4xl font-bold">Agenda des événements</h1>
            </div>
            <a href="{{ route('home') }}" class="text-amber-400 hover:text-amber-300 transition">Retour accueil</a>
        </div>

        <form method="GET" action="{{ route('events.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher..." class="md:col-span-2 bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm">
            <select name="categorie" class="bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected($activeCategory?->id === $cat->id)>{{ $cat->name_fr }}</option>
                @endforeach
            </select>
            <select name="ville" class="bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm">
                <option value="">Toutes villes</option>
                @foreach($cities as $v)
                    <option value="{{ $v }}" @selected($city === $v)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="periode" class="bg-[#1c1c16] border border-white/10 rounded-lg px-3 py-2 text-sm">
                <option value="upcoming" @selected($period === 'upcoming')>À venir</option>
                <option value="past" @selected($period === 'past')>Passés</option>
                <option value="all" @selected($period === 'all')>Tous</option>
            </select>
            <div class="md:col-span-5 flex gap-2">
                <button class="bg-amber-500 hover:bg-amber-600 text-black font-semibold px-4 py-2 rounded-lg text-sm">Filtrer</button>
                <a href="{{ route('events.index') }}" class="bg-[#1c1c16] hover:bg-[#252520] border border-white/10 px-4 py-2 rounded-lg text-sm transition">Réinitialiser</a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($events as $event)
                <a href="{{ route('events.show', $event->slug) }}" class="bg-[#141410] border border-white/5 rounded-xl p-4 hover:border-amber-500/40 transition">
                    <p class="text-amber-400 text-xs uppercase">{{ $event->category->name_fr ?? 'Événement' }}</p>
                    <h2 class="text-lg font-semibold mt-1">{{ $event->title_fr }}</h2>
                    <p class="text-gray-500 text-sm mt-2">{{ $event->starts_at?->format('d/m/Y H:i') }} · {{ $event->city ?: 'Côte d\'Ivoire' }}</p>
                </a>
            @empty
                <p class="text-gray-500 col-span-3">Aucun événement trouvé.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $events->links() }}</div>
    </div>
</body>
</html>
