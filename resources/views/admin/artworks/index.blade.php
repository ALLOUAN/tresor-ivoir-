@extends('layouts.app')

@section('title', 'Art & Créations')
@section('page-title', 'Modération des œuvres')

@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.artworks.categories.index') }}"
           class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-tags text-xs"></i> Gérer les catégories
        </a>
        <a href="{{ route('admin.artworks.create') }}"
           class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-circle-plus text-xs"></i> Nouvelle œuvre
        </a>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Toutes</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($counts['all']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">En attente</p>
        <p class="text-orange-400 text-2xl font-bold mt-1">{{ number_format($counts['pending_review']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Publiées</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($counts['published']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Suspendues</p>
        <p class="text-red-400 text-2xl font-bold mt-1">{{ number_format($counts['suspended']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Vendues</p>
        <p class="text-green-400 text-2xl font-bold mt-1">{{ number_format($counts['sold']) }}</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Liste des œuvres</h2>
        <form method="GET" action="{{ route('admin.artworks.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un titre..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les statuts</option>
                @foreach(\App\Models\Artwork::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="category" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>{{ $cat->name_fr }}</option>
                @endforeach
            </select>
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.artworks.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Œuvre</th>
                    <th class="text-left px-5 py-3">Artiste</th>
                    <th class="text-left px-5 py-3">Catégorie</th>
                    <th class="text-left px-5 py-3">Prix</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($artworks as $artwork)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if(!empty($artwork->images[0]))
                                    <img src="{{ $artwork->images[0] }}" class="w-10 h-10 rounded-lg object-cover border border-slate-700" alt="">
                                @endif
                                <p class="text-white font-medium">{{ $artwork->title }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-300">{{ $artwork->provider?->name }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ $artwork->category?->name_fr }}</td>
                        <td class="px-5 py-3 text-slate-200 whitespace-nowrap">{{ number_format((int) $artwork->price_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3">
                            @php
                                $statusClass = match($artwork->status) {
                                    \App\Models\Artwork::STATUS_PUBLISHED => 'bg-emerald-500/20 text-emerald-300',
                                    \App\Models\Artwork::STATUS_PENDING_REVIEW => 'bg-orange-500/20 text-orange-300',
                                    \App\Models\Artwork::STATUS_SUSPENDED, \App\Models\Artwork::STATUS_REJECTED => 'bg-red-500/20 text-red-300',
                                    \App\Models\Artwork::STATUS_SOLD => 'bg-slate-500/20 text-slate-300',
                                    default => 'bg-slate-500/20 text-slate-300',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs {{ $statusClass }}">{{ $artwork->labelForStatus() }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.artworks.edit', $artwork) }}"
                                   class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1.5 rounded">Modifier</a>
                                @if($artwork->status !== \App\Models\Artwork::STATUS_PUBLISHED)
                                    <form method="POST" action="{{ route('admin.artworks.approve', $artwork) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs px-3 py-1.5 rounded">Approuver</button>
                                    </form>
                                @endif
                                @if($artwork->status === \App\Models\Artwork::STATUS_PENDING_REVIEW)
                                    <form method="POST" action="{{ route('admin.artworks.reject', $artwork) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-slate-600 hover:bg-slate-500 text-white text-xs px-3 py-1.5 rounded">Refuser</button>
                                    </form>
                                @endif
                                @if($artwork->status !== \App\Models\Artwork::STATUS_SUSPENDED)
                                    <form method="POST" action="{{ route('admin.artworks.suspend', $artwork) }}"
                                          onsubmit="return confirm('Suspendre cette œuvre ?');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-orange-600 hover:bg-orange-500 text-white text-xs px-3 py-1.5 rounded">Suspendre</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.artworks.destroy', $artwork) }}"
                                      onsubmit="return confirm('Supprimer définitivement cette œuvre ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-700 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucune œuvre.</td>
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
