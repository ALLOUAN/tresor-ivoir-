@extends('layouts.visitor-public')

@section('title', __('Sur mesure et offres'))
@section('page-title', __('Sur mesure et offres — Art & Créations'))

@section('content')
@php $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA'; $in = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100'; @endphp
<div class="max-w-5xl mx-auto">
    @if(session('success'))<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-sm">{{ session('error') }}</div>@endif
    <p class="mb-4 text-sm"><a href="{{ route('visitor.art-orders.index') }}" class="text-orange-300"><i class="fas fa-arrow-left mr-1"></i>{{ __('Mes commandes d\'œuvres') }}</a></p>

    <h2 class="text-white font-semibold mb-2"><i class="fas fa-paintbrush text-orange-400 mr-1.5"></i>{{ __('Commandes sur mesure') }}</h2>
    <div class="space-y-3 mb-8">
        @forelse($commissions as $c)
            <div class="bg-green-900 border border-slate-800 rounded-2xl p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-white font-semibold">« {{ $c->subject }} » <span class="text-slate-400 font-normal">— {{ $c->provider?->name }}</span></p>
                        <p class="text-slate-400 text-xs">{{ $c->reference }} · {{ $c->created_at->format('d/m/Y') }}@if($c->budget_xof) · {{ __('budget :a', ['a' => $fmt($c->budget_xof)]) }}@endif</p>
                        @if($c->quote_xof)<p class="text-slate-200 text-sm mt-1">{{ __('Devis : :a, réalisation en :d jours', ['a' => $fmt($c->quote_xof), 'd' => $c->quote_days]) }} · {{ __('payé :a', ['a' => $fmt($c->amount_paid_xof)]) }}</p>@endif
                        @if($c->quote_message)<p class="text-slate-400 text-xs italic mt-0.5">« {{ $c->quote_message }} »</p>@endif
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-200">{{ $c->statusLabel() }}</span>
                </div>
                @if($c->sketches && in_array($c->status, ['sketch_sent', 'revision', 'sketch_approved', 'balance_paid', 'delivered'], true))
                    <div class="flex flex-wrap gap-2 mt-3">@foreach($c->sketches as $s)<a href="{{ $s }}" target="_blank"><img src="{{ $s }}" alt="{{ __('Esquisse') }}" class="w-24 h-24 rounded-lg object-cover border border-slate-700"></a>@endforeach</div>
                    @if($c->sketch_note)<p class="text-slate-300 text-xs mt-1">« {{ $c->sketch_note }} »</p>@endif
                @endif
                @if($c->dueStage())
                    <form method="POST" action="{{ route('visitor.art-commissions.pay', $c) }}" class="mt-3 flex flex-wrap items-center gap-2">
                        @csrf
                        <span class="text-xs text-slate-300">{{ $c->dueStage() === 'deposit' ? __('Acompte à verser :') : __('Solde à régler :') }} <b class="text-white">{{ $fmt($c->dueAmount()) }}</b></span>
                        <button name="payment_method" value="cinetpay" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5">{{ __('Mobile Money / carte') }}</button>
                        <button name="payment_method" value="wallet" class="text-xs text-slate-200 border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Avec mon solde') }}</button>
                    </form>
                @endif
                @if($c->status === 'sketch_sent')
                    <div class="mt-3 flex flex-wrap items-end gap-2">
                        <form method="POST" action="{{ route('visitor.art-commissions.sketch', $c) }}">@csrf<button name="decision" value="approve" class="text-xs font-semibold bg-emerald-600 text-white rounded-lg px-3 py-2">{{ __('Valider l\'esquisse') }}</button></form>
                        @if($c->revisions_used < \App\Models\ArtCommission::MAX_REVISIONS)
                            <form method="POST" action="{{ route('visitor.art-commissions.sketch', $c) }}" class="flex flex-wrap items-end gap-2">@csrf
                                <input name="note" required maxlength="1000" placeholder="{{ __('Retouches souhaitées') }}" class="{{ $in }}">
                                <button name="decision" value="revise" class="text-xs text-slate-200 border border-slate-700 rounded-lg px-3 py-2">{{ __('Demander une retouche (:n restante(s))', ['n' => \App\Models\ArtCommission::MAX_REVISIONS - $c->revisions_used]) }}</button>
                            </form>
                        @endif
                    </div>
                @endif
                @if(in_array($c->status, ['requested', 'quoted'], true))
                    <form method="POST" action="{{ route('visitor.art-commissions.cancel', $c) }}" class="mt-2" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Annuler cette demande ?')) }});">@csrf<button class="text-xs text-rose-300">{{ __('Annuler la demande') }}</button></form>
                @endif
            </div>
        @empty
            <p class="text-slate-400 text-sm">{{ __('Aucune commande sur mesure. Ouvrez la page d\'un artiste pour lui en proposer une.') }}</p>
        @endforelse
        {{ $commissions->links() }}
    </div>

    <h2 class="text-white font-semibold mb-2"><i class="fas fa-handshake text-orange-400 mr-1.5"></i>{{ __('Mes offres') }}</h2>
    <div class="space-y-2">
        @forelse($offers as $o)
            <div class="bg-green-900 border border-slate-800 rounded-2xl p-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                <span class="text-white">{{ $o->artwork?->title }} — {{ $fmt($o->amount_xof) }}@if($o->counter_amount_xof) <span class="text-amber-300">→ {{ __('contre-proposition :a', ['a' => $fmt($o->counter_amount_xof)]) }}</span>@endif</span>
                <span class="flex items-center gap-2"><span class="text-xs text-slate-300">{{ $o->statusLabel() }}</span>
                    @if($o->artwork && ($o->payablePrice() || ($o->status === 'countered' && ! $o->expires_at?->isPast())))<a href="{{ route('art.show', $o->artwork->slug) }}#acheter" class="text-xs font-semibold bg-orange-500 text-black rounded-lg px-3 py-1.5">{{ $o->payablePrice() ? __('Payer') : __('Voir') }}</a>@endif</span>
            </div>
        @empty
            <p class="text-slate-400 text-sm">{{ __('Aucune offre.') }}</p>
        @endforelse
    </div>
</div>
@endsection
