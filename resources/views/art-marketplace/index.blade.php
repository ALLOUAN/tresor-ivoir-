<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art &amp; Créations — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .hero-art {
            background:
                radial-gradient(120% 100% at 15% 0%, rgba(242,121,15,0.16), transparent 55%),
                linear-gradient(150deg, #241e14 0%, #14130f 65%);
        }
        .art-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .art-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(194,94,10,0.14); border-color: rgba(242,121,15,0.3); }
        .art-cover { position: relative; overflow: hidden; background: #14130f; aspect-ratio: 4/3; }
        .art-cover img { transition: transform .5s ease; }
        .art-card:hover .art-cover img { transform: scale(1.06); }
        .btn-primary {
            background: #f2790f;
            box-shadow: 0 10px 24px rgba(242,121,15,0.3);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
            color: #1b1408;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="hero-art relative py-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="inline-block rounded-2xl bg-green-950/70 backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
                <p class="text-orange-300 text-sm font-medium uppercase tracking-widest mb-3">Art &amp; Créations</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">Découvrez et achetez des œuvres uniques</h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">Peintures, sculptures, photographies et artisanat d'exception, directement auprès des artistes ivoiriens.</p>
            </div>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 relative z-10 pb-16">

        <form method="GET" action="{{ route('art.index') }}" class="filter-card rounded-2xl p-5 mb-8" style="border:1px solid rgba(0,0,0,.08); background:#fff; box-shadow:0 14px 34px rgba(20,18,12,.08);">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Recherche</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Titre de l'œuvre..." class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Catégorie</label>
                    <select name="categorie" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">Toutes catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>{{ $cat->name_fr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Budget</label>
                    <select name="budget" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">Tous budgets</option>
                        <option value="low" @selected($budget==='low')>Moins de 50 000 XOF</option>
                        <option value="mid" @selected($budget==='mid')>50 000 – 200 000 XOF</option>
                        <option value="high" @selected($budget==='high')>200 000 – 500 000 XOF</option>
                        <option value="luxury" @selected($budget==='luxury')>Plus de 500 000 XOF</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Trier par</label>
                    <select name="tri" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="pertinence" @selected($sort==='pertinence')>Nouveautés</option>
                        <option value="prix_asc" @selected($sort==='prix_asc')>Prix croissant</option>
                        <option value="prix_desc" @selected($sort==='prix_desc')>Prix décroissant</option>
                        <option value="populaire" @selected($sort==='populaire')>Popularité</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
                <button type="submit" class="btn-primary btn-shine px-5 py-2.5 rounded-lg font-bold text-sm">
                    <i class="fas fa-magnifying-glass mr-1.5"></i> Filtrer
                </button>
                <a href="{{ route('art.index') }}" class="px-4 py-2.5 rounded-lg border border-black/10 text-sm font-semibold hover:bg-black/5 transition">Réinitialiser</a>
            </div>
        </form>

        <p class="text-[#8a7f6b] text-sm mb-4">{{ $artworks->total() }} œuvre(s) trouvée(s)</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($artworks as $artwork)
                <a href="{{ route('art.show', $artwork->slug) }}" class="art-card rounded-2xl overflow-hidden block">
                    <div class="art-cover">
                        @if(!empty($artwork->images[0]))
                            <img src="{{ $artwork->images[0] }}" class="w-full h-full object-cover" alt="{{ $artwork->title }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-image text-3xl"></i></div>
                        @endif
                        @if($artwork->status === \App\Models\Artwork::STATUS_SOLD)
                            <span class="absolute top-3 right-3 bg-slate-900/80 text-white text-[11px] font-semibold px-2.5 py-1 rounded-full">Vendue</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-[11px] uppercase tracking-wide text-orange-600 font-semibold mb-1">{{ $artwork->category?->name_fr }}</p>
                        <h3 class="font-serif text-lg font-bold truncate">{{ $artwork->title }}</h3>
                        @if($artwork->provider)
                        <a href="{{ route('providers.show', $artwork->provider->slug) }}" onclick="event.stopPropagation()"
                           class="text-[#8a7f6b] text-sm truncate hover:text-orange-600 hover:underline transition block">{{ $artwork->provider->name }}</a>
                        @endif
                        <p class="text-[#1c1915] font-bold mt-2">{{ number_format((int) $artwork->price_xof, 0, ',', ' ') }} XOF</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16 text-[#8a7f6b]">
                    Aucune œuvre disponible pour le moment.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $artworks->links() }}
        </div>
    </div>

    @include('partials.homepage-footer')
</body>
</html>
