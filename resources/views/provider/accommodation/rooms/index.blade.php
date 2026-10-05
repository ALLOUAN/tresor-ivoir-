@extends('layouts.app')

@section('title', 'Chambres & tarifs')
@section('page-title', 'Chambres & tarifs')

@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('provider.accommodation.dashboard') }}"
           class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
        <a href="{{ route('provider.accommodation.rooms.create') }}"
           class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-circle-plus text-xs"></i> Ajouter une chambre
        </a>
    </div>
@endsection

@section('content')

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($rooms as $room)
    <div class="bg-green-900 border border-slate-800 hover:border-slate-700 rounded-xl overflow-hidden transition">
        <div class="aspect-video bg-slate-800 flex items-center justify-center">
            @if(!empty($room['photos'][0]))
                <img src="{{ $room['photos'][0] }}" class="w-full h-full object-cover" alt="{{ $room['name'] }}">
            @else
                <i class="fas fa-bed text-slate-700 text-2xl"></i>
            @endif
        </div>
        <div class="p-4">
            <p class="text-white font-semibold text-sm">{{ $room['name'] }}</p>
            <p class="text-slate-500 text-xs mt-1">
                <i class="fas fa-user text-[10px] mr-1"></i>{{ $room['max_adults'] ?? 2 }} adulte(s)
                @if(($room['max_children'] ?? 0) > 0) · {{ $room['max_children'] }} enfant(s) @endif
                @if(!empty($room['area_m2'])) · {{ $room['area_m2'] }} m² @endif
            </p>
            <p class="text-orange-400 font-semibold text-sm mt-2">
                @if(!empty($room['price_xof'])) {{ number_format((int) $room['price_xof'], 0, ',', ' ') }} XOF/nuit @else Prix non défini @endif
            </p>
            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-800">
                <a href="{{ route('provider.accommodation.rooms.edit', $room['id']) }}"
                   class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">Modifier</a>
                <form method="POST" action="{{ route('provider.accommodation.rooms.destroy', $room['id']) }}"
                      onsubmit="return confirm('Supprimer cette chambre ?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button class="w-full bg-red-900/60 hover:bg-red-800 text-red-200 text-xs font-semibold px-3 py-1.5 rounded-lg transition">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16 text-slate-500 bg-green-900 border border-slate-800 rounded-xl">
        <i class="fas fa-bed text-3xl mb-3 block text-slate-700"></i>
        Aucune chambre ajoutée pour le moment.
        <div class="mt-4">
            <a href="{{ route('provider.accommodation.rooms.create') }}" class="text-orange-400 hover:text-orange-300 font-medium">Ajouter votre première chambre</a>
        </div>
    </div>
    @endforelse
</div>

@endsection
