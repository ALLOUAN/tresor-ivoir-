<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Tous nos établissements') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .hero-establishments {
            background:
                linear-gradient(transparent, transparent),
                linear-gradient(#0E3B2A, #0E3B2A);
        }
        .home-provider-card-bg { transition: transform .6s ease; }
        .group:hover .home-provider-card-bg { transform: scale(1.06); }

        [data-est-panel][hidden] { display: none !important; }
        .est-tab { cursor: pointer; }
        .est-tab.is-active { background: rgba(235,110,0, .12); border-color: rgba(235,110,0, .35); }
        .est-tab.is-active .est-tab-icon { background: rgba(235,110,0, .18); color: #EB6E00; }
        .est-tab.is-active .est-tab-label { color: #B85600 !important; }

        /* Bandeau d'en-tête sur fond papier (partials.ui-kit) : texte sombre lisible. */
        html:not(.dark) .hero-establishments .text-white { color: #0E3B2A !important; }
        html:not(.dark) .hero-establishments .text-white\/70 { color: #24503D !important; }
        html:not(.dark) .hero-establishments .text-orange-300 { color: #EB6E00 !important; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="hero-establishments relative py-16 sm:py-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <p class="text-orange-300 text-xs tracking-[.25em] uppercase font-semibold mb-3">{{ __('Annuaire complet') }}</p>
            <h1 class="font-serif text-3xl sm:text-5xl font-bold text-white mb-4">{{ __('Tous nos établissements') }}</h1>
            <p class="text-white/70 text-base sm:text-lg max-w-2xl mx-auto">
                {{ $totalCount }} {{ $totalCount > 1 ? __('établissements vérifiés') : __('établissement vérifié') }} {{ __(': hôtels, restaurants, sites touristiques, agences de voyages, loisirs & culture, transports.') }}
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-8 relative z-10 pb-16">

        {{-- Onglets secteur --}}
        <div class="flex flex-wrap gap-2 justify-center mb-8" id="est-tabs" role="tablist" aria-label="{{ __('Secteurs') }}">
            <button type="button" class="est-tab is-active inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-black/8 bg-white text-sm font-semibold transition"
                    role="tab" id="est-tab-all" aria-selected="true" aria-controls="est-panel-all" data-est-tab="all">
                <span class="est-tab-icon w-7 h-7 rounded-lg bg-black/[0.04] flex items-center justify-center"><i class="fas fa-border-all text-xs"></i></span>
                <span class="est-tab-label">{{ __('Tous') }}</span>
                <span class="text-[#557466] text-xs font-bold">{{ $totalCount }}</span>
            </button>
            @foreach($sectors as $sector)
            @php $count = ($showcase[$sector->slug]['items'] ?? collect())->count(); @endphp
            @if($count > 0)
            <button type="button" class="est-tab inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-black/8 bg-white text-sm font-semibold transition"
                    role="tab" id="est-tab-{{ $sector->slug }}" aria-selected="false" aria-controls="est-panel-{{ $sector->slug }}" data-est-tab="{{ $sector->slug }}">
                <span class="est-tab-icon w-7 h-7 rounded-lg bg-black/[0.04] flex items-center justify-center"><i class="fas {{ $showcase[$sector->slug]['items']->first()['icon'] ?? 'fa-store' }} text-xs"></i></span>
                <span class="est-tab-label">{{ $sector->localized('name') }}</span>
                <span class="text-[#557466] text-xs font-bold">{{ $count }}</span>
            </button>
            @endif
            @endforeach
        </div>

        {{-- Panneau « Tous » : chaque secteur, l'un sous l'autre --}}
        <div id="est-panel-all" data-est-panel="all" role="tabpanel" aria-labelledby="est-tab-all" class="space-y-12">
            @forelse($sectors as $sector)
                @php $items = $showcase[$sector->slug]['items'] ?? collect(); @endphp
                @continue($items->isEmpty())
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-serif text-xl sm:text-2xl font-bold">{{ $sector->localized('name') }}</h2>
                        <a href="{{ $showcase[$sector->slug]['index_url'] }}" class="text-[#EB6E00] hover:text-[#B85600] text-sm font-semibold inline-flex items-center gap-1.5 transition">
                            {{ __('Voir tout') }} <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($items as $it)
                            @include('partials.home-sector-card', ['it' => $it])
                        @endforeach
                    </div>
                </div>
            @empty
            <div class="text-center py-16 rounded-2xl border border-dashed border-black/10 bg-white/60">
                <i class="fas fa-store-slash text-3xl mb-3 block text-[#8AA99B]"></i>
                <p class="text-[#557466]">{{ __('Aucun établissement disponible pour le moment.') }}</p>
            </div>
            @endforelse
        </div>

        {{-- Un panneau par secteur (masqué tant que l'onglet n'est pas choisi) --}}
        @foreach($sectors as $sector)
            @php $items = $showcase[$sector->slug]['items'] ?? collect(); @endphp
            @continue($items->isEmpty())
            <div id="est-panel-{{ $sector->slug }}" data-est-panel="{{ $sector->slug }}" role="tabpanel" aria-labelledby="est-tab-{{ $sector->slug }}" hidden>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($items as $it)
                        @include('partials.home-sector-card', ['it' => $it])
                    @endforeach
                </div>
                <a href="{{ $showcase[$sector->slug]['index_url'] }}" class="mt-6 inline-flex items-center gap-2 text-[#EB6E00] hover:text-[#B85600] text-sm font-semibold transition">
                    {{ __('Voir tout :') }} {{ $sector->localized('name') }} <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        @endforeach
    </div>

@include('partials.homepage-footer')
@include('partials.place-card-script')
@include('partials.image-protection')
<script>
(function () {
    const tabs = document.querySelectorAll('[data-est-tab]');
    const panels = document.querySelectorAll('[data-est-panel]');
    if (!tabs.length || !panels.length) return;

    tabs.forEach(function (t) {
        t.addEventListener('click', function () {
            const key = t.getAttribute('data-est-tab');
            tabs.forEach(function (b) {
                const on = b === t;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (p) {
                p.hidden = p.getAttribute('data-est-panel') !== key;
            });
        });
    });
})();
</script>
</body>
</html>
