@extends('layouts.app')

@section('title', 'Portefeuilles')
@section('page-title', 'Portefeuilles & commissions')

@section('header-actions')
    <a href="{{ route('admin.wallet.payouts') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-money-bill-transfer text-xs"></i>
        Demandes de retrait
        @if($stats['payouts_pending_count'] > 0)
            <span class="bg-orange-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $stats['payouts_pending_count'] }}</span>
        @endif
    </a>
@endsection

@section('content')
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-7 gap-4 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Solde plateforme</p>
        <p class="text-white text-xl font-bold mt-1">{{ number_format($stats['platform_balance_xof'], 0, ',', ' ') }} XOF</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Soldes clients (en dépôt)</p>
        <p class="text-blue-300 text-xl font-bold mt-1">{{ number_format($stats['client_balances_xof'], 0, ',', ' ') }} XOF</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Revenu commissions (cumulé)</p>
        <p class="text-orange-300 text-xl font-bold mt-1">{{ number_format($stats['commission_revenue_xof'], 0, ',', ' ') }} XOF</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Dû aux prestataires (en attente)</p>
        <p class="text-amber-300 text-xl font-bold mt-1">{{ number_format($stats['provider_pending_xof'], 0, ',', ' ') }} XOF</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Disponible pour retrait</p>
        <p class="text-emerald-300 text-xl font-bold mt-1">{{ number_format($stats['provider_available_xof'], 0, ',', ' ') }} XOF</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Retraits en attente</p>
        <p class="text-white text-xl font-bold mt-1">{{ number_format($stats['payouts_pending_count']) }}</p>
    </div>
    <div class="bg-green-900 border border-rose-500/25 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Créances en cours</p>
        <p class="text-rose-300 text-xl font-bold mt-1">{{ number_format($stats['debts_outstanding_xof'], 0, ',', ' ') }} XOF</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-white font-semibold">Transactions</h2>
            <a href="{{ route('admin.wallet.export', request()->query()) }}" class="inline-flex items-center gap-2 text-slate-300 hover:text-white text-xs border border-slate-700 rounded-lg px-3 py-1.5">
                <i class="fas fa-file-csv"></i> Exporter CSV
            </a>
        </div>
        <form method="GET" action="{{ route('admin.wallet.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="provider_id" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les prestataires</option>
                @foreach($providers as $p)
                    <option value="{{ $p->id }}" @selected((string) $providerId === (string) $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="type" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les types</option>
                @foreach(\App\Models\WalletTransaction::TYPE_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $from }}" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <input type="date" name="to" value="{{ $to }}" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.wallet.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Type</th>
                    <th class="text-left px-5 py-3">Portefeuille</th>
                    <th class="text-left px-5 py-3">Réservation</th>
                    <th class="text-right px-5 py-3">Montant</th>
                    <th class="text-right px-5 py-3">Solde après</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($transactions as $t)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 text-slate-300">{{ $t->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3 text-slate-200">{{ $t->labelForType() }}</td>
                        <td class="px-5 py-3">
                            @if($t->wallet?->provider_id)
                                <a href="{{ route('admin.wallet.provider-show', $t->wallet->provider_id) }}" class="text-green-400 hover:text-green-300">{{ $t->wallet->provider?->name }}</a>
                            @else
                                <span class="text-slate-400">Plateforme</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $t->reservation?->reference ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $t->amount_xof >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                            {{ $t->amount_xof >= 0 ? '+' : '' }}{{ number_format($t->amount_xof, 0, ',', ' ') }} XOF
                        </td>
                        <td class="px-5 py-3 text-right text-slate-300">{{ number_format($t->balance_after_xof, 0, ',', ' ') }} XOF</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-500">Aucune transaction pour ces filtres.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
