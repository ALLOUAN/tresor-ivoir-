<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bundle->localized('title') }} — {{ __('Offres groupées') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => $bundle->localized('title'), 'description' => $bundle->localized('summary') ?: $bundle->localized('description'), 'image' => $bundle->cover_image, 'url' => route('bundles.show', $bundle)])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .bento-card { border: 1px solid rgba(14,59,42,0.07); background: #FFFFFF; box-shadow: 0 10px 28px rgba(14,59,42,0.06); }
        .contact-card { border: 1px solid rgba(14,59,42,0.08); background: #FFFFFF; box-shadow: 0 20px 50px rgba(14,59,42,0.1); }
        .btn-primary { background: #EB6E00; color: #fff; }
        .btn-primary:hover { background: #EB6E00; }
        .bundle-hero { position: relative; background: #0E3B2A; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="bundle-hero">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14 grid lg:grid-cols-5 gap-8 items-center">
            <div class="lg:col-span-3 text-white">
                <a href="{{ route('bundles.index') }}" class="text-sm text-white/70 hover:text-white">← {{ __('Toutes les offres') }}</a>
                <h1 class="font-serif text-3xl sm:text-5xl font-bold mt-3">{{ $bundle->localized('title') }}</h1>
                @if($bundle->localized('summary'))
                    <p class="text-white/80 mt-3 text-lg">{{ $bundle->localized('summary') }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-5 text-sm">
                    <span class="px-3 py-1.5 rounded-full bg-white/10"><i class="fas fa-clock mr-1.5 text-[#EB6E00]"></i>{{ trans_choice(':count jour|:count jours', $bundle->duration_days) }}</span>
                    @if($bundle->city)<span class="px-3 py-1.5 rounded-full bg-white/10"><i class="fas fa-location-dot mr-1.5 text-[#EB6E00]"></i>{{ $bundle->city->name }}</span>@endif
                    <span class="px-3 py-1.5 rounded-full bg-white/10"><i class="fas fa-users mr-1.5 text-[#EB6E00]"></i>{{ $bundle->max_people ? __(':min à :max voyageurs', ['min' => $bundle->min_people, 'max' => $bundle->max_people]) : __('À partir de :min voyageur(s)', ['min' => $bundle->min_people]) }}</span>
                </div>
            </div>
            <div class="lg:col-span-2">
                @if($bundle->cover_url)
                    <img src="{{ $bundle->cover_url }}" alt="{{ $bundle->localized('title') }}" class="w-full aspect-[4/3] object-cover rounded-3xl" decoding="async">
                @endif
            </div>
        </div>
    </section>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-10 grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-3 space-y-8">
            @if($bundle->localized('description'))
                <section>
                    <h2 class="font-serif text-2xl font-bold mb-3">{{ __('Le programme') }}</h2>
                    <div class="bento-card rounded-2xl p-5 text-[#24503D] leading-relaxed whitespace-pre-line">{{ $bundle->localized('description') }}</div>
                </section>
            @endif

            @if(! empty($bundle->highlights))
                <section>
                    <h2 class="font-serif text-2xl font-bold mb-3">{{ __('Ce qui est compris') }}</h2>
                    <ul class="bento-card rounded-2xl p-5 space-y-2.5">
                        @foreach($bundle->highlights as $h)
                            <li class="flex items-start gap-2.5 text-sm"><i class="fas fa-circle-check text-[#00874F] mt-0.5"></i><span>{{ $h }}</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if($items->isNotEmpty())
                <section>
                    <h2 class="font-serif text-2xl font-bold mb-3">{{ __('Les adresses de votre séjour') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($items as $item)
                            <a href="{{ $item['url'] ?? '#' }}" class="bento-card rounded-2xl p-4 flex items-center gap-3 hover:border-[#00874F]/40 transition">
                                <span class="w-10 h-10 rounded-xl bg-[#00874F]/10 text-[#00874F] flex items-center justify-center shrink-0"><i class="fas fa-map-pin"></i></span>
                                <span class="min-w-0">
                                    <span class="block text-xs text-[#557466]">{{ $item['label'] }}</span>
                                    <span class="block font-semibold truncate">{{ $item['model']->name }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="lg:col-span-2">
            <div class="lg:sticky lg:top-24 space-y-3">
                <div class="bento-card rounded-2xl p-5">
                    <p class="text-sm text-[#557466]">{{ __('Prix par personne') }}</p>
                    <p class="text-3xl font-extrabold text-[#00874F]">{{ number_format($bundle->price_xof, 0, ',', ' ') }} FCFA</p>
                    <p class="text-xs text-[#557466] mt-1">{{ __('Le prix final est confirmé par l\'organisateur, puis réglé en ligne de façon sécurisée.') }}</p>
                </div>
                @include('partials.booking-request-form', ['type' => 'bundle', 'target' => $bundle])
            </div>
        </aside>
    </main>

    @if($others->isNotEmpty())
        <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-12">
            <h2 class="font-serif text-2xl font-bold mb-4">{{ __('Autres offres') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($others as $o)
                    <a href="{{ route('bundles.show', $o) }}" class="bento-card rounded-2xl p-4">
                        <p class="font-semibold">{{ $o->localized('title') }}</p>
                        <p class="text-sm text-[#00874F] font-bold mt-1">{{ number_format($o->price_xof, 0, ',', ' ') }} FCFA <span class="text-xs text-[#557466] font-normal">{{ __('/ personne') }}</span></p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.homepage-footer')
</body>
</html>
