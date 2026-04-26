@extends('layouts.app')

@section('title', 'Forfaits')
@section('page-title', 'Gérer mon abonnement')

@section('content')
@php
    $planOrder  = ['bronze' => 0, 'silver' => 1, 'gold' => 2];
    $currentCode = $currentSubscription?->plan?->code;
    $currentOrder = $planOrder[$currentCode] ?? -1;
    $icons       = ['bronze' => 'fa-seedling', 'silver' => 'fa-star', 'gold' => 'fa-gem'];
    $iconColors  = ['bronze' => 'text-emerald-400', 'silver' => 'text-amber-400', 'gold' => 'text-violet-400'];
@endphp

{{-- Abonnement actuel --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold mb-3">Abonnement actuel</h2>

    @if($currentSubscription)
        @php
            $remaining  = $daysRemaining ?? 0;
            $isCritical = $remaining <= 0;
            $isWarning  = ! $isCritical && $remaining < 7;
        @endphp

        <div class="border rounded-lg px-4 py-2.5 text-sm mb-4
            {{ $isCritical ? 'border-red-500/40 bg-red-500/10 text-red-300' : ($isWarning ? 'border-amber-500/40 bg-amber-500/10 text-amber-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300') }}">
            <i class="fas {{ $isCritical ? 'fa-triangle-exclamation' : ($isWarning ? 'fa-clock' : 'fa-circle-check') }} mr-1.5"></i>
            @if($isCritical) Abonnement expiré — renouvellement requis.
            @elseif($isWarning) Expire dans {{ $remaining }} jour(s) — pensez à renouveler.
            @else Abonnement actif — {{ $remaining }} jour(s) restant(s).
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            @foreach([
                ['Forfait', strtoupper($currentSubscription->plan->code ?? 'N/A') . ' — ' . ($currentSubscription->plan->name_fr ?? '—')],
                ['Cycle', $currentSubscription->billing_cycle === 'yearly' ? 'Annuel' : 'Mensuel'],
                ['Expire le', optional($currentSubscription->ends_at)->format('d/m/Y') ?: '—'],
                ['Jours restants', $remaining . ' jour(s)'],
            ] as [$lbl, $val])
            <div class="bg-slate-800/70 border border-slate-700 rounded-lg px-3 py-2">
                <p class="text-slate-400 text-xs">{{ $lbl }}</p>
                <p class="text-white font-semibold mt-0.5">{{ $val }}</p>
            </div>
            @endforeach
        </div>
    @else
        <p class="text-slate-400 text-sm">Aucun abonnement actif. Choisissez un forfait ci-dessous.</p>
    @endif
</div>

{{-- Plans --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach($plans as $plan)
    @php
        $pOrder  = $planOrder[$plan->code] ?? 0;
        $isCurrent   = $currentCode === $plan->code;
        $isUpgrade   = $currentOrder >= 0 && $pOrder > $currentOrder;
        $isDowngrade = $currentOrder >= 0 && $pOrder < $currentOrder;
        $icon        = $icons[$plan->code] ?? 'fa-star';
        $iconColor   = $iconColors[$plan->code] ?? 'text-amber-400';
    @endphp

    <div class="bg-slate-900 border rounded-xl p-5 flex flex-col
        {{ $isCurrent ? 'border-amber-500/50' : 'border-slate-800' }}">

        @if($isCurrent)
        <div class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 rounded-full px-3 py-1 self-start mb-3">
            Plan actuel
        </div>
        @endif

        <div class="flex items-center gap-2.5 mb-3">
            <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center">
                <i class="fas {{ $icon }} {{ $iconColor }} text-sm"></i>
            </div>
            <div>
                <p class="text-white font-bold">{{ strtoupper($plan->code) }} — {{ $plan->name_fr }}</p>
                <p class="text-slate-500 text-xs">{{ $plan->benefits_text ?: 'Boostez votre visibilité.' }}</p>
            </div>
        </div>

        <div class="mb-4 space-y-0.5 text-sm">
            <p class="text-slate-400">Mensuel : <span class="text-white font-semibold">{{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA</span></p>
            <p class="text-slate-400">Annuel : <span class="text-white font-semibold">{{ number_format((float) $plan->price_yearly, 0, ',', ' ') }} FCFA</span></p>
        </div>

        <ul class="space-y-1.5 mb-5 flex-1 text-xs text-slate-400">
            @foreach([
                [$plan->photos_limit . ' photo(s)', $plan->photos_limit > 0],
                ['Vidéo de présentation', $plan->has_video],
                ['Mise en avant accueil', $plan->has_homepage],
                ['Campagne newsletter', $plan->has_newsletter],
                ['Posts réseaux sociaux', $plan->has_social_posts],
                ['Badge vérifié', $plan->has_verified_badge],
                ['Stats ' . ($plan->stats_level === 'advanced' ? 'avancées' : 'de base'), true],
                ['Support ' . ($plan->support_level === 'priority' ? 'prioritaire' : 'e-mail'), true],
            ] as [$lbl, $ok])
            <li class="{{ $ok ? 'text-slate-300' : 'text-slate-600 line-through' }} flex items-center gap-2">
                <i class="fas {{ $ok ? 'fa-check text-emerald-500' : 'fa-xmark text-slate-600' }} w-3 text-center text-[10px]"></i>
                {{ $lbl }}
            </li>
            @endforeach
        </ul>

        @if($isCurrent)
            <a href="{{ route('provider.billing.checkout', $plan) }}"
               class="block text-center py-2.5 rounded-lg text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white transition">
                Renouveler
            </a>
        @elseif($isUpgrade)
            <a href="{{ route('provider.billing.checkout', $plan) }}"
               class="block text-center py-2.5 rounded-lg text-sm font-semibold bg-violet-600 hover:bg-violet-700 text-white transition">
                <i class="fas fa-arrow-up text-xs mr-1"></i> Passer à ce forfait
            </a>
        @elseif($isDowngrade)
            <a href="{{ route('provider.billing.checkout', $plan) }}"
               class="block text-center py-2.5 rounded-lg text-sm font-semibold border border-slate-600 text-slate-300 hover:bg-slate-800 transition">
                <i class="fas fa-arrow-down text-xs mr-1"></i> Rétrograder
            </a>
        @else
            <a href="{{ route('provider.billing.checkout', $plan) }}"
               class="block text-center py-2.5 rounded-lg text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white transition">
                Sélectionner
            </a>
        @endif
    </div>
    @endforeach
</div>
@endsection
