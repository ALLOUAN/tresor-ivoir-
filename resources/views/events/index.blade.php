<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Agenda des événements</h1>
            <a href="/" class="text-amber-400">Retour accueil</a>
        </div>

        <form method="GET" action="{{ route('events.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher..." class="md:col-span-2 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm">
            <select name="categorie" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected($activeCategory?->id === $cat->id)>{{ $cat->name_fr }}</option>
                @endforeach
            </select>
            <select name="ville" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <option value="">Toutes villes</option>
                @foreach($cities as $v)
                    <option value="{{ $v }}" @selected($city === $v)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="periode" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <option value="upcoming" @selected($period === 'upcoming')>À venir</option>
                <option value="past" @selected($period === 'past')>Passés</option>
                <option value="all" @selected($period === 'all')>Tous</option>
            </select>
            <div class="md:col-span-5 flex gap-2">
                <button class="bg-amber-500 hover:bg-amber-600 text-black font-semibold px-4 py-2 rounded-lg text-sm">Filtrer</button>
                <a href="{{ route('events.index') }}" class="bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg text-sm">Réinitialiser</a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($events as $event)
                <a href="{{ route('events.show', $event->slug) }}" class="bg-slate-900 border border-slate-800 rounded-xl p-4 hover:border-amber-500/40 transition">
                    <p class="text-amber-400 text-xs uppercase">{{ $event->category->name_fr ?? 'Événement' }}</p>
                    <h2 class="text-lg font-semibold mt-1">{{ $event->title_fr }}</h2>
                    <p class="text-slate-400 text-sm mt-2">{{ $event->starts_at?->format('d/m/Y H:i') }} · {{ $event->city ?: 'Côte d\'Ivoire' }}</p>
                </a>
            @empty
                <p class="text-slate-500 col-span-3">Aucun événement trouvé.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $events->links() }}</div>
    </div>
</body>
</html>
