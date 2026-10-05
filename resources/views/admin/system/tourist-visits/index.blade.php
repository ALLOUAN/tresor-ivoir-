@extends('layouts.app')

@section('title', 'Visites touristiques')
@section('page-title', 'Visites de sites touristiques')

@section('header-actions')
    <a href="{{ route('admin.tourist-visits.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold px-3 py-2 rounded-lg shrink-0">
        <i class="fas fa-download"></i>
        Exporter (CSV)
    </a>
@endsection

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Total</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-900/40 to-green-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Confirmées</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($stats['paid']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-slate-800/60 to-green-900 border border-slate-600/30 rounded-xl p-4">
        <p class="text-slate-400 text-xs font-medium">Annulées</p>
        <p class="text-slate-300 text-2xl font-bold mt-1">{{ number_format($stats['cancelled']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Ce mois</p>
        <p class="text-green-200 text-2xl font-bold mt-1">{{ number_format($stats['this_month']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-900/40 to-green-900 border border-orange-500/30 rounded-xl p-4 col-span-2 sm:col-span-1">
        <p class="text-orange-300/80 text-xs font-medium">Commissions encaissées</p>
        <p class="text-orange-200 text-xl font-bold mt-1">{{ number_format((int) $stats['commissions_xof']) }} XOF</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20 mb-6">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.tourist-visits.index') }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === '' ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
            Toutes
        </a>
        @foreach(\App\Models\TouristVisit::STATUS_LABELS as $value => $label)
            <a href="{{ route('admin.tourist-visits.index', ['status' => $value]) }}"
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
                    <th class="text-left px-5 py-3">Site / Prestataire</th>
                    <th class="text-left px-5 py-3">Visiteur</th>
                    <th class="text-left px-5 py-3">Type</th>
                    <th class="text-left px-5 py-3">Total</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($visits as $visit)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ $visit->reference }}</td>
                        <td class="px-5 py-3 align-top max-w-[220px]">
                            <p class="text-white font-medium truncate">{{ $visit->experience_name }}</p>
                            <p class="text-slate-500 text-xs truncate">{{ $visit->provider?->name }}</p>
                        </td>
                        <td class="px-5 py-3 align-top">
                            <p class="text-slate-200">{{ $visit->full_name }}</p>
                            <p class="text-slate-500 text-xs">{{ $visit->email }}</p>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $visit->labelForType() }}</td>
                        <td class="px-5 py-3 align-top text-slate-200 whitespace-nowrap">
                            {{ $visit->amount_total_xof ? number_format((int) $visit->amount_total_xof, 0, ',', ' ').' XOF' : 'Gratuit' }}
                        </td>
                        <td class="px-5 py-3 align-top whitespace-nowrap">{{ $visit->labelForStatus() }}</td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('admin.tourist-visits.show', $visit) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-orange-500/15 text-orange-400 hover:bg-orange-500/25 border border-orange-500/30 transition"
                               title="Voir">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                            <i class="fas fa-calendar-xmark text-3xl mb-3 opacity-40 block"></i>
                            Aucune visite pour ces critères.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($visits->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $visits->links() }}
        </div>
    @endif
</div>

@endsection
