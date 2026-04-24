@extends('layouts.app')

@section('title', 'Factures')
@section('page-title', 'Mes factures')

@section('content')
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Historique des factures</h2>
        <p class="text-slate-400 text-sm mt-1">Retrouvez ici toutes vos factures d'abonnement.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">N° Facture</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Montant TTC</th>
                    <th class="text-left px-5 py-3">Transaction</th>
                    <th class="text-left px-5 py-3">PDF</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 text-white font-medium">{{ $invoice->number }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ optional($invoice->issued_at)->format('d/m/Y H:i') ?: '—' }}</td>
                        <td class="px-5 py-3 text-slate-200">{{ number_format((float) $invoice->amount_ttc, 0, ',', ' ') }} FCFA</td>
                        <td class="px-5 py-3 text-slate-400">{{ $invoice->payment->gateway_txn_id ?? $invoice->payment->uuid ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($invoice->pdf_url)
                                <a href="{{ $invoice->pdf_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1.5 rounded">
                                    <i class="fas fa-file-pdf"></i> Ouvrir
                                </a>
                            @else
                                <span class="text-slate-500 text-xs">Indisponible</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucune facture disponible pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
