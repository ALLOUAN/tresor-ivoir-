{{--
    Tableau de bord prestataire : « Mes secteurs » — chaque secteur exercé, son forfait et son échéance,
    l'accès direct à son espace, et l'ajout d'un secteur (page des forfaits, sélection multiple).
    Attend : $provider.
--}}
@php
    $pvSpaces = [
        'hotels' => ['provider.accommodation.dashboard', 'fa-bed'],
        'restaurants' => ['provider.menu.dashboard', 'fa-utensils'],
        'agences-voyages' => ['provider.tours.dashboard', 'fa-plane-departure'],
        'sites-touristiques' => ['provider.tourist-experience.dashboard', 'fa-landmark'],
        'loisirs-culture' => ['provider.leisure-venue.dashboard', 'fa-masks-theater'],
        'transports' => ['provider.transport.dashboard', 'fa-van-shuttle'],
        'art-creations' => ['provider.artworks.dashboard', 'fa-palette'],
    ];
    $pvSectorSlugs = $provider->sectorSlugs();
    $pvCats = \App\Models\ProviderCategory::whereIn('slug', $pvSectorSlugs)->whereNull('parent_id')->get()->keyBy('slug');
    $pvPrimaryId = $provider->category?->parent_id ? $provider->category->parent_id : $provider->category_id;
    $pvSubs = $provider->subscriptions()->with('plan')->where('status', 'active')->where('ends_at', '>=', now())->get()
        ->keyBy(fn ($s) => $s->sector_category_id ?: $pvPrimaryId);
@endphp
<section class="mb-6" aria-labelledby="pv-sectors-title">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <h2 id="pv-sectors-title" class="text-white text-lg font-bold">{{ count($pvSectorSlugs) > 1 ? __('Mes secteurs') : __('Mon secteur') }}</h2>
        <a href="{{ route('plans.public', ['secteurs' => implode(',', $pvSectorSlugs)]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:border-orange-500/60">
            <i class="fas fa-plus"></i> {{ __('Ajouter un secteur') }}
        </a>
    </div>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($pvSectorSlugs as $slug)
            @php
                $cat = $pvCats->get($slug);
                [$route, $icon] = $pvSpaces[$slug] ?? ['provider.dashboard', 'fa-store'];
                $sub = $cat ? $pvSubs->get($cat->id) : null;
                $left = $sub?->ends_at ? now()->startOfDay()->diffInDays($sub->ends_at->copy()->startOfDay(), false) : null;
            @endphp
            <a href="{{ \Illuminate\Support\Facades\Route::has($route) ? route($route) : route('provider.dashboard') }}" class="group flex items-center gap-3 p-4 rounded-xl bg-green-900 border border-slate-800 hover:border-orange-500/50 transition">
                <span class="w-11 h-11 rounded-xl bg-orange-500/15 text-orange-400 flex items-center justify-center shrink-0"><i class="fas {{ $icon }}"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-white text-sm font-semibold truncate">{{ $cat?->localized('name') ?? $slug }}</span>
                    @if($sub)
                        <span class="block text-xs text-emerald-300">{{ __('Forfait') }} {{ $sub->plan?->localized('name') ?? strtoupper((string) $sub->plan?->code) }} · {{ $left !== null && $left >= 0 ? __(':n j restants', ['n' => $left]) : __('Expiré') }}</span>
                    @else
                        <span class="block text-xs text-amber-300">{{ __('Sans forfait actif') }}</span>
                    @endif
                </span>
                <i class="fas fa-chevron-right text-xs text-slate-500 group-hover:text-orange-400"></i>
            </a>
        @endforeach
    </div>
</section>
