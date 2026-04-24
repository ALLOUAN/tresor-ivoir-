<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annuaire des prestataires — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">Annuaire des prestataires</h1>
                <p class="text-slate-400 text-sm mt-1">Trouvez les meilleures adresses par ville, catégorie et gamme de prix.</p>
            </div>
            <a href="/" class="text-amber-400 text-sm">Retour accueil</a>
        </div>

        @if($featured->isNotEmpty())
            <div class="mb-6">
                <h2 class="text-white font-semibold mb-3">Prestataires mis en avant</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($featured as $f)
                        <a href="{{ route('providers.show', $f->slug) }}" class="bg-slate-900 border border-amber-500/20 rounded-xl p-4 hover:border-amber-500/50 transition">
                            <p class="text-amber-400 text-xs uppercase">{{ $f->category->name_fr ?? 'Prestataire' }}</p>
                            <p class="text-white font-semibold mt-1">{{ $f->name }}</p>
                            <p class="text-slate-400 text-sm mt-1">{{ $f->city ?: 'Côte d\'Ivoire' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('providers.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Nom, ville ou description..."
                    class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm"
                >
                <select name="categorie" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($activeCategory?->id === $cat->id)>
                            {{ $cat->name_fr }} ({{ $cat->providers_count }})
                        </option>
                    @endforeach
                </select>
                <select name="ville" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="">Toutes villes</option>
                    @foreach($cities as $v)
                        <option value="{{ $v }}" @selected($city === $v)>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="prix" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="">Tous prix</option>
                    @foreach(['budget' => 'Budget', 'mid' => 'Moyen', 'premium' => 'Premium', 'luxury' => 'Luxe'] as $value => $label)
                        <option value="{{ $value }}" @selected($price === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="tri" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="rating" @selected($sort === 'rating')>Tri: mieux notés</option>
                    <option value="name" @selected($sort === 'name')>Tri: nom</option>
                    <option value="newest" @selected($sort === 'newest')>Tri: plus récents</option>
                    <option value="views" @selected($sort === 'views')>Tri: plus vus</option>
                </select>
                <label class="md:col-span-2 text-slate-300 text-sm flex items-center gap-2">
                    <input type="checkbox" name="verifie" value="1" @checked(request()->boolean('verifie'))>
                    Prestataires vérifiés uniquement
                </label>
                <div class="md:col-span-4 flex items-center gap-2">
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm px-4 py-2 rounded-lg">Filtrer</button>
                    <a href="{{ route('providers.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm px-4 py-2 rounded-lg">Réinitialiser</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($providers as $provider)
                <a href="{{ route('providers.show', $provider->slug) }}" class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden hover:border-amber-500/40 transition">
                    <div class="h-40 bg-slate-800">
                        @if($provider->cover_url)
                            <img src="{{ $provider->cover_url }}" alt="{{ $provider->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-600">
                                <i class="fas fa-store text-3xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-white font-semibold">{{ $provider->name }}</p>
                            @if($provider->is_verified)
                                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded-full">Vérifié</span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-xs mt-1">{{ $provider->category->name_fr ?? 'Catégorie' }} · {{ $provider->city ?: 'N/A' }}</p>
                        <p class="text-slate-300 text-sm mt-2">{{ $provider->short_desc_fr ?: \Illuminate\Support\Str::limit($provider->description_fr, 90) }}</p>
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="text-amber-400">{{ number_format((float) ($provider->rating_avg ?? 0), 1) }} ★</span>
                            <span class="text-slate-500 uppercase">{{ $provider->price_range ?: 'n/a' }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 bg-slate-900 border border-slate-800 rounded-xl p-8 text-center text-slate-500">
                    Aucun prestataire trouvé avec ces filtres.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $providers->links() }}
        </div>
    </div>
</body>
</html>
