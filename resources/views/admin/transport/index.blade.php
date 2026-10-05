@extends('layouts.app')

@section('title', 'Transports & Mobilité')
@section('page-title', 'Supervision des offres de transport')

@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.transport.categories.index') }}"
           class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-tags text-xs"></i> Gérer les catégories
        </a>
        <a href="{{ route('admin.transport.create') }}"
           class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-circle-plus text-xs"></i> Nouvelle offre
        </a>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Toutes</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($counts['all']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Disponibles</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($counts['available']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Indisponibles</p>
        <p class="text-red-400 text-2xl font-bold mt-1">{{ number_format($counts['unavailable']) }}</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Liste des offres</h2>
        <form method="GET" action="{{ route('admin.transport.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher une offre..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="availability" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Toutes les disponibilités</option>
                <option value="available" @selected($availability === 'available')>Disponible</option>
                <option value="unavailable" @selected($availability === 'unavailable')>Indisponible</option>
            </select>
            <select name="category" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>{{ $cat->name_fr }}</option>
                @endforeach
            </select>
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.transport.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Offre</th>
                    <th class="text-left px-5 py-3">Prestataire</th>
                    <th class="text-left px-5 py-3">Catégorie</th>
                    <th class="text-left px-5 py-3">Prix</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($offers as $offer)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if(!empty($offer->images[0]))
                                    <img src="{{ $offer->images[0] }}" class="w-10 h-10 rounded-lg object-cover border border-slate-700" alt="">
                                @endif
                                <p class="text-white font-medium">{{ $offer->name }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-300">{{ $offer->provider?->name }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ $offer->category?->name_fr }}</td>
                        <td class="px-5 py-3 text-slate-200 whitespace-nowrap">{{ number_format((int) $offer->price_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-full text-xs {{ $offer->is_available ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300' }}">
                                {{ $offer->is_available ? 'Disponible' : 'Indisponible' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.transport.edit', $offer) }}"
                                   class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1.5 rounded">Modifier</a>
                                <form method="POST" action="{{ route('admin.transport.toggle', $offer) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="bg-orange-600 hover:bg-orange-500 text-white text-xs px-3 py-1.5 rounded">
                                        {{ $offer->is_available ? 'Rendre indisponible' : 'Rendre disponible' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.transport.destroy', $offer) }}"
                                      onsubmit="return confirm('Supprimer définitivement cette offre ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-700 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucune offre.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $offers->links() }}
</div>

@endsection
