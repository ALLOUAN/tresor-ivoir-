@extends('layouts.app')

@section('title', 'Portefeuille client')
@section('page-title', 'Portefeuille — '.$user->full_name)

@section('header-actions')
    <a href="{{ route('admin.wallet.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i>
        Retour aux portefeuilles
    </a>
@endsection

@section('content')
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Solde disponible</p>
        <p class="text-emerald-300 text-xl font-bold mt-1">{{ number_format($wallet->balance_available_xof, 0, ',', ' ') }} XOF</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Contact</p>
        <p class="text-white text-sm font-medium mt-1">{{ $user->email }}</p>
        @if($user->phone)
            <p class="text-slate-500 text-xs">{{ $user->phone }}</p>
        @endif
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Coordonnées de retrait</p>
        <p class="text-white text-sm font-medium mt-1">
            {{ $user->payout_method ? \App\Models\PayoutRequest::METHOD_LABELS[$user->payout_method] ?? $user->payout_method : 'Non renseignées' }}
        </p>
        @if($user->payout_account_number)
            <p class="text-slate-500 text-xs">{{ $user->payout_account_number }}</p>
        @endif
    </div>
</div>

@if($payoutRequests->isNotEmpty())
<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Demandes de retrait</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Montant</th>
                    <th class="text-left px-5 py-3">Méthode</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Référence</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @foreach($payoutRequests as $req)
                <tr>
                    <td class="px-5 py-3 text-slate-300">{{ $req->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3 text-white font-semibold">{{ number_format($req->amount_xof, 0, ',', ' ') }} XOF</td>
                    <td class="px-5 py-3 text-slate-300">{{ $req->labelForMethod() }}</td>
                    <td class="px-5 py-3">
                        @php
                            $pClass = match($req->status) {
                                'paid' => 'bg-emerald-500/20 text-emerald-300',
                                'approved' => 'bg-slate-500/20 text-slate-300',
                                'rejected' => 'bg-rose-500/20 text-rose-300',
                                default => 'bg-orange-500/20 text-orange-300',
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs {{ $pClass }}">{{ $req->labelForStatus() }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs">{{ $req->payment_reference ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Historique des transactions</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Type</th>
                    <th class="text-left px-5 py-3">Réservation</th>
                    <th class="text-right px-5 py-3">Montant</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($transactions as $t)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 text-slate-300">{{ $t->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3 text-slate-200">{{ $t->labelForType() }}</td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $t->reservation?->reference ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $t->amount_xof >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                            {{ $t->amount_xof >= 0 ? '+' : '' }}{{ number_format($t->amount_xof, 0, ',', ' ') }} XOF
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucune transaction.</td>
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
