@php
    $providerNav = [
        [
            'label'  => 'Prestataires',
            'route'  => 'admin.providers.index',
            'icon'   => 'fas fa-store',
            'match'  => 'admin.providers.index',
            'count'  => \App\Models\Provider::count(),
        ],
        [
            'label'  => 'Catégories',
            'route'  => 'admin.providers.categories.index',
            'icon'   => 'fas fa-tags',
            'match'  => 'admin.providers.categories.*',
            'count'  => \App\Models\ProviderCategory::count(),
        ],
    ];
@endphp

<div class="flex items-center gap-1 bg-green-900 border border-slate-800 rounded-xl p-1 mb-6">
    @foreach($providerNav as $item)
    <a href="{{ route($item['route']) }}"
       class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition
              {{ request()->routeIs($item['match'])
                  ? 'bg-orange-500 text-black shadow-sm'
                  : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
        <i class="{{ $item['icon'] }} text-xs"></i>
        <span>{{ $item['label'] }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold
            {{ request()->routeIs($item['match'])
                ? 'bg-green-950/20 text-black'
                : 'bg-slate-800 text-slate-500' }}">
            {{ $item['count'] }}
        </span>
    </a>
    @endforeach
</div>
