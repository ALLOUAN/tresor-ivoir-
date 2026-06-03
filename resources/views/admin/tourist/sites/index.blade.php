@extends('layouts.app')

@section('title', 'Sites Touristiques')
@section('page-title', 'Sites Touristiques')

@section('header-actions')
<a href="{{ route('admin.tourist.sites.create') }}"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouveau site
</a>
@endsection

@section('content')

@include('admin.tourist.partials.subnav')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['fas fa-map-pin',     'text-slate-300',   $counts['total'],    'Total'],
        ['fas fa-circle-check','text-emerald-400', $counts['active'],   'Actifs'],
        ['fas fa-star',        'text-amber-400',   $counts['featured'], 'En vedette'],
    ] as [$icon, $color, $val, $lbl])
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <i class="{{ $icon }} {{ $color }} text-sm mb-2 block"></i>
        <p class="text-2xl font-bold text-white">{{ $val }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $lbl }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-5">
    <form method="GET" action="{{ route('admin.tourist.sites.index') }}" class="flex flex-wrap gap-3 items-end">
        <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un site…"
            class="w-48 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition placeholder-slate-600">
        <select name="city"
            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none">
            <option value="">Toutes les villes</option>
            @foreach($cities as $c)
            <option value="{{ $c->id }}" {{ $cityId == $c->id ? 'selected':'' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="category"
            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none">
            <option value="">Toutes catégories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ $catId == $cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="active"
            class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none">
            <option value="">Tous statuts</option>
            <option value="1" {{ $active === '1' ? 'selected':'' }}>Actifs</option>
            <option value="0" {{ $active === '0' ? 'selected':'' }}>Inactifs</option>
        </select>
        <button class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs rounded-lg transition">
            <i class="fas fa-search"></i>
        </button>
        @if($search || $cityId || $catId || $active !== null)
        <a href="{{ route('admin.tourist.sites.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-300 text-xs transition">
            <i class="fas fa-xmark mr-1"></i>Réinitialiser
        </a>
        @endif
    </form>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Table --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Site</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Ville</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">Catégorie</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Vues</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($sites as $site)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($site->thumbnail)
                            <img src="{{ $site->thumbnail }}" class="w-12 h-10 rounded-lg object-cover shrink-0 hidden sm:block">
                            @else
                            <div class="w-12 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 hidden sm:block">
                                <i class="fas fa-image text-slate-600 text-sm"></i>
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-white font-medium text-sm line-clamp-1">{{ $site->name }}</p>
                                @if($site->is_featured)
                                <span class="text-amber-400 text-[10px]"><i class="fas fa-star mr-0.5"></i>En vedette</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell text-slate-400 text-xs">
                        {{ $site->city->name ?? '—' }}
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="text-amber-400/80 text-xs">{{ $site->category->name ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell text-slate-500 text-xs">
                        <i class="fas fa-eye mr-1"></i>{{ number_format($site->views_count) }}
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium border
                            {{ $site->is_active ? 'bg-emerald-900/40 text-emerald-300 border-emerald-800' : 'bg-slate-800 text-slate-500 border-slate-700' }}">
                            {{ $site->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Vedette --}}
                            <form method="POST" action="{{ route('admin.tourist.sites.featured', $site) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center transition"
                                    title="{{ $site->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}">
                                    <i class="fas fa-star text-xs {{ $site->is_featured ? 'text-amber-400' : 'text-slate-400' }}"></i>
                                </button>
                            </form>
                            {{-- Toggle actif --}}
                            <form method="POST" action="{{ route('admin.tourist.sites.toggle', $site) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 transition"
                                    title="{{ $site->is_active ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $site->is_active ? 'eye' : 'eye-slash' }} text-xs"></i>
                                </button>
                            </form>
                            {{-- Voir public --}}
                            <a href="{{ route('tourist.site', $site->slug) }}" target="_blank"
                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-blue-900/40 flex items-center justify-center text-slate-400 hover:text-blue-300 transition" title="Voir">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            {{-- Modifier --}}
                            <a href="{{ route('admin.tourist.sites.edit', $site) }}"
                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center text-slate-400 hover:text-amber-300 transition" title="Modifier">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            {{-- Supprimer --}}
                            <form method="POST" action="{{ route('admin.tourist.sites.destroy', $site) }}"
                                onsubmit="return confirm('Supprimer « {{ addslashes($site->name) }} » ?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center text-slate-500">
                        <i class="fas fa-map-pin text-3xl mb-3 block text-slate-700"></i>
                        Aucun site touristique trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sites->hasPages())
    <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
        <span>{{ $sites->firstItem() }}–{{ $sites->lastItem() }} sur {{ $sites->total() }}</span>
        <div class="flex gap-1">
            @if(!$sites->onFirstPage())
            <a href="{{ $sites->previousPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">← Préc.</a>
            @endif
            @if($sites->hasMorePages())
            <a href="{{ $sites->nextPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">Suiv. →</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
