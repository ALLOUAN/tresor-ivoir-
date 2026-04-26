@extends('layouts.app')

@section('title', 'Mes événements')
@section('page-title', 'Événements')

@section('header-actions')
<a href="{{ route('editor.events.create') }}"
   class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvel événement
</a>
@endsection

@section('content')
<div class="flex items-center gap-1 overflow-x-auto mb-6 bg-slate-900/50 border border-slate-800 rounded-xl p-1">
    @php $tabs = [''=>'Tous','draft'=>'Brouillons','published'=>'Publiés','cancelled'=>'Annulés','past'=>'Passés']; @endphp
    @foreach($tabs as $val => $label)
    <a href="{{ route('editor.events.index', $val ? ['status' => $val] : []) }}"
       class="shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap
              {{ $status === $val || ($status === null && $val === '') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
        {{ $label }}
        <span class="bg-slate-600/50 text-slate-300 text-[10px] px-1.5 py-0.5 rounded-full">
            {{ $counts[$val ?: 'all'] }}
        </span>
    </a>
    @endforeach
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Événement</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Catégorie</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">Début</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Mis à jour</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($events as $event)
                <tr class="hover:bg-slate-800/30 transition group">
                    <td class="px-5 py-4">
                        <p class="text-white font-medium text-sm">{{ $event->title_fr }}</p>
                        <p class="text-slate-500 text-xs mt-0.5">{{ $event->city ?: 'Ville non précisée' }}</p>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <span class="text-amber-400/80 text-xs">{{ $event->category->name_fr ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell text-slate-400 text-xs">
                        {{ $event->starts_at?->format('d/m/Y H:i') ?: '—' }}
                    </td>
                    <td class="px-5 py-4">
                        @php $statusMap = [
                            'published' => ['bg-emerald-900/40 text-emerald-300 border-emerald-800', 'Publié'],
                            'draft' => ['bg-slate-800 text-slate-400 border-slate-700', 'Brouillon'],
                            'cancelled' => ['bg-rose-900/40 text-rose-300 border-rose-800', 'Annulé'],
                            'past' => ['bg-slate-800 text-slate-500 border-slate-700', 'Passé'],
                        ]; [$cls, $lbl] = $statusMap[$event->status] ?? ['bg-slate-800 text-slate-400', $event->status]; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium border {{ $cls }}">{{ $lbl }}</span>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell text-slate-500 text-xs">{{ $event->updated_at->diffForHumans() }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('editor.events.edit', $event) }}"
                               class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/50 flex items-center justify-center text-slate-400 hover:text-amber-300 transition" title="Modifier">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('editor.events.destroy', $event) }}"
                                  onsubmit="return confirm('Supprimer cet événement ?')" class="inline">
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
                        Aucun événement trouvé.
                        <a href="{{ route('editor.events.create') }}" class="text-amber-400 hover:underline ml-1">Créer le premier</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
