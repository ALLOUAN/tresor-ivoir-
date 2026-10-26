<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offres groupées') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => __('Offres groupées'), 'description' => __('Séjours tout compris en Côte d\'Ivoire : hébergement, visites, repas et transport réunis en une seule réservation.')])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .bundle-hero { background: #0E3B2A; }
        .bundle-card { border: 1px solid rgba(14,59,42,0.07); background: #FFFFFF; box-shadow: 0 10px 28px rgba(14,59,42,0.06); transition: transform .25s ease, box-shadow .25s ease; }
        .bundle-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(0,135,79,0.14); }
        .bundle-cover { position: relative; overflow: hidden; background: #0E3B2A; }
        .bundle-cover img { transition: transform .5s ease; }
        .bundle-card:hover .bundle-cover img { transform: scale(1.06); }
        .chip { border: 1px solid rgba(14,59,42,0.1); background: #fff; }
        .chip.is-active { background: #00874F; border-color: #00874F; color: #fff; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="bundle-hero relative py-16 sm:py-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <p class="text-sm font-semibold uppercase tracking-widest mb-3 text-[#EB6E00]">{{ __('Offres groupées') }}</p>
            <h1 class="font-serif text-3xl sm:text-5xl font-bold text-white mb-4">{{ __('Votre séjour, tout compris') }}</h1>
            <p class="text-white/75 max-w-2xl mx-auto">{{ __('Hébergement, visites, repas et transport réunis en une seule réservation, avec un prix par personne connu d\'avance.') }}</p>
        </div>
    </section>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        @if($cities->isNotEmpty())
            <div class="flex gap-2 overflow-x-auto pb-2 mb-6">
                <a href="{{ route('bundles.index') }}" class="chip shrink-0 rounded-full px-4 py-2 text-sm font-semibold {{ $city === '' ? 'is-active' : '' }}">{{ __('Toutes villes') }}</a>
                @foreach($cities as $c)
                    <a href="{{ route('bundles.index', ['ville' => $c->slug]) }}" class="chip shrink-0 rounded-full px-4 py-2 text-sm font-semibold {{ $city === $c->slug ? 'is-active' : '' }}">{{ $c->name }}</a>
                @endforeach
            </div>
        @endif

        @if($bundles->isEmpty())
            <div class="bundle-card rounded-2xl p-10 text-center">
                <i class="fas fa-layer-group text-3xl text-[#00874F] mb-3"></i>
                <p class="font-semibold">{{ __('Aucune offre groupée pour le moment.') }}</p>
                <p class="text-sm text-[#557466] mt-1">{{ __('Revenez bientôt, ou composez votre propre séjour avec notre planificateur.') }}</p>
                <a href="{{ route('trip.create') }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl bg-[#EB6E00] text-white font-semibold text-sm">{{ __('Planifier mon séjour') }}</a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($bundles as $b)
                    <a href="{{ route('bundles.show', $b) }}" class="bundle-card rounded-2xl overflow-hidden flex flex-col">
                        <div class="bundle-cover aspect-[4/3]">
                            @if($b->cover_url)
                                <img src="{{ $b->cover_url }}" alt="{{ $b->localized('title') }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white/40"><i class="fas fa-layer-group text-4xl"></i></div>
                            @endif
                            @if($b->is_featured)
                                <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-[#EB6E00] text-[#0E3B2A] text-xs font-bold">{{ __('Coup de cœur') }}</span>
                            @endif
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <p class="text-xs text-[#557466]">
                                <i class="fas fa-clock mr-1 text-[#00874F]"></i>{{ trans_choice(':count jour|:count jours', $b->duration_days) }}
                                @if($b->city) · <i class="fas fa-location-dot mx-1 text-[#00874F]"></i>{{ $b->city->name }}@endif
                            </p>
                            <h2 class="font-serif text-xl font-bold mt-1.5">{{ $b->localized('title') }}</h2>
                            @if($b->localized('summary'))
                                <p class="text-sm text-[#24503D] mt-2 line-clamp-3">{{ $b->localized('summary') }}</p>
                            @endif
                            <div class="mt-auto pt-4 flex items-end justify-between">
                                <p><span class="text-xs text-[#557466]">{{ __('à partir de') }}</span><br><span class="text-lg font-extrabold text-[#00874F]">{{ number_format($b->price_xof, 0, ',', ' ') }} FCFA</span> <span class="text-xs text-[#557466]">{{ __('/ personne') }}</span></p>
                                <span class="text-sm font-semibold text-[#EB6E00]">{{ __('Voir l\'offre') }} →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </main>

    @include('partials.homepage-footer')
</body>
</html>
