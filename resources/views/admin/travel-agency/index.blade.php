@extends('layouts.app')

@section('title', 'Agences de Voyages — Admin')
@section('page-title', 'Agences de Voyages & Tours')

@section('header-actions')
<a href="{{ route('travel-agency.index') }}" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-eye"></i> Voir le site
</a>
<a href="{{ route('admin.travel-agencies.create') }}"
   class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvelle agence
</a>
@endsection

@section('content')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center shrink-0">
            <i class="fas fa-plane text-slate-300 text-sm"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-none">{{ $counts['total'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">Total</p>
        </div>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-900/40 flex items-center justify-center shrink-0">
            <i class="fas fa-circle-check text-emerald-400 text-sm"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-none">{{ $counts['active'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">Actives</p>
        </div>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-orange-900/40 flex items-center justify-center shrink-0">
            <i class="fas fa-star text-orange-400 text-sm"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-none">{{ $counts['featured'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">En vedette</p>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('admin.travel-agencies.index') }}" class="flex flex-wrap gap-2 mb-6 items-end">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher une agence…"
               class="w-full bg-green-900 border border-slate-800 focus:border-orange-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition placeholder-slate-600">
    </div>
    <select name="city_id" class="bg-green-900 border border-slate-800 focus:border-orange-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition">
        <option value="">Toutes les villes</option>
        @foreach($cities as $city)
            <option value="{{ $city->id }}" {{ $cityId == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition flex items-center gap-1.5">
        <i class="fas fa-search"></i> Filtrer
    </button>
    @if($search || $cityId)
        <a href="{{ route('admin.travel-agencies.index') }}" class="px-3 py-2 bg-slate-800/50 hover:bg-slate-800 text-slate-500 hover:text-slate-300 text-xs rounded-lg transition flex items-center gap-1.5">
            <i class="fas fa-xmark"></i> Réinitialiser
        </a>
    @endif
</form>

@if($agencies->isEmpty())
    <div class="bg-green-900 border border-slate-800 rounded-xl py-20 text-center">
        <i class="fas fa-plane text-4xl text-slate-700 mb-4 block"></i>
        <p class="text-slate-500 text-sm">Aucune agence trouvée.</p>
        <a href="{{ route('admin.travel-agencies.create') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold rounded-lg transition">
            <i class="fas fa-plus"></i> Créer la première
        </a>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($agencies as $agency)
        <div class="group bg-green-900 border border-slate-800 hover:border-slate-700 rounded-xl overflow-hidden transition flex flex-col">
            <div class="relative h-44 bg-slate-800 overflow-hidden shrink-0">
                @if($agency->cover_image || $agency->thumbnail)
                    <img src="{{ $agency->cover_image ?? $agency->thumbnail }}" alt="{{ $agency->name }}" loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-plane text-slate-700 text-3xl"></i>
                    </div>
                @endif
                <div class="absolute inset-0 bg-linear-to-t from-green-900/80 via-transparent to-transparent"></div>

                <div class="absolute top-2.5 right-2.5 flex flex-col items-end gap-1">
                    @if(!$agency->is_active)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-700/90 backdrop-blur-sm text-slate-400 border border-slate-600">Inactif</span>
                    @endif
                    @if($agency->is_featured)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-orange-500/80 backdrop-blur-sm text-black"><i class="fas fa-star text-[8px] mr-0.5"></i>Vedette</span>
                    @endif
                </div>

                @if($agency->media_count > 0)
                <div class="absolute bottom-2.5 right-2.5">
                    <span class="text-[11px] text-slate-300 bg-green-950/50 backdrop-blur-sm px-2 py-0.5 rounded-full border border-white/10">
                        <i class="fas fa-images text-[9px] mr-0.5"></i>{{ $agency->media_count }}
                    </span>
                </div>
                @endif
            </div>

            <div class="p-4 flex flex-col flex-1">
                <div class="flex items-center gap-1 text-slate-500 text-[11px] mb-1.5">
                    <i class="fas fa-location-dot text-orange-400/70"></i>
                    <span>{{ $agency->city?->name ?? '—' }}</span>
                    @if($agency->quartier)
                        <span class="text-slate-700">·</span>
                        <span>{{ $agency->quartier }}</span>
                    @endif
                </div>

                <h3 class="text-white font-semibold text-sm leading-tight mb-2 line-clamp-1">{{ $agency->name }}</h3>

                @if($agency->short_description)
                <p class="text-slate-500 text-xs leading-relaxed line-clamp-2 mb-3">{{ $agency->short_description }}</p>
                @endif

                <div class="flex-1"></div>

                <div class="flex items-center justify-between pt-3 mt-1 border-t border-slate-800">
                    <div class="flex items-center gap-1">
                        <form method="POST" action="{{ route('admin.travel-agencies.toggle-active', $agency) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition" title="{{ $agency->is_active ? 'Désactiver' : 'Activer' }}">
                                <i class="fas fa-{{ $agency->is_active ? 'eye' : 'eye-slash' }} text-xs {{ $agency->is_active ? 'text-emerald-400' : 'text-slate-500' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.travel-agencies.toggle-featured', $agency) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-orange-900/40 flex items-center justify-center transition" title="{{ $agency->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}">
                                <i class="fas fa-star text-xs {{ $agency->is_featured ? 'text-orange-400' : 'text-slate-500' }}"></i>
                            </button>
                        </form>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.travel-agencies.edit', $agency) }}" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-orange-900/40 flex items-center justify-center text-slate-400 hover:text-orange-300 transition" title="Modifier">
                            <i class="fas fa-pen text-xs"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.travel-agencies.destroy', $agency) }}" onsubmit="return confirm('Supprimer « {{ addslashes($agency->name) }} » ?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

@if($agencies->hasPages())
<div class="mt-5 flex items-center justify-between text-xs text-slate-500 bg-green-900 border border-slate-800 rounded-xl px-5 py-3">
    <span>{{ $agencies->firstItem() }}–{{ $agencies->lastItem() }} sur {{ $agencies->total() }}</span>
    <div class="flex gap-1">
        @if(!$agencies->onFirstPage())
            <a href="{{ $agencies->previousPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">← Préc.</a>
        @endif
        @if($agencies->hasMorePages())
            <a href="{{ $agencies->nextPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">Suiv. →</a>
        @endif
    </div>
</div>
@endif

@endsection
