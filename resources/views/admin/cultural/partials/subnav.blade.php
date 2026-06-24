@php
    $culturalNav = [
        [
            'label' => 'Peuples',
            'route' => 'admin.cultural.peoples.index',
            'icon'  => 'fas fa-people-group',
            'match' => 'admin.cultural.peoples.*',
            'count' => \App\Models\CulturalPeople::count(),
        ],
        [
            'label' => 'Domaines',
            'route' => 'admin.cultural.domains.index',
            'icon'  => 'fas fa-layer-group',
            'match' => 'admin.cultural.domains.*',
            'count' => \App\Models\CulturalDomain::count(),
        ],
        [
            'label' => 'Éléments',
            'route' => 'admin.cultural.elements.index',
            'icon'  => 'fas fa-masks-theater',
            'match' => 'admin.cultural.elements.*',
            'count' => \App\Models\CulturalElement::count(),
        ],
    ];
@endphp

<div class="flex items-center gap-1 bg-slate-900 border border-slate-800 rounded-xl p-1 mb-6">
    @foreach($culturalNav as $item)
    <a href="{{ route($item['route']) }}"
       class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition
              {{ request()->routeIs($item['match'])
                  ? 'bg-amber-500 text-black shadow-sm'
                  : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
        <i class="{{ $item['icon'] }} text-xs"></i>
        <span>{{ $item['label'] }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold
            {{ request()->routeIs($item['match']) ? 'bg-black/20 text-black' : 'bg-slate-800 text-slate-500' }}">
            {{ $item['count'] }}
        </span>
    </a>
    @endforeach
</div>
