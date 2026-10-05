@extends('layouts.app')

@section('title', 'Demandes de retrait')
@section('page-title', 'Demandes de retrait')

@section('header-actions')
    <a href="{{ route('admin.wallet.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i>
        Retour aux portefeuilles
    </a>
@endsection

@section('content')
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ session('error') }}</div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <form method="GET" action="{{ route('admin.wallet.payouts') }}" class="flex flex-wrap items-center gap-3">
            <select name="holder" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Prestataires + Clients</option>
                <option value="provider" @selected($holder === 'provider')>Prestataires uniquement</option>
                <option value="client" @selected($holder === 'client')>Clients uniquement</option>
            </select>
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les statuts</option>
                @foreach(\App\Models\PayoutRequest::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="risk_level" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous niveaux de risque</option>
                <option value="low" @selected($riskLevel === 'low')>Risque faible</option>
                <option value="medium" @selected($riskLevel === 'medium')>Risque moyen</option>
                <option value="high" @selected($riskLevel === 'high')>Risque élevé</option>
            </select>
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
        </form>
    </div>

    <div class="divide-y divide-slate-800">
        @forelse($payoutRequests as $req)
        <div class="px-5 py-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    @if($req->isForClient())
                        <a href="{{ route('admin.wallet.user-show', $req->user_id) }}" class="text-white font-semibold hover:text-orange-300">{{ $req->user?->full_name }}</a>
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-500/20 text-slate-300">Client</span>
                    @else
                        <a href="{{ route('admin.wallet.provider-show', $req->provider_id) }}" class="text-white font-semibold hover:text-orange-300">{{ $req->provider?->name }}</a>
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-500/20 text-orange-300">Prestataire</span>
                    @endif
                    <p class="text-slate-500 text-xs mt-0.5">{{ $req->created_at?->format('d/m/Y H:i') }} · {{ $req->labelForMethod() }} · {{ $req->payout_destination }}</p>
                    @if($req->admin_note)
                        <p class="text-slate-400 text-xs mt-1"><i class="fas fa-note-sticky mr-1"></i>{{ $req->admin_note }}</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-white text-lg font-bold">{{ number_format($req->amount_xof, 0, ',', ' ') }} XOF</p>
                    @php
                        $pClass = match($req->status) {
                            'paid' => 'bg-emerald-500/20 text-emerald-300',
                            'approved' => 'bg-slate-500/20 text-slate-300',
                            'rejected', 'suspended' => 'bg-rose-500/20 text-rose-300',
                            default => 'bg-orange-500/20 text-orange-300',
                        };
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs {{ $pClass }}">{{ $req->labelForStatus() }}</span>
                </div>
            </div>

            @if($req->risk_score !== null)
            <div class="mt-3">
                @php
                    $riskClass = match($req->risk_level) {
                        'high' => 'bg-rose-500/20 text-rose-300',
                        'medium' => 'bg-orange-500/20 text-orange-300',
                        default => 'bg-emerald-500/20 text-emerald-300',
                    };
                    $riskLabel = ['low' => 'Risque faible', 'medium' => 'Risque moyen', 'high' => 'Risque élevé'][$req->risk_level] ?? $req->risk_level;
                @endphp
                <button type="button"
                        onclick="document.getElementById('payout-detail-{{ $req->id }}').classList.toggle('hidden')"
                        class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs {{ $riskClass }} hover:opacity-80 transition">
                    <i class="fas fa-gauge-high"></i> {{ $riskLabel }} ({{ $req->risk_score }}/100)
                    @if($req->decision)
                        · {{ $req->labelForDecision() }}
                    @endif
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </button>

                <div id="payout-detail-{{ $req->id }}" class="hidden mt-3 rounded-lg border border-slate-700 bg-slate-800/40 p-3 space-y-3">
                    @if(!empty($req->risk_flags))
                        <div>
                            <p class="text-slate-400 text-[11px] uppercase tracking-wide mb-1.5">Signaux détectés</p>
                            <ul class="space-y-1">
                                @foreach($req->risk_flags as $flag)
                                    <li class="text-xs text-slate-300 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400 shrink-0"></span>
                                        {{ $flag['message'] ?? $flag['code'] }}
                                        <span class="text-slate-600">(+{{ $flag['weight'] ?? 0 }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($req->auditLogs->isNotEmpty())
                        <div>
                            <p class="text-slate-400 text-[11px] uppercase tracking-wide mb-1.5">Journal d'audit</p>
                            <ul class="space-y-1">
                                @foreach($req->auditLogs as $log)
                                    <li class="text-xs text-slate-400">
                                        <span class="text-slate-600">{{ $log->created_at?->format('d/m/Y H:i:s') }}</span>
                                        — {{ $log->event_type }}
                                        <span class="text-slate-600">({{ ['system' => 'système', 'user' => 'utilisateur', 'admin' => 'admin'][$log->actor_type] ?? $log->actor_type }}{{ $log->actor ? ' · '.$log->actor->full_name : '' }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            @if($req->status === \App\Models\PayoutRequest::STATUS_PENDING || $req->status === \App\Models\PayoutRequest::STATUS_SUSPENDED)
            <div class="flex items-center gap-2 mt-3">
                <form method="POST" action="{{ route('admin.wallet.payouts.approve', $req) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-3 py-2 rounded-lg">
                        <i class="fas fa-check"></i> Approuver
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.wallet.payouts.reject', $req) }}" onsubmit="return confirm('Refuser cette demande de retrait ?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-rose-600/80 hover:bg-rose-500 text-white text-xs font-semibold px-3 py-2 rounded-lg">
                        <i class="fas fa-xmark"></i> Refuser
                    </button>
                </form>
            </div>
            @elseif($req->status === \App\Models\PayoutRequest::STATUS_APPROVED)
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <form method="POST" action="{{ route('admin.wallet.payouts.mark-paid', $req) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="payment_reference" required placeholder="Référence du versement (ex: transaction Mobile Money)"
                           class="flex-1 max-w-sm bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-3 py-2 rounded-lg">
                        <i class="fas fa-money-bill-transfer"></i> Marquer payé
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.wallet.payouts.reject', $req) }}" onsubmit="return confirm('Refuser cette demande de retrait ?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-rose-600/80 hover:bg-rose-500 text-white text-xs font-semibold px-3 py-2 rounded-lg">
                        <i class="fas fa-xmark"></i> Refuser
                    </button>
                </form>
            </div>
            @elseif($req->status === \App\Models\PayoutRequest::STATUS_PENDING_VERIFICATION)
                <p class="text-orange-400 text-xs mt-2"><i class="fas fa-hourglass-half mr-1"></i> En attente de la confirmation du demandeur (code de vérification envoyé).</p>
            @elseif($req->status === \App\Models\PayoutRequest::STATUS_PAID)
                <p class="text-emerald-400 text-xs mt-2"><i class="fas fa-circle-check mr-1"></i> Payé le {{ $req->paid_at?->format('d/m/Y H:i') }} — réf. {{ $req->payment_reference }}</p>
            @endif
        </div>
        @empty
        <div class="px-5 py-14 text-center text-slate-500 text-sm">Aucune demande de retrait pour ces filtres.</div>
        @endforelse
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $payoutRequests->links() }}
    </div>
</div>
@endsection
