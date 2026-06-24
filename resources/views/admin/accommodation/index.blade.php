@extends('layouts.app')

@section('title', 'Hébergements — Admin')
@section('page-title', 'Hébergements Touristiques')

@section('header-actions')
<a href="{{ route('admin.accommodations.index') }}" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
    <i class="fas fa-eye"></i> Voir le site
</a>
<a href="{{ route('admin.accommodations.create') }}"
   class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvel hébergement
</a>
@endsection

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif

{{-- Stats ─────────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center shrink-0">
            <i class="fas fa-hotel text-slate-300 text-sm"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-none">{{ $counts['total'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">Total</p>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-900/40 flex items-center justify-center shrink-0">
            <i class="fas fa-circle-check text-emerald-400 text-sm"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-none">{{ $counts['active'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">Actifs</p>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-900/40 flex items-center justify-center shrink-0">
            <i class="fas fa-star text-amber-400 text-sm"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-none">{{ $counts['featured'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">En vedette</p>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center shrink-0">
            <i class="fas fa-bed text-slate-400 text-sm"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-none">{{ $counts['total'] - $counts['active'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">Inactifs</p>
        </div>
    </div>
</div>

{{-- Filtres ──────────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.accommodations.index') }}"
      class="flex flex-wrap gap-2 mb-6 items-end">

    <div class="flex-1 min-w-[200px]">
        <input type="text" name="q" value="{{ $search }}"
               placeholder="Rechercher un hébergement…"
               class="w-full bg-slate-900 border border-slate-800 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition placeholder-slate-600">
    </div>

    <select name="city_id"
            class="bg-slate-900 border border-slate-800 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition">
        <option value="">Toutes les villes</option>
        @foreach($cities as $city)
            <option value="{{ $city->id }}" {{ $cityId == $city->id ? 'selected' : '' }}>
                {{ $city->name }}
            </option>
        @endforeach
    </select>

    <select name="type"
            class="bg-slate-900 border border-slate-800 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition">
        <option value="">Tous les types</option>
        @foreach(['hotel'=>'Hôtel','resort'=>'Resort','guesthouse'=>"Maison d'hôtes",'hostel'=>'Auberge de jeunesse','auberge'=>'Auberge','villa'=>'Villa','eco_lodge'=>'Éco-lodge'] as $val => $lbl)
            <option value="{{ $val }}" {{ $type === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
    </select>

    <button type="submit"
            class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition flex items-center gap-1.5">
        <i class="fas fa-search"></i> Filtrer
    </button>

    @if($search || $cityId || $type)
        <a href="{{ route('admin.accommodations.index') }}"
           class="px-3 py-2 bg-slate-800/50 hover:bg-slate-800 text-slate-500 hover:text-slate-300 text-xs rounded-lg transition flex items-center gap-1.5">
            <i class="fas fa-xmark"></i> Réinitialiser
        </a>
    @endif
</form>

{{-- Type pills (raccourcis rapides) --}}
<div class="flex flex-wrap gap-2 mb-5">
    @php
        $typeCounts = [
            'hotel' => \App\Models\Accommodation::where('type','hotel')->count(),
            'resort' => \App\Models\Accommodation::where('type','resort')->count(),
            'villa' => \App\Models\Accommodation::where('type','villa')->count(),
            'eco_lodge' => \App\Models\Accommodation::where('type','eco_lodge')->count(),
            'guesthouse' => \App\Models\Accommodation::where('type','guesthouse')->count(),
        ];
        $typeIcons = ['hotel'=>'fa-building','resort'=>'fa-umbrella-beach','villa'=>'fa-house','eco_lodge'=>'fa-leaf','guesthouse'=>'fa-house-chimney'];
    @endphp
    @foreach($typeCounts as $tval => $tcount)
        @if($tcount > 0)
        <a href="{{ route('admin.accommodations.index', ['type'=>$tval]) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition
                  {{ $type === $tval
                     ? 'bg-amber-500/20 border-amber-500/40 text-amber-300'
                     : 'bg-slate-900 border-slate-700 text-slate-400 hover:border-amber-500/30 hover:text-slate-300' }}">
            <i class="fas {{ $typeIcons[$tval] ?? 'fa-hotel' }} text-[10px]"></i>
            {{ ['hotel'=>'Hôtels','resort'=>'Resorts','villa'=>'Villas','eco_lodge'=>'Éco-lodges','guesthouse'=>"Maisons d'hôtes"][$tval] }}
            <span class="ml-0.5 opacity-60">{{ $tcount }}</span>
        </a>
        @endif
    @endforeach
</div>

{{-- Grille de cartes ──────────────────────────────────────────────────────── --}}
@if($accommodations->isEmpty())
    <div class="bg-slate-900 border border-slate-800 rounded-xl py-20 text-center">
        <i class="fas fa-hotel text-4xl text-slate-700 mb-4 block"></i>
        <p class="text-slate-500 text-sm">Aucun hébergement trouvé.</p>
        <a href="{{ route('admin.accommodations.create') }}"
           class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black text-xs font-semibold rounded-lg transition">
            <i class="fas fa-plus"></i> Créer le premier
        </a>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($accommodations as $acc)
        <div class="group bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-xl overflow-hidden transition flex flex-col">

            {{-- Image --}}
            <div class="relative h-44 bg-slate-800 overflow-hidden shrink-0">
                @if($acc->cover_image || $acc->thumbnail)
                    <img src="{{ $acc->cover_image ?? $acc->thumbnail }}"
                         alt="{{ $acc->name }}"
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-hotel text-slate-700 text-3xl"></i>
                    </div>
                @endif

                {{-- Overlay gradient --}}
                <div class="absolute inset-0 bg-linear-to-t from-slate-900/80 via-transparent to-transparent"></div>

                {{-- Badges top --}}
                <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                 bg-black/50 backdrop-blur-sm border border-white/10 text-white">
                        {{ $acc->type_label }}
                    </span>
                    @if($acc->stars > 0)
                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                 bg-amber-500/80 backdrop-blur-sm text-black">
                        @for($s=0;$s<$acc->stars;$s++)<i class="fas fa-star text-[8px]"></i>@endfor
                    </span>
                    @endif
                </div>

                {{-- Status badges top-right --}}
                <div class="absolute top-2.5 right-2.5 flex flex-col items-end gap-1">
                    @if(!$acc->is_active)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-700/90 backdrop-blur-sm text-slate-400 border border-slate-600">
                        Inactif
                    </span>
                    @endif
                    @if($acc->is_featured)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/80 backdrop-blur-sm text-black">
                        <i class="fas fa-star text-[8px] mr-0.5"></i>Vedette
                    </span>
                    @endif
                </div>

                {{-- Prix bottom-left --}}
                @if($acc->starting_price_xof)
                <div class="absolute bottom-2.5 left-2.5">
                    <span class="text-[11px] font-semibold text-white bg-black/50 backdrop-blur-sm px-2 py-0.5 rounded-full border border-white/10">
                        À partir de {{ number_format($acc->starting_price_xof, 0, ',', ' ') }} XOF
                    </span>
                </div>
                @endif

                {{-- Nb médias bottom-right --}}
                @if($acc->media_count > 0)
                <div class="absolute bottom-2.5 right-2.5">
                    <span class="text-[11px] text-slate-300 bg-black/50 backdrop-blur-sm px-2 py-0.5 rounded-full border border-white/10">
                        <i class="fas fa-images text-[9px] mr-0.5"></i>{{ $acc->media_count }}
                    </span>
                </div>
                @endif
            </div>

            {{-- Contenu --}}
            <div class="p-4 flex flex-col flex-1">

                {{-- Ville --}}
                <div class="flex items-center gap-1 text-slate-500 text-[11px] mb-1.5">
                    <i class="fas fa-location-dot text-amber-400/70"></i>
                    <span>{{ $acc->city?->name ?? '—' }}</span>
                    @if($acc->quartier)
                        <span class="text-slate-700">·</span>
                        <span>{{ $acc->quartier }}</span>
                    @endif
                </div>

                {{-- Nom --}}
                <h3 class="text-white font-semibold text-sm leading-tight mb-2 line-clamp-1">
                    {{ $acc->name }}
                </h3>

                {{-- Description courte --}}
                @if($acc->short_description)
                <p class="text-slate-500 text-xs leading-relaxed line-clamp-2 mb-3">
                    {{ $acc->short_description }}
                </p>
                @endif

                {{-- Catégories --}}
                @php $cats = $acc->categories; @endphp
                @if($cats->isNotEmpty())
                <div class="flex flex-wrap gap-1 mb-3">
                    @foreach($cats->take(3) as $cat)
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px]
                                 bg-slate-800 text-slate-400 border border-slate-700">
                        @if($cat->icon)<i class="{{ $cat->icon }} text-[8px]"
                            @if($cat->color) style="color:{{ $cat->color }}"@endif></i>@endif
                        {{ $cat->name }}
                    </span>
                    @endforeach
                    @if($cats->count() > 3)
                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-800 text-slate-600 border border-slate-700">
                        +{{ $cats->count() - 3 }}
                    </span>
                    @endif
                </div>
                @endif

                {{-- Espaceur --}}
                <div class="flex-1"></div>

                {{-- Footer actions --}}
                <div class="flex items-center justify-between pt-3 mt-1 border-t border-slate-800">
                    {{-- Toggles --}}
                    <div class="flex items-center gap-1">
                        {{-- Actif --}}
                        <form method="POST" action="{{ route('admin.accommodations.toggle-active', $acc) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition"
                                    title="{{ $acc->is_active ? 'Désactiver' : 'Activer' }}">
                                <i class="fas fa-{{ $acc->is_active ? 'eye' : 'eye-slash' }} text-xs {{ $acc->is_active ? 'text-emerald-400' : 'text-slate-500' }}"></i>
                            </button>
                        </form>
                        {{-- Vedette --}}
                        <form method="POST" action="{{ route('admin.accommodations.toggle-featured', $acc) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center transition"
                                    title="{{ $acc->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}">
                                <i class="fas fa-star text-xs {{ $acc->is_featured ? 'text-amber-400' : 'text-slate-500' }}"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Édition --}}
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.accommodations.edit', $acc) }}"
                           class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center text-slate-400 hover:text-amber-300 transition"
                           title="Modifier">
                            <i class="fas fa-pen text-xs"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.accommodations.destroy', $acc) }}"
                              onsubmit="return confirm('Supprimer « {{ addslashes($acc->name) }} » ?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition"
                                    title="Supprimer">
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

{{-- Pagination ──────────────────────────────────────────────────────────── --}}
@if($accommodations->hasPages())
<div class="mt-5 flex items-center justify-between text-xs text-slate-500
            bg-slate-900 border border-slate-800 rounded-xl px-5 py-3">
    <span>{{ $accommodations->firstItem() }}–{{ $accommodations->lastItem() }} sur {{ $accommodations->total() }}</span>
    <div class="flex gap-1">
        @if(!$accommodations->onFirstPage())
            <a href="{{ $accommodations->previousPageUrl() }}"
               class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">← Préc.</a>
        @endif
        @if($accommodations->hasMorePages())
            <a href="{{ $accommodations->nextPageUrl() }}"
               class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">Suiv. →</a>
        @endif
    </div>
</div>
@endif

@endsection
