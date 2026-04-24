@extends('layouts.app')

@section('title', 'Paiements')
@section('page-title', 'Gestion des paiements')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Total paiements</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">En attente</p>
        <p class="text-amber-400 text-2xl font-bold mt-1">{{ number_format($stats['pending']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Terminés</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($stats['completed']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Revenu encaissé</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['revenue'], 0, ',', ' ') }} FCFA</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Échoués</p>
        <p class="text-red-400 text-2xl font-bold mt-1">{{ number_format($stats['failed']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Remboursés</p>
        <p class="text-slate-300 text-2xl font-bold mt-1">{{ number_format($stats['refunded']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Taux de réussite</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($stats['success_rate'], 2, ',', ' ') }}%</p>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Paiements</h2>
        <form method="GET" action="{{ route('admin.payments.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Prestataire ou transaction..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les statuts</option>
                @foreach(['pending' => 'En attente', 'completed' => 'Terminé', 'failed' => 'Echoué', 'refunded' => 'Remboursé'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="method" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Toutes les méthodes</option>
                @foreach(['orange_money','mtn_momo','wave','moov_money','card','paypal'] as $value)
                    <option value="{{ $value }}" @selected($method === $value)>{{ str_replace('_', ' ', ucfirst($value)) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $from }}" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <input type="date" name="to" value="{{ $to }}" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <input type="number" step="0.01" name="min_amount" value="{{ $minAmount }}" placeholder="Montant min" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <input type="number" step="0.01" name="max_amount" value="{{ $maxAmount }}" placeholder="Montant max" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <div class="md:col-span-6 flex items-center gap-2">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.payments.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Prestataire</th>
                    <th class="text-left px-5 py-3">Abonnement</th>
                    <th class="text-left px-5 py-3">Montant</th>
                    <th class="text-left px-5 py-3">Méthode</th>
                    <th class="text-left px-5 py-3">Statut</th><th class="text-left px-5 py-3">Détail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($payments as $payment)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 text-slate-300">{{ optional($payment->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <p class="text-white">{{ $payment->provider->name ?? '—' }}</p>
                            <p class="text-slate-500 text-xs">{{ $payment->gateway_txn_id ?? 'Sans transaction ID' }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-300">{{ strtoupper($payment->subscription->plan->code ?? 'N/A') }}</td>
                        <td class="px-5 py-3 text-white font-semibold">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ str_replace('_', ' ', ucfirst($payment->method)) }}</td>
                        <td class="px-5 py-3">
                            @php
                                $statusClass = match($payment->status) {
                                    'completed' => 'bg-emerald-500/20 text-emerald-300',
                                    'pending' => 'bg-amber-500/20 text-amber-300',
                                    'failed' => 'bg-red-500/20 text-red-300',
                                    default => 'bg-slate-500/20 text-slate-300',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs {{ $statusClass }}">{{ $payment->status }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="text-cyan-400 hover:text-cyan-300 text-xs">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-500">Aucun paiement trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $payments->links() }}
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mt-6">
    <h2 class="text-white font-semibold text-sm mb-3">Répartition des revenus par type d'abonnement</h2>
    <div class="space-y-2">
        @forelse($stats['revenue_by_plan'] as $planCode => $total)
            <div class="flex items-center justify-between bg-slate-800 rounded-lg px-3 py-2">
                <span class="text-slate-300 uppercase">{{ $planCode }}</span>
                <span class="text-white font-semibold">{{ number_format((float) $total, 0, ',', ' ') }} FCFA</span>
            </div>
        @empty
            <p class="text-slate-500 text-sm">Aucune donnée de revenu pour les filtres courants.</p>
        @endforelse
    </div>
</div>
@endsection
