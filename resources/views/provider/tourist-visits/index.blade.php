@extends('layouts.app')

@section('title', 'Visites reçues')
@section('page-title', 'Visites reçues')

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

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Total</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-amber-900/40 to-green-900 border border-amber-500/30 rounded-xl p-4">
        <p class="text-amber-300/80 text-xs font-medium">En attente</p>
        <p class="text-amber-200 text-2xl font-bold mt-1">{{ number_format($stats['pending_payment']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-900/40 to-green-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Confirmées</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($stats['paid']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-slate-800/60 to-green-900 border border-slate-600/30 rounded-xl p-4">
        <p class="text-slate-400 text-xs font-medium">Annulées</p>
        <p class="text-slate-300 text-2xl font-bold mt-1">{{ number_format($stats['cancelled']) }}</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20 mb-6">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-center gap-2">
        <a href="{{ route('provider.tourist-visits.index') }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === '' ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
            Toutes
        </a>
        @foreach(\App\Models\TouristVisit::STATUS_LABELS as $value => $label)
            <a href="{{ route('provider.tourist-visits.index', ['status' => $value]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === $value ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Référence</th>
                    <th class="text-left px-5 py-3">Visiteur</th>
                    <th class="text-left px-5 py-3">Type</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Participants</th>
                    <th class="text-left px-5 py-3">Total</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($visits as $visit)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ $visit->reference }}</td>
                        <td class="px-5 py-3 align-top">
                            <p class="text-slate-200">{{ $visit->full_name }}</p>
                            <p class="text-slate-500 text-xs">{{ $visit->email }}</p>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $visit->labelForType() }}</td>
                        <td class="px-5 py-3 align-top text-slate-400 text-xs whitespace-nowrap">
                            {{ optional($visit->session_date ?? $visit->desired_date)->format('d/m/Y') ?? '—' }}
                            @if($visit->session_time_label) <br>{{ $visit->session_time_label }} @endif
                        </td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $visit->participants_count }}</td>
                        <td class="px-5 py-3 align-top text-slate-200 whitespace-nowrap">
                            {{ $visit->amount_total_xof ? number_format((int) $visit->amount_total_xof, 0, ',', ' ').' XOF' : 'Gratuit' }}
                        </td>
                        <td class="px-5 py-3 align-top whitespace-nowrap">{{ $visit->labelForStatus() }}</td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('provider.tourist-visits.show', $visit) }}" class="text-slate-400 hover:text-white text-xs">
                                Détail <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-500">Aucune visite pour l'instant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $visits->links() }}
</div>

@endsection
