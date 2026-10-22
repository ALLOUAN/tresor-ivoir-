<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Résidences & Hôtels') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .btn-primary {
            background: #f2790f;
            box-shadow: 0 10px 24px rgba(242,121,15,0.3);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
            color: #1b1408;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .hero-lodging {
            background:
                linear-gradient(transparent, transparent),
                linear-gradient(#1c1912, #1c1912);
        }
        .filter-card { border: 1px solid rgba(0,0,0,0.08); background: #ffffff; box-shadow: 0 14px 34px rgba(20,18,12,0.08); }
        .accom-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: #fdfcf9;
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .accom-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(194,94,10,0.14); border-color: rgba(242,121,15,0.3); }
        .accom-cover { position: relative; overflow: hidden; background: #14130f; }
        .accom-cover img { transition: transform .5s ease; }
        .accom-card:hover .accom-cover img { transform: scale(1.06); }
        #filterPanel { transition: none; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="hero-lodging relative py-20 overflow-hidden">
        @include('partials.ivorian-motifs', ['culture' => 'hotels'])
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="ti-hero-card ti-tour-hero inline-block rounded-[28px] px-6 py-8 sm:px-12 sm:py-10">
                <p class="ti-tour-hero__kicker"><i class="fas fa-hotel" aria-hidden="true"></i>{{ __('Résidences & Hôtels') }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">{{ __('Trouvez votre hébergement idéal') }}</h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">{{ __('Hôtels et résidences vérifiés partout en Côte d\'Ivoire — réservation en ligne avec acompte sécurisé.') }}</p>
            </div>
        </div>
        @include('partials.hero-separator', ['class' => 'relative z-10 mt-10'])
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 relative z-10 pb-16">

        <button type="button" id="filterToggleBtn" class="lg:hidden w-full mb-4 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white border border-black/10 shadow-md font-semibold text-sm">
            <i class="fas fa-sliders"></i> {{ __('Filtrer') }}
        </button>

        <div id="filterPanel"
             class="hidden lg:!block lg:!static lg:!inset-auto lg:!bg-transparent lg:!p-0 lg:!z-auto fixed inset-0 z-50 bg-[#14130f]/95 p-5 overflow-y-auto">
            <div class="flex items-center justify-between mb-4 lg:hidden">
                <p class="text-white font-semibold">{{ __('Filtrer les résultats') }}</p>
                <button type="button" id="filterCloseBtn" class="w-9 h-9 rounded-full bg-white/10 text-white flex items-center justify-center"><i class="fas fa-xmark"></i></button>
            </div>

            <form method="GET" action="{{ route('accommodations.index') }}" class="filter-card rounded-2xl p-5 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Région') }}</label>
                        <select name="region" id="regionSelect" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">{{ __('Toutes régions') }}</option>
                            @foreach($regions as $r)
                                <option value="{{ $r }}" @selected($region === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Ville') }}</label>
                        <select name="ville" id="citySelect" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">{{ __('Toutes villes') }}</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" @selected((string) $cityId === (string) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Type') }}</label>
                        <select name="type" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">{{ __('Tous types') }}</option>
                            @foreach(['hotel'=>__('Hôtel'),'residence'=>__('Résidence'),'resort'=>__('Resort'),'guesthouse'=>__('Maison d\'hôtes'),'hostel'=>__('Auberge de jeunesse'),'auberge'=>__('Auberge'),'villa'=>__('Villa'),'eco_lodge'=>'Éco-lodge'] as $val => $label)
                                <option value="{{ $val }}" @selected($type === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Arrivée') }}</label>
                        <input type="date" name="arrivee" value="{{ $checkIn }}" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Départ') }}</label>
                        <input type="date" name="depart" value="{{ $checkOut }}" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Voyageurs') }}</label>
                        <input type="number" name="voyageurs" min="1" max="30" value="{{ $guests ?: '' }}" placeholder="2" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 mt-3">
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Budget (à partir de, par nuit)') }}</label>
                        <select name="budget" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">{{ __('Tous budgets') }}</option>
                            <option value="low" @selected($budget==='low')>{{ __('Moins de 50 000 FCFA') }}</option>
                            <option value="mid" @selected($budget==='mid')>{{ __('50 000 – 100 000 FCFA') }}</option>
                            <option value="high" @selected($budget==='high')>{{ __('100 000 – 200 000 FCFA') }}</option>
                            <option value="luxury" @selected($budget==='luxury')>{{ __('Plus de 200 000 FCFA') }}</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">{{ __('Trier par') }}</label>
                        <select name="tri" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="pertinence" @selected($sort==='pertinence')>{{ __('Pertinence') }}</option>
                            <option value="prix_asc" @selected($sort==='prix_asc')>{{ __('Prix croissant') }}</option>
                            <option value="prix_desc" @selected($sort==='prix_desc')>{{ __('Prix décroissant') }}</option>
                            <option value="popularite" @selected($sort==='popularite')>{{ __('Popularité') }}</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2 flex items-end gap-2">
                        <button type="submit" class="btn-primary btn-shine flex-1 px-4 py-2.5 rounded-lg font-bold text-sm">
                            <i class="fas fa-magnifying-glass mr-1.5"></i> {{ __('Rechercher') }}
                        </button>
                        <a href="{{ route('accommodations.index') }}" class="px-4 py-2.5 rounded-lg border border-black/10 text-sm font-semibold hover:bg-black/5 transition">{{ __('Réinitialiser') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="ti-tour-bar"><p class="ti-tour-bar__count"><strong>{{ $accommodations->count() }}</strong> {{ $accommodations->count() > 1 ? __('hébergements trouvés') : __('hébergement trouvé') }}</p></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($accommodations as $a)
                @include('partials.home-sector-card', ['it' => \App\Support\HomeSectorShowcase::card($a, 'hotels') + ['available' => $checkIn && $checkOut]])
            @empty
            <div class="col-span-full text-center py-16 rounded-2xl border border-dashed border-black/10 bg-white/60">
                <i class="fas fa-hotel text-3xl mb-3 block text-[#c9bfa8]"></i>
                <p class="text-[#8a7f6b]">{{ __('Aucun hébergement ne correspond à ces critères. Essayez d\'élargir votre recherche.') }}</p>
            </div>
            @endforelse
        </div>
    </div>

@include('partials.place-card-script')
@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
(function () {
    const toggleBtn = document.getElementById('filterToggleBtn');
    const closeBtn = document.getElementById('filterCloseBtn');
    const panel = document.getElementById('filterPanel');
    if (toggleBtn && panel) toggleBtn.addEventListener('click', () => panel.classList.remove('hidden'));
    if (closeBtn && panel) closeBtn.addEventListener('click', () => panel.classList.add('hidden'));

    const regionSelect = document.getElementById('regionSelect');
    const citySelect = document.getElementById('citySelect');
    if (!regionSelect || !citySelect) return;

    const citiesUrl = @json(route('accommodations.cities'));
    const initialCityId = @json($cityId ? (string) $cityId : '');

    regionSelect.addEventListener('change', () => {
        const region = regionSelect.value;
        citySelect.innerHTML = '<option value="">Toutes villes</option>';
        if (!region) return;

        fetch(`${citiesUrl}?region=${encodeURIComponent(region)}`, { headers: { 'Accept': 'application/json' } })
            .then((r) => r.json())
            .then((data) => {
                (data.cities || []).forEach((c) => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    if (String(c.id) === initialCityId) opt.selected = true;
                    citySelect.appendChild(opt);
                });
            });
    });
})();
</script>
</body>
</html>
