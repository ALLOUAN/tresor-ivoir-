@extends('layouts.app')

@section('title', 'Événements')
@section('page-title', 'Gestion des événements')
@section('header-actions')
    <a href="{{ route('editor.events.index') }}"
       class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-semibold px-3 py-2 rounded-lg border border-slate-700 transition">
        <i class="fas fa-list-check"></i>
        Modifier Evenements
    </a>
    <a href="{{ route('editor.events.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-semibold px-3 py-2 rounded-lg">
        <i class="fas fa-plus"></i>
        Créer un événement
    </a>
@endsection

@section('content')
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Tous</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($counts['all']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Brouillons</p>
        <p class="text-slate-300 text-2xl font-bold mt-1">{{ number_format($counts['draft']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Publiés</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($counts['published']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Annulés</p>
        <p class="text-red-400 text-2xl font-bold mt-1">{{ number_format($counts['cancelled']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Passés</p>
        <p class="text-amber-400 text-2xl font-bold mt-1">{{ number_format($counts['past']) }}</p>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Liste des événements</h2>
        <form method="GET" action="{{ route('admin.events.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un titre..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les statuts</option>
                @foreach(['draft' => 'Brouillon', 'published' => 'Publié', 'cancelled' => 'Annulé', 'past' => 'Passé'] as $value => $label)
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
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.events.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Titre</th>
                    <th class="text-left px-5 py-3">Catégorie</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($events as $event)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3">
                            <p class="text-white font-medium">{{ $event->title_fr }}</p>
                            <p class="text-slate-500 text-xs mt-0.5">{{ $event->city ?: 'Ville non précisée' }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-300">{{ $event->category->name_fr ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ optional($event->starts_at)->format('d/m/Y H:i') ?: '—' }}</td>
                        <td class="px-5 py-3">
                            @php
                                $statusClass = match($event->status) {
                                    'published' => 'bg-emerald-500/20 text-emerald-300',
                                    'cancelled' => 'bg-red-500/20 text-red-300',
                                    'past' => 'bg-amber-500/20 text-amber-300',
                                    default => 'bg-slate-500/20 text-slate-300',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs {{ $statusClass }}">{{ $event->status }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                @if($event->status !== 'published')
                                    <form method="POST" action="{{ route('admin.events.publish', $event) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs px-3 py-1.5 rounded">Publier</button>
                                    </form>
                                @endif
                                @if($event->status !== 'cancelled')
                                    <form method="POST" action="{{ route('admin.events.cancel', $event) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-orange-600 hover:bg-orange-500 text-white text-xs px-3 py-1.5 rounded">Annuler</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Supprimer cet événement ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-700 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucun événement trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $events->links() }}
    </div>
</div>
@endsection
