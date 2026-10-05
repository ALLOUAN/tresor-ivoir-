@extends('layouts.app')

@section('title', 'Mes circuits')
@section('page-title', 'Mes circuits')

@section('header-actions')
    <a href="{{ route('provider.tours.create') }}"
       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Ajouter un circuit
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Circuit</th>
                    <th class="text-left px-5 py-3">Catégorie</th>
                    <th class="text-left px-5 py-3">Durée</th>
                    <th class="text-left px-5 py-3">Prix</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($tours as $tour)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 align-top">
                            <div class="flex items-center gap-3">
                                @if(!empty($tour->images[0]))
                                    <img src="{{ $tour->images[0] }}" class="w-12 h-12 rounded-lg object-cover border border-slate-700" alt="">
                                @endif
                                <p class="text-white font-medium truncate">{{ $tour->name }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $tour->category?->name_fr }}</td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $tour->duration_days ? $tour->duration_days.' j' : '—' }}</td>
                        <td class="px-5 py-3 align-top text-slate-200 whitespace-nowrap">{{ number_format((int) $tour->price_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3 align-top">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if($tour->is_available) bg-emerald-500/20 text-emerald-300 border border-emerald-500/35
                                @else bg-slate-700/40 text-slate-300 border border-slate-600/40 @endif">
                                {{ $tour->is_available ? 'Disponible' : 'Indisponible' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('provider.tours.edit', $tour) }}" class="text-slate-400 hover:text-white text-xs mr-3">
                                <i class="fas fa-pen"></i> Modifier
                            </a>
                            <form method="POST" action="{{ route('provider.tours.destroy', $tour) }}" class="inline"
                                  onsubmit="return confirm('Retirer ce circuit ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                            Aucun circuit publié pour l'instant.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $tours->links() }}
</div>

@endsection
