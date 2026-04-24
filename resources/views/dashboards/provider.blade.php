@extends('layouts.app')

@section('title', 'Espace Prestataire')
@section('page-title', 'Tableau de bord — Prestataire')

@section('header-actions')
<a href="#" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium rounded-lg transition border border-slate-700">
    <i class="fas fa-pen"></i> Modifier ma fiche
</a>
@endsection

@section('content')

@if(!$provider)
{{-- No provider profile yet --}}
<div class="max-w-lg mx-auto text-center py-16">
    <div class="w-20 h-20 rounded-full bg-amber-900/30 flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-store text-amber-400 text-3xl"></i>
    </div>
    <h2 class="text-white text-xl font-bold mb-2">Aucune fiche prestataire</h2>
    <p class="text-slate-400 text-sm mb-6">Votre compte n'est pas encore lié à un établissement. Créez votre fiche pour apparaître dans l'annuaire.</p>
    <a href="#" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition">
        <i class="fas fa-circle-plus"></i> Créer ma fiche
    </a>
</div>
@else

{{-- ── Subscription banner ──────────────────────────────────────────────── --}}
@if($subscription)
@php
$planCode = strtolower($subscription->plan->code ?? 'bronze');
$bannerColors = ['gold'=>'from-amber-700/40 to-amber-900/20 border-amber-600/40','silver'=>'from-slate-500/30 to-slate-700/20 border-slate-500/40','bronze'=>'from-amber-900/30 to-slate-800/20 border-amber-800/40'];
$bc = $bannerColors[$planCode] ?? $bannerColors['bronze'];
@endphp
<div class="bg-gradient-to-r {{ $bc }} border rounded-xl p-5 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
            <i class="fas fa-gem text-amber-400 text-xl"></i>
        </div>
        <div>
            <p class="text-white font-semibold">Forfait {{ ucfirst($planCode) }}</p>
            <p class="text-slate-400 text-xs mt-0.5">
                Valide jusqu'au {{ $subscription->ends_at?->format('d/m/Y') ?? 'N/A' }}
                · {{ $subscription->ends_at?->diffInDays(now()) > 0 ? $subscription->ends_at->diffInDays(now()) . ' jours restants' : 'Expiré' }}
            </p>
        </div>
    </div>
    <a href="{{ route('provider.billing.plans') }}" class="shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition">
        Gérer l'abonnement
    </a>
</div>
@else
<div class="bg-slate-900 border border-dashed border-slate-700 rounded-xl p-5 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <i class="fas fa-gem text-slate-600 text-xl"></i>
        <div>
            <p class="text-slate-300 font-medium text-sm">Aucun abonnement actif</p>
            <p class="text-slate-500 text-xs">Souscrivez un forfait pour bénéficier de plus de visibilité.</p>
        </div>
    </div>
    <a href="{{ route('provider.billing.plans') }}" class="shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition">
        Voir les forfaits
    </a>
</div>
@endif

