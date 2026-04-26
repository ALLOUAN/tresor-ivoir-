@extends('layouts.app')

@section('title', 'Journal d\'audit')
@section('page-title', 'Journal d\'audit — actions admin')

@section('content')
@if($missingTable ?? false)
    <div class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-5 py-4 text-amber-100 text-sm">
        La table d\'audit n\'existe pas encore. Exécutez <code class="text-amber-200">php artisan migrate</code> pour activer l\'enregistrement des actions.
    </div>
@else
    <p class="text-slate-500 text-sm mb-6">
        Enregistrement des requêtes <strong>POST, PUT, PATCH, DELETE</strong> sous <code class="text-slate-400">/admin</code> (sans contenu sensible : seulement les noms de champs soumis).
    </p>

    <form method="get" action="{{ route('admin.audit.index') }}" class="flex flex-wrap items-end gap-3 mb-6">
        <div>
            <label for="method" class="block text-xs text-slate-400 mb-1">Méthode</label>
            <select name="method" id="method" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 min-w-[120px]">
                <option value="">Toutes</option>
                @foreach(['POST','PUT','PATCH','DELETE'] as $m)
                    <option value="{{ $m }}" @selected(request('method') === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="user_id" class="block text-xs text-slate-400 mb-1">ID utilisateur</label>
            <input type="number" name="user_id" id="user_id" value="{{ request('user_id') }}" placeholder="ex. 1"
                   class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 w-28">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label for="route" class="block text-xs text-slate-400 mb-1">Route (contient)</label>
            <input type="text" name="route" id="route" value="{{ request('route') }}" placeholder="ex. articles.publish"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>
        <button type="submit" class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-search text-xs"></i> Filtrer
        </button>
        <a href="{{ route('admin.audit.index') }}" class="text-slate-400 hover:text-slate-200 text-sm py-2">Réinitialiser</a>
    </form>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-slate-800 bg-slate-800/40">
                    <tr>
                        <th class="text-left px-4 py-3">Date</th>
                        <th class="text-left px-4 py-3">Utilisateur</th>
                        <th class="text-left px-4 py-3">Méthode</th>
                        <th class="text-left px-4 py-3 hidden lg:table-cell">Route</th>
                        <th class="text-left px-4 py-3">Statut</th>
                        <th class="text-left px-4 py-3 hidden xl:table-cell">Champs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-4 py-2 text-slate-400 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2 text-slate-200">
                                @if($log->user)
                                    <span class="text-white">{{ $log->user->first_name }} {{ $log->user->last_name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $log->user->email }}</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2"><span class="font-mono text-xs text-amber-300/90">{{ $log->method }}</span></td>
                            <td class="px-4 py-2 text-slate-400 text-xs hidden lg:table-cell font-mono">{{ $log->route_name ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-emerald-900/40 text-emerald-300' : ($log->status_code >= 400 ? 'bg-rose-900/40 text-rose-300' : 'bg-slate-700 text-slate-300') }}">
                                    {{ $log->status_code ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-slate-500 text-xs hidden xl:table-cell max-w-xs truncate" title="{{ implode(', ', $log->meta['input_keys'] ?? []) }}">
                                {{ implode(', ', array_slice($log->meta['input_keys'] ?? [], 0, 12)) }}{{ count($log->meta['input_keys'] ?? []) > 12 ? '…' : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">Aucune entrée pour le moment. Effectuez une action admin (enregistrement, publication, etc.) pour voir des lignes apparaître.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-800">{{ $logs->links() }}</div>
        @endif
    </div>
@endif
@endsection
