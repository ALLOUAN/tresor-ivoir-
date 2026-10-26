<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Art & Créations') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .hero-art {
            background:
                linear-gradient(transparent, transparent),
                linear-gradient(#0E3B2A, #0E3B2A);
        }
        .art-card {
            border: 1px solid rgba(14,59,42,0.07);
            background: #FFFFFF;
            box-shadow: 0 10px 28px rgba(14,59,42,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .art-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(235,110,0,0.14); border-color: rgba(235,110,0,0.3); }
        .art-cover { position: relative; overflow: hidden; background: #0E3B2A; aspect-ratio: 4/3; }
        .art-cover img { transition: transform .5s ease; }
        .art-card:hover .art-cover img { transform: scale(1.06); }
        .btn-primary {
            background: #EB6E00;
            box-shadow: 0 10px 24px rgba(235,110,0,0.3);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
            color: #0E3B2A;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="hero-art relative py-20 overflow-hidden">
        @include('partials.ivorian-motifs', ['culture' => 'art'])
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="ti-hero-card inline-block rounded-2xl px-6 py-8 sm:px-12 sm:py-10">
                <p class="text-orange-300 text-sm font-medium uppercase tracking-widest mb-3">{{ __('Art & Créations') }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">{{ __('Découvrez et achetez des œuvres uniques') }}</h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">{{ __('Peintures, sculptures, photographies et artisanat d\'exception, directement auprès des artistes ivoiriens.') }}</p>
            </div>
        </div>
        @include('partials.hero-separator', ['class' => 'relative z-10 mt-10'])
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 relative z-10 pb-16">

        <form method="GET" action="{{ route('art.index') }}" class="filter-card rounded-2xl p-5 mb-8" style="border:1px solid rgba(14,59,42,.08); background:#fff; box-shadow:0 14px 34px rgba(14,59,42,.08);">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#557466] mb-1">{{ __('Recherche') }}</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Titre de l\'œuvre...') }}" class="w-full bg-[#F3F8F5] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#557466] mb-1">{{ __('Catégorie') }}</label>
                    <select name="categorie" class="w-full bg-[#F3F8F5] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">{{ __('Toutes catégories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>{{ $cat->localized('name') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#557466] mb-1">{{ __('Budget') }}</label>
                    <select name="budget" class="w-full bg-[#F3F8F5] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">{{ __('Tous budgets') }}</option>
                        <option value="low" @selected($budget==='low')>{{ __('Moins de 50 000 FCFA') }}</option>
                        <option value="mid" @selected($budget==='mid')>{{ __('50 000 – 200 000 FCFA') }}</option>
                        <option value="high" @selected($budget==='high')>{{ __('200 000 – 500 000 FCFA') }}</option>
                        <option value="luxury" @selected($budget==='luxury')>{{ __('Plus de 500 000 FCFA') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#557466] mb-1">{{ __('Trier par') }}</label>
                    <select name="tri" class="w-full bg-[#F3F8F5] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="pertinence" @selected($sort==='pertinence')>{{ __('Nouveautés') }}</option>
                        <option value="prix_asc" @selected($sort==='prix_asc')>{{ __('Prix croissant') }}</option>
                        <option value="prix_desc" @selected($sort==='prix_desc')>{{ __('Prix décroissant') }}</option>
                        <option value="populaire" @selected($sort==='populaire')>{{ __('Popularité') }}</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
                <button type="submit" class="btn-primary btn-shine px-5 py-2.5 rounded-lg font-bold text-sm">
                    <i class="fas fa-magnifying-glass mr-1.5"></i> {{ __('Filtrer') }}
                </button>
                <a href="{{ route('art.index') }}" class="px-4 py-2.5 rounded-lg border border-black/10 text-sm font-semibold hover:bg-black/5 transition">{{ __('Réinitialiser') }}</a>
            </div>
        </form>

        <p class="text-[#557466] text-sm mb-4">{{ $artworks->total() }} {{ $artworks->total() > 1 ? __('œuvres trouvées') : __('œuvre trouvée') }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($artworks as $artwork)
                @include('partials.artwork-card', ['artwork' => $artwork, 'headingTag' => 'h2'])
            @empty
                <div class="col-span-full text-center py-16 text-[#557466]">
                    {{ __('Aucune œuvre disponible pour le moment.') }}
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
