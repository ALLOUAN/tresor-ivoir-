@extends('layouts.app')

@section('title', 'Forfaits')
@section('page-title', 'Choisir un forfait')

@section('content')
<div class="mb-6 bg-slate-900 border border-slate-800 rounded-xl p-5">
    <h2 class="text-white font-semibold">Abonnement actuel</h2>

    @if($currentSubscription)
        @php
            $remaining = $daysRemaining ?? 0;
            $isCritical = $remaining <= 0;
            $isWarning = !$isCritical && $remaining < 7;
            $alertClass = $isCritical
                ? 'border-red-500/40 bg-red-500/10 text-red-200'
                : ($isWarning ? 'border-amber-500/40 bg-amber-500/10 text-amber-200' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200');
            $daysClass = $isCritical ? 'text-red-400' : ($isWarning ? 'text-amber-400' : 'text-emerald-400');
        @endphp

        <div class="mt-3 border rounded-lg px-3 py-2 text-sm {{ $alertClass }}">
            @if($isCritical)
                <i class="fas fa-triangle-exclamation mr-1"></i> Abonnement expiré. Renouvellement recommandé immédiatement.
            @elseif($isWarning)
                <i class="fas fa-clock mr-1"></i> Votre abonnement expire bientôt (moins de 7 jours).
            @else
                <i class="fas fa-circle-check mr-1"></i> Abonnement actif.
            @endif
        </div>

        <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
            <div class="bg-slate-800/70 border border-slate-700 rounded-lg px-3 py-2">
                <p class="text-slate-400 text-xs">Plan</p>
                <p class="text-white font-semibold">{{ strtoupper($currentSubscription->plan->code ?? 'N/A') }} - {{ $currentSubscription->plan->name_fr ?? '—' }}</p>
            </div>
            <div class="bg-slate-800/70 border border-slate-700 rounded-lg px-3 py-2">
                <p class="text-slate-400 text-xs">Cycle</p>
                <p class="text-white font-semibold">{{ $currentSubscription->billing_cycle === 'yearly' ? 'Annuel' : 'Mensuel' }}</p>
            </div>
            <div class="bg-slate-800/70 border border-slate-700 rounded-lg px-3 py-2">
                <p class="text-slate-400 text-xs">Date fin</p>
                <p class="text-white font-semibold">{{ optional($currentSubscription->ends_at)->format('d/m/Y') ?: '—' }}</p>
            </div>
            <div class="bg-slate-800/70 border border-slate-700 rounded-lg px-3 py-2">
                <p class="text-slate-400 text-xs">Jours restants</p>
                <p class="font-semibold {{ $daysClass }}">{{ $remaining }} jour(s)</p>
            </div>
        </div>

        <a href="{{ route('provider.billing.checkout', $currentSubscription->plan_id) }}"
           class="inline-flex mt-4 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
            Renouveler ce forfait
        </a>
    @else
        <p class="text-slate-400 text-sm mt-2">Aucun abonnement actif pour le moment. Sélectionnez un forfait ci-dessous.</p>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach($plans as $plan)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-bold">{{ strtoupper($plan->code) }} - {{ $plan->name_fr }}</h2>
            <p class="text-slate-400 text-sm mt-1">{{ $plan->benefits_text ?: 'Forfait premium pour votre visibilité.' }}</p>
            <div class="mt-4 text-slate-300 text-sm space-y-1">
                <p>Mensuel: <span class="text-white font-semibold">{{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA</span></p>
                <p>Annuel: <span class="text-white font-semibold">{{ number_format((float) $plan->price_yearly, 0, ',', ' ') }} FCFA</span></p>
            </div>
            <a href="{{ route('provider.billing.checkout', $plan) }}" class="inline-flex mt-4 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                Sélectionner ce forfait
            </a>
        </div>
    @endforeach
</div>
@endsection
