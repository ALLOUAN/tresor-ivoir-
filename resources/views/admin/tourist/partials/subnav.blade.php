@php
    $touristNav = [
        [
            'label'  => 'Villes',
            'route'  => 'admin.tourist.cities.index',
            'icon'   => 'fas fa-city',
            'match'  => 'admin.tourist.cities.*',
            'count'  => \App\Models\TouristCity::count(),
        ],
        [
            'label'  => 'Catégories',
            'route'  => 'admin.tourist.categories.index',
            'icon'   => 'fas fa-tags',
            'match'  => 'admin.tourist.categories.*',
            'count'  => \App\Models\TouristCategory::count(),
        ],
        [
            'label'  => 'Sites',
            'route'  => 'admin.tourist.sites.index',
            'icon'   => 'fas fa-map-pin',
            'match'  => 'admin.tourist.sites.*',
            'count'  => \App\Models\TouristSite::count(),
        ],
    ];
@endphp

<div class="flex items-center gap-1 bg-slate-900 border border-slate-800 rounded-xl p-1 mb-6">
    @foreach($touristNav as $item)
    <a href="{{ route($item['route']) }}"
       class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition
              {{ request()->routeIs($item['match'])
                  ? 'bg-amber-500 text-black shadow-sm'
                  : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
        <i class="{{ $item['icon'] }} text-xs"></i>
        <span>{{ $item['label'] }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold
            {{ request()->routeIs($item['match'])
                ? 'bg-black/20 text-black'
                : 'bg-slate-800 text-slate-500' }}">
            {{ $item['count'] }}
        </span>
    </a>
    @endforeach
</div>
