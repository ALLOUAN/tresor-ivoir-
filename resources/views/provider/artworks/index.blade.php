@extends('layouts.app')

@section('title', 'Mes œuvres')
@section('page-title', 'Mes œuvres')

@section('header-actions')
    <a href="{{ route('provider.artworks.create') }}"
       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Publier une œuvre
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
                    <th class="text-left px-5 py-3">Œuvre</th>
                    <th class="text-left px-5 py-3">Catégorie</th>
                    <th class="text-left px-5 py-3">Prix</th>
                    <th class="text-left px-5 py-3">Stock</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($artworks as $artwork)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 align-top">
                            <div class="flex items-center gap-3">
                                @if(!empty($artwork->images[0]))
                                    <img src="{{ $artwork->images[0] }}" class="w-12 h-12 rounded-lg object-cover border border-slate-700" alt="">
                                @endif
                                <p class="text-white font-medium truncate">{{ $artwork->title }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $artwork->category?->name_fr }}</td>
                        <td class="px-5 py-3 align-top text-slate-200 whitespace-nowrap">{{ number_format((int) $artwork->price_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $artwork->stock_quantity }}</td>
                        <td class="px-5 py-3 align-top">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if($artwork->status === \App\Models\Artwork::STATUS_PUBLISHED) bg-emerald-500/20 text-emerald-300 border border-emerald-500/35
                                @elseif($artwork->status === \App\Models\Artwork::STATUS_SOLD) bg-orange-500/20 text-orange-300 border border-orange-500/35
                                @else bg-slate-700/40 text-slate-300 border border-slate-600/40 @endif">
                                {{ $artwork->labelForStatus() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('provider.artworks.edit', $artwork) }}" class="text-slate-400 hover:text-white text-xs mr-3">
                                <i class="fas fa-pen"></i> Modifier
                            </a>
                            <form method="POST" action="{{ route('provider.artworks.destroy', $artwork) }}" class="inline"
                                  onsubmit="return confirm('Retirer cette œuvre ?');">
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
                            Aucune œuvre publiée pour l'instant.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $artworks->links() }}
</div>

@endsection
