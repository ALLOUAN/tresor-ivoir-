<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Agences de Voyages & Tours') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .hero-agency {
            background:
                linear-gradient(transparent, transparent),
                linear-gradient(#1c1912, #1c1912);
        }
        .filter-card { border: 1px solid rgba(0,0,0,0.08); background: #ffffff; box-shadow: 0 14px 34px rgba(20,18,12,0.08); }
        .venue-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: #fdfcf9;
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .venue-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(41,128,185,0.14); border-color: rgba(41,128,185,0.3); }
        .venue-cover { position: relative; overflow: hidden; background: #14130f; }
        .venue-cover img { transition: transform .5s ease; }
        .venue-card:hover .venue-cover img { transform: scale(1.06); }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="hero-agency relative py-20 overflow-hidden">
        @include('partials.ivorian-motifs', ['culture' => 'agences'])
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="ti-hero-card ti-tour-hero inline-block rounded-[28px] px-6 py-8 sm:px-12 sm:py-10">
                <p class="ti-tour-hero__kicker"><i class="fas fa-plane-departure" aria-hidden="true"></i>{{ __('Agences de Voyages & Tours') }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">{{ __('Organisez votre prochain voyage') }}</h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">{{ __('Agences de voyages et organisateurs de circuits vérifiés en Côte d\'Ivoire.') }}</p>
            </div>
        </div>
        @include('partials.hero-separator', ['class' => 'relative z-10 mt-10'])
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 relative z-10 pb-16">

        <form method="GET" action="{{ route('travel-agency.index') }}" class="filter-card rounded-2xl p-5 mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Ville') }}</label>
                    <select name="ville" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">{{ __('Toutes villes') }}</option>
                        @foreach($cities as $c)
                            <option value="{{ $c->id }}" @selected((string) $cityId === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Trier par') }}</label>
                    <select name="tri" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="pertinence" @selected($sort==='pertinence')>{{ __('Pertinence') }}</option>
                        <option value="recent" @selected($sort==='recent')>{{ __('Plus récent') }}</option>
                        <option value="popularite" @selected($sort==='popularite')>{{ __('Popularité') }}</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary flex-1 px-4 py-2.5 rounded-lg font-bold text-sm">
                        <i class="fas fa-magnifying-glass mr-1.5"></i> {{ __('Rechercher') }}
                    </button>
                    <a href="{{ route('travel-agency.index') }}" class="px-4 py-2.5 rounded-lg border border-black/10 text-sm font-semibold hover:bg-black/5 transition">{{ __('Réinitialiser') }}</a>
                </div>
            </div>
        </form>

        <div class="ti-tour-bar"><p class="ti-tour-bar__count"><strong>{{ $agencies->count() }}</strong> {{ $agencies->count() > 1 ? __('agences trouvées') : __('agence trouvée') }}</p></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($agencies as $agency)
                @include('partials.home-sector-card', ['it' => \App\Support\HomeSectorShowcase::card($agency, 'agences-voyages')])
            @empty
            <div class="col-span-full text-center py-16 rounded-2xl border border-dashed border-black/10 bg-white/60">
                <i class="fas fa-plane text-3xl mb-3 block text-[#c9bfa8]"></i>
                <p class="text-[#8a7f6b]">{{ __('Aucune agence ne correspond à ces critères.') }}</p>
            </div>
            @endforelse
        </div>
    </div>

@include('partials.place-card-script')
@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
