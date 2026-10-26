<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Annuaire des prestataires') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Montserrat', serif; }
        .providers-filter-shell {
            box-shadow: 0 12px 28px rgba(14,59,42,0.18);
        }
        .provider-list-card {
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .provider-list-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(14,59,42,0.2);
        }
        html:not(.dark) .providers-filter-shell {
            background:#D8E9E0 !important;
            border-color: rgba(14,59,42,0.08) !important;
            box-shadow: 0 12px 26px rgba(14,59,42,0.06);
        }
        html:not(.dark) .provider-list-card {
            background:#D8E9E0 !important;
            border-color: rgba(14,59,42,0.08) !important;
            box-shadow: 0 10px 22px rgba(14,59,42,0.05);
        }
        /* Image de fond configurable (back-office) du bandeau de la page Annuaire. */
        .providers-hero {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .providers-hero[style*="--providers-hero-bg-image"] {
            background-image: var(--providers-hero-bg-image, none);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="bg-[#ffffff] text-white">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    @php
        $providersHeroImage = \Illuminate\Support\Facades\Schema::hasTable('providers_hero_images')
            ? \App\Models\ProvidersHeroImage::query()->find(1)
            : null;
    @endphp

    <section class="relative py-20 overflow-hidden {{ ($providersHeroImage && $providersHeroImage->isVisible()) ? 'providers-hero' : '' }}"
        @if($providersHeroImage && $providersHeroImage->isVisible())
            style="--providers-hero-bg-image: url('{{ $providersHeroImage->image_url }}');"
        @endif
    >
        <a href="{{ route('home') }}" class="absolute top-6 right-6 z-10 rounded-full bg-white/95 px-3.5 py-1.5 text-sm font-medium shadow-sm hover:underline" style="color:#EB6E00">{{ __('← Retour accueil') }}</a>
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="ti-hero-card inline-block rounded-2xl px-6 py-8 sm:px-12 sm:py-10">
                <p class="text-orange-400 text-sm font-medium uppercase tracking-widest mb-3">{{ __('Annuaire') }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                    {{ __('Annuaire des prestataires') }}
                </h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">
                    {{ __('Trouvez les meilleures adresses par ville, catégorie et gamme de prix.') }}
                </p>
            </div>
        </div>
    </section>

@include('partials.hero-separator')

{{-- Recommandations personnalisées (module IA 1) --}}
@include('partials.ai-reco-strip', ['limit' => 4, 'heading' => __('Recommandé pour vous')])

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <div class="providers-filter-shell bg-[#ffffff] border border-white/8 rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('providers.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="{{ __('Nom, ville ou description...') }}"
                    class="md:col-span-2 bg-[#ffffff] border border-white/10 rounded-lg px-3 py-2 text-sm"
                >
                <select name="categorie" class="bg-[#ffffff] border border-white/10 rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('Toutes catégories') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($activeCategory?->id === $cat->id)>
                            {{ $cat->localized('name') }} ({{ $cat->providers_count }})
                        </option>
                    @endforeach
                </select>
                <select name="ville" class="bg-[#ffffff] border border-white/10 rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('Toutes villes') }}</option>
                    @foreach($cities as $v)
                        <option value="{{ $v }}" @selected($city === $v)>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="prix" class="bg-[#ffffff] border border-white/10 rounded-lg px-3 py-2 text-sm">
                    <option value="">{{ __('Tous prix') }}</option>
                    @foreach(['budget' => __('Budget'), 'mid' => __('Moyen'), 'premium' => __('Premium'), 'luxury' => __('Luxe')] as $value => $label)
                        <option value="{{ $value }}" @selected($price === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="tri" class="bg-[#ffffff] border border-white/10 rounded-lg px-3 py-2 text-sm">
                    <option value="rating" @selected($sort === 'rating')>{{ __('Tri: mieux notés') }}</option>
                    <option value="name" @selected($sort === 'name')>{{ __('Tri: nom') }}</option>
                    <option value="newest" @selected($sort === 'newest')>{{ __('Tri: plus récents') }}</option>
                    <option value="views" @selected($sort === 'views')>{{ __('Tri: plus vus') }}</option>
                </select>
                <label class="md:col-span-2 text-gray-300 text-sm flex items-center gap-2">
                    <input type="checkbox" name="verifie" value="1" @checked(request()->boolean('verifie'))>
                    {{ __('Prestataires vérifiés uniquement') }}
                </label>
                <div class="md:col-span-4 flex items-center gap-2">
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-black font-semibold text-sm px-4 py-2 rounded-lg">{{ __('Filtrer') }}</button>
                    <a href="{{ route('providers.index') }}" class="bg-[#ffffff] hover:bg-[#ffffff] border border-white/10 text-white text-sm px-4 py-2 rounded-lg transition">{{ __('Réinitialiser') }}</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($providers as $provider)
                <a href="{{ route('providers.show', $provider->slug) }}" class="provider-list-card bg-[#ffffff] border border-white/5 rounded-xl overflow-hidden hover:border-orange-500/40 transition">
                    <div class="h-40 bg-[#ffffff]">
                        @if($provider->cover_url)
                            <img src="{{ $provider->cover_url }}" alt="{{ $provider->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-600">
                                <i class="fas fa-store text-3xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-white font-semibold">{{ $provider->name }}</p>
                            @if($provider->is_verified)
                                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded-full">{{ __('Vérifié') }}</span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-xs mt-1">{{ collect([$provider->category->localized('name') ?? null, $provider->city])->filter()->join(' · ') }}</p>
                        <p class="text-gray-300 text-sm mt-2 line-clamp-2">{{ $provider->short_desc_fr ?: \Illuminate\Support\Str::limit($provider->localized('description'), 90) }}</p>
                        @php
                            $cardRating = (float) ($provider->rating_avg ?? 0);
                            $cardPrice = ['budget' => __('Économique'), 'mid' => __('Intermédiaire'), 'premium' => __('Haut de gamme'), 'luxury' => __('Luxe')][$provider->price_range] ?? null;
                        @endphp
                        <div class="mt-3 flex items-center justify-between text-xs">
                            @if($cardRating > 0)
                                <span class="font-semibold text-[#0E3B2A]"><i class="fas fa-star text-[10px] mr-1" style="color:#EB6E00"></i>{{ number_format($cardRating, 1, ',', ' ') }}</span>
                            @else
                                <span class="text-gray-500">{{ __('Pas encore d’avis') }}</span>
                            @endif
                            @if($cardPrice)
                                <span class="text-gray-500">{{ $cardPrice }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 bg-[#ffffff] border border-white/8 rounded-xl p-8 text-center text-gray-500">
                    {{ __('Aucun prestataire trouvé avec ces filtres.') }}
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $providers->links() }}
        </div>
    </div>
@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
