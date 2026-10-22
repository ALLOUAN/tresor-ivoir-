@extends('layouts.app')

@section('title', 'Commande ' . $order->reference)
@section('page-title', 'Commande ' . $order->reference)

@section('header-actions')
    <a href="{{ route('provider.art-orders.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
    </a>
@endsection

@section('content')

<div class="max-w-2xl space-y-6">

    @if(session('success'))
    <div class="px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 bg-rose-900/30 border border-rose-800 text-rose-300 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation shrink-0"></i> {{ session('error') }}
    </div>
    @endif

    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
        <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-slate-500 text-xs uppercase tracking-wide">Œuvre vendue</p>
                <h2 class="text-white font-serif text-xl font-semibold mt-1 break-words">{{ $order->artwork?->title }}</h2>
            </div>
            <div class="shrink-0 text-right text-xs text-slate-500">
                <p>{{ $order->created_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="p-5 sm:p-6 space-y-5">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                    <i class="fas fa-user"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $order->buyer_name }}</p>
                    <a href="mailto:{{ $order->buyer_email }}" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $order->buyer_email }}</a>
                    @if($order->buyer_phone)
                        <p class="text-slate-400 text-sm">{{ $order->buyer_phone }}</p>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Part qui vous revient</p>
                <p class="text-white text-lg font-bold">{{ number_format((int) $order->artist_net_amount_xof, 0, ',', ' ') }} XOF</p>
            </div>
        </div>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-1">Statut de la commande</h3>
        <p class="text-slate-500 text-xs mb-4">
            Statut actuel : <span class="text-slate-300 font-medium">{{ $order->labelForStatus() }}</span>
        </p>

        @php $fld = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100'; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4 text-sm">
            <div><p class="text-slate-500 text-xs">Remise</p><p class="text-slate-200">{{ $order->deliveryLabel() ?? 'Non précisée' }}@if($order->delivery_fee_xof) · {{ number_format($order->delivery_fee_xof, 0, ',', ' ') }} XOF@endif</p>
                @if($order->delivery_address)<p class="text-slate-400 text-xs">{{ $order->delivery_address }}{{ $order->delivery_city ? ', '.$order->delivery_city : '' }}</p>@endif
                @if($order->insurance_xof)<p class="text-emerald-300 text-xs"><i class="fas fa-shield-halved mr-1"></i>Assurance transport souscrite ({{ number_format($order->insurance_xof, 0, ',', ' ') }} XOF)</p>@endif</div>
            @if($order->edition_label)<div><p class="text-slate-500 text-xs">Édition</p><p class="text-slate-200">{{ $order->edition_label }}@if($order->edition_number) — exemplaire {{ $order->edition_number }}/{{ $order->edition_size }}@endif</p></div>@endif
            @if($order->payment_plan === 'layaway')
                <div class="sm:col-span-2"><p class="text-slate-500 text-xs">Paiement en 3 fois</p>
                    @foreach($order->installments ?? [] as $i)<p class="text-xs {{ $i['paid_at'] ? 'text-emerald-300' : 'text-slate-400' }}">Échéance {{ $i['n'] }} : {{ number_format($i['amount_xof'], 0, ',', ' ') }} XOF — {{ $i['paid_at'] ? 'payée' : 'due le '.\Carbon\Carbon::parse($i['due_on'])->format('d/m/Y') }}</p>@endforeach</div>
            @endif
        </div>

        @if($order->status === \App\Models\ArtworkOrder::STATUS_RESERVED)
            <p class="text-amber-300 text-sm"><i class="fas fa-lock mr-1"></i>Œuvre réservée : remettez-la uniquement après le paiement de toutes les échéances.</p>
        @elseif($order->status === \App\Models\ArtworkOrder::STATUS_PAID)
            @if($order->delivery_mode && $order->delivery_mode !== 'pickup')
                <form method="POST" action="{{ route('provider.art-orders.ship', $order) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <label class="text-xs text-slate-400">Transporteur<input name="carrier" required maxlength="80" placeholder="Coursier, DHL, Chronopost…" class="{{ $fld }} block mt-1"></label>
                    <label class="text-xs text-slate-400">N° de suivi<input name="tracking_number" maxlength="80" class="{{ $fld }} block mt-1"></label>
                    <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-4 py-2 rounded-lg"><i class="fas fa-truck"></i> Expédier</button>
                </form>
            @else
                <form method="POST" action="{{ route('provider.art-orders.status', $order) }}" onsubmit="return confirm('L\'acheteur a retiré l\'œuvre à l\'atelier ?');">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\ArtworkOrder::STATUS_SHIPPED }}">
                    <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-4 py-2 rounded-lg"><i class="fas fa-hand-holding"></i> Œuvre prête / remise en main propre</button>
                </form>
            @endif
        @elseif($order->status === \App\Models\ArtworkOrder::STATUS_SHIPPED)
            <p class="text-slate-300 text-sm">Expédiée le {{ $order->shipped_at?->format('d/m/Y') }}@if($order->carrier) via {{ $order->carrier }}@endif @if($order->tracking_number)— suivi {{ $order->tracking_number }}@endif. L'acheteur confirme la réception ; à défaut :</p>
            <form method="POST" action="{{ route('provider.art-orders.status', $order) }}" class="mt-2">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ \App\Models\ArtworkOrder::STATUS_DELIVERED }}">
                <button class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg"><i class="fas fa-box-open"></i> Confirmer la remise</button>
            </form>
        @elseif($order->status === \App\Models\ArtworkOrder::STATUS_DELIVERED)
            <p class="text-emerald-400 text-sm"><i class="fas fa-circle-check mr-1"></i>Reçue le {{ ($order->received_at ?? $order->delivered_at)?->format('d/m/Y') }}.
                @if(! $order->return_status && $order->returnWindowEndsAt()?->isFuture()) Votre part sera disponible le {{ $order->returnWindowEndsAt()->format('d/m/Y') }} (fin du délai de retour).@endif</p>
            @if($order->return_status)
                <div class="mt-3 rounded-lg border border-amber-600/40 bg-amber-900/20 p-3 text-sm">
                    <p class="text-amber-200 font-semibold">Retour : {{ ['requested' => 'demandé', 'accepted' => 'accepté — en attente de l\'œuvre', 'refused' => 'refusé', 'refunded' => 'remboursé'][$order->return_status] ?? $order->return_status }}</p>
                    @if($order->return_reason)<p class="text-slate-300 text-xs mt-1">« {{ $order->return_reason }} »</p>@endif
                    <div class="flex flex-wrap gap-2 mt-2">
                        @if($order->return_status === 'requested')
                            <form method="POST" action="{{ route('provider.art-orders.return', $order) }}">@csrf<button name="action" value="accept" class="text-xs bg-orange-500 text-black font-semibold rounded-lg px-3 py-1.5">Accepter le retour</button></form>
                            <form method="POST" action="{{ route('provider.art-orders.return', $order) }}">@csrf<button name="action" value="refuse" class="text-xs border border-slate-600 text-slate-200 rounded-lg px-3 py-1.5">Refuser</button></form>
                        @endif
                        @if(in_array($order->return_status, ['requested', 'accepted'], true))
                            <form method="POST" action="{{ route('provider.art-orders.return', $order) }}" onsubmit="return confirm('Œuvre récupérée en bon état ? L\'acheteur sera remboursé intégralement sur son solde.');">@csrf<button name="action" value="received" class="text-xs bg-emerald-600 text-white font-semibold rounded-lg px-3 py-1.5">Œuvre récupérée — rembourser</button></form>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <p class="text-slate-500 text-sm">Aucune action possible sur cette commande pour l'instant.</p>
        @endif

        @if($order->hasCertificate())
            <div class="mt-5 pt-4 border-t border-slate-800 flex flex-wrap items-center gap-3">
                <a href="{{ $order->certificatePdfUrl() }}" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    <i class="fas fa-certificate"></i> Certificat d'authenticité (PDF)
                </a>
                <span class="text-slate-500 text-xs">N° {{ $order->certificateNumber() }} — joignez-le à l'œuvre : l'acheteur le retrouve aussi dans son espace.</span>
            </div>
        @endif
    </div>
</div>
@endsection