{{-- ── Stats ───────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    @php
    $stat_cards = [
        ['label' => 'Vues de la fiche',    'value' => number_format($stats['views']),          'icon' => 'fa-eye',            'color' => 'text-blue-400',   'bg' => 'bg-blue-900/20'],
        ['label' => 'Clics téléphone',     'value' => number_format($stats['clicks_phone']),   'icon' => 'fa-phone',          'color' => 'text-emerald-400','bg' => 'bg-emerald-900/20'],
        ['label' => 'Clics site web',      'value' => number_format($stats['clicks_website']), 'icon' => 'fa-globe',          'color' => 'text-violet-400', 'bg' => 'bg-violet-900/20'],
        ['label' => 'Note moyenne',        'value' => number_format($stats['rating_avg'], 1),  'icon' => 'fa-star',           'color' => 'text-amber-400',  'bg' => 'bg-amber-900/20'],
        ['label' => 'Nombre d\'avis',      'value' => number_format($stats['rating_count']),   'icon' => 'fa-comments',       'color' => 'text-rose-400',   'bg' => 'bg-rose-900/20'],
    ];
    @endphp
    @foreach($stat_cards as $card)
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="w-8 h-8 rounded-lg {{ $card['bg'] }} flex items-center justify-center">
                <i class="fas {{ $card['icon'] }} {{ $card['color'] }} text-sm"></i>
            </div>
        </div>
        <p class="text-white text-xl font-bold">{{ $card['value'] }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $card['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Two columns ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    {{-- Recent reviews --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-star text-amber-400"></i> Derniers avis approuvés
                @if($pending_reviews > 0)
                <span class="bg-rose-500 text-white text-xs px-2 py-0.5 rounded-full" title="{{ $pending_reviews }} en attente">{{ $pending_reviews }} en attente</span>
                @endif
            </h2>
            <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Voir tout →</a>
        </div>
        <div class="divide-y divide-slate-800">
            @forelse($recent_reviews as $review)
            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-700' }}"></i>
                        @endfor
                    </div>
                    <span class="text-slate-500 text-xs">{{ $review->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-slate-300 text-sm">{{ Str::limit($review->comment, 120) }}</p>
                @if($review->title)
                <p class="text-slate-500 text-xs mt-1 italic">"{{ $review->title }}"</p>
                @endif
            </div>
            @empty
            <div class="px-5 py-8 text-center text-slate-500 text-sm">
                <i class="fas fa-star text-slate-700 text-2xl mb-2 block"></i>
                Aucun avis pour le moment.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Invoices --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-file-invoice text-violet-400"></i> Dernières factures
            </h2>
            <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Voir tout →</a>
        </div>
        <div class="divide-y divide-slate-800">
            @forelse($invoices as $invoice)
            @php
            $statusColors = ['paid'=>['text-emerald-400','bg-emerald-900/30'],'pending'=>['text-amber-400','bg-amber-900/30'],'overdue'=>['text-red-400','bg-red-900/30'],'cancelled'=>['text-slate-400','bg-slate-800']];
            [$ic, $ibg] = $statusColors[$invoice->status] ?? ['text-slate-400','bg-slate-800'];
            @endphp
            <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-800/40 transition">
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium">{{ $invoice->number }}</p>
                    <p class="text-slate-500 text-xs">{{ $invoice->issued_at?->format('d/m/Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-white text-sm font-bold">{{ number_format($invoice->total_amount, 0, ',', ' ') }} <span class="text-slate-500 text-xs font-normal">FCFA</span></span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs {{ $ic }} {{ $ibg }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                    <a href="#" class="text-slate-600 hover:text-amber-400 text-xs transition" title="Télécharger">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-slate-500 text-sm">
                <i class="fas fa-file-invoice text-slate-700 text-2xl mb-2 block"></i>
                Aucune facture disponible.
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Provider info card ──────────────────────────────────────────────── --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 flex items-start gap-5">
    <div class="w-14 h-14 rounded-xl bg-slate-800 flex items-center justify-center shrink-0">
        <i class="fas fa-store text-amber-400 text-xl"></i>
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-white font-bold text-lg">{{ $provider->business_name }}</h3>
                <p class="text-slate-400 text-sm">{{ $provider->category->name ?? 'Non catégorisé' }}</p>
            </div>
            @php
            $pstatusColors = ['active'=>'text-emerald-400 bg-emerald-900/30','pending'=>'text-amber-400 bg-amber-900/30','suspended'=>'text-red-400 bg-red-900/30','inactive'=>'text-slate-400 bg-slate-800'];
            @endphp
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $pstatusColors[$provider->status] ?? 'text-slate-400 bg-slate-800' }}">
                {{ ucfirst($provider->status) }}
            </span>
        </div>
        <p class="text-slate-400 text-sm mt-2 line-clamp-2">{{ $provider->description }}</p>
        <div class="flex flex-wrap gap-3 mt-3 text-xs text-slate-500">
            @if($provider->city)
            <span><i class="fas fa-location-dot mr-1"></i>{{ $provider->city }}</span>
            @endif
            @if($provider->phone)
            <span><i class="fas fa-phone mr-1"></i>{{ $provider->phone }}</span>
            @endif
            @if($provider->website)
            <a href="{{ $provider->website }}" target="_blank" class="text-amber-400 hover:text-amber-300 transition">
                <i class="fas fa-globe mr-1"></i>Site web
            </a>
            @endif
        </div>
    </div>
</div>

@endif
@endsection
