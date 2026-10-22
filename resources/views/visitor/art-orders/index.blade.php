@extends('layouts.visitor-public')

@section('title', __('Mes commandes Art & Créations'))
@section('page-title', __('Mes commandes — Art & Créations'))

@section('content')
@php $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA'; $in = 'bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100'; @endphp
<div class="max-w-5xl mx-auto">
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ session('error') }}</div>
    @endif
    @if($errors->any())<div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>@endif
    @if($errors->art->any())<div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->art->first() }}</div>@endif

    <p class="mb-4 text-sm"><a href="{{ route('visitor.art-commissions.index') }}" class="text-orange-300 hover:text-orange-200"><i class="fas fa-paintbrush mr-1"></i>{{ __('Mes commandes sur mesure et mes offres') }}</a></p>

    @if($orders->isEmpty())
        <div class="bg-green-900 border border-slate-800 rounded-xl p-14 text-center">
            <i class="fas fa-palette text-slate-700 text-4xl mb-3 block"></i>
            <p class="text-slate-400 text-sm">{{ __('Vous n\'avez pas encore acheté d\'œuvre.') }}</p>
            <a href="{{ route('art.index') }}" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-black text-sm font-semibold transition"><i class="fas fa-palette text-xs"></i>{{ __('Découvrir les œuvres') }}</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($orders as $order)
                <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
                    <div class="flex items-center gap-4">
                        <a href="{{ $order->artwork ? route('art.show', $order->artwork->slug) : '#' }}" class="shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-slate-800 border border-slate-700">
                            @if(!empty($order->artwork?->images[0]))<img src="{{ $order->artwork->images[0] }}" alt="{{ $order->artwork->title }}" class="w-full h-full object-cover">@else<div class="w-full h-full flex items-center justify-center"><i class="fas fa-image text-slate-600 text-xl"></i></div>@endif
                        </a>
                        <div class="min-w-0 flex-1">
                            <p class="text-white font-medium truncate">{{ $order->artwork?->title ?? __('Œuvre indisponible') }}@if($order->edition_label) <span class="text-slate-400 font-normal">— {{ $order->edition_label }}@if($order->edition_number) {{ $order->edition_number }}/{{ $order->edition_size }}@endif</span>@endif</p>
                            <p class="text-slate-500 text-xs">{{ $order->provider?->name }} · {{ $order->reference }} · {{ $order->deliveryLabel() }}</p>
                            @if($order->carrier)<p class="text-slate-300 text-xs mt-0.5"><i class="fas fa-truck mr-1"></i>{{ $order->carrier }}@if($order->tracking_number) · {{ __('suivi') }} <span class="font-mono">{{ $order->tracking_number }}</span>@endif</p>@endif
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-white font-semibold text-sm">{{ $fmt((int) $order->amount_total_xof) }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold mt-1 {{ match ($order->status) { 'delivered' => 'bg-emerald-500/20 text-emerald-300', 'paid', 'shipped', 'reserved' => 'bg-orange-500/20 text-orange-300', 'refunded', 'cancelled' => 'bg-red-500/20 text-red-300', default => 'bg-slate-700/40 text-slate-300' } }}">{{ $order->labelForStatus() }}</span>
                            @if($order->hasCertificate())<a href="{{ $order->certificatePdfUrl() }}" class="block mt-1.5 text-orange-300 hover:text-orange-200 text-xs"><i class="fas fa-certificate mr-1"></i>{{ __('Certificat') }}</a>@endif
                        </div>
                    </div>

                    @if($order->payment_plan === 'layaway' && $order->installments)
                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                            @foreach($order->installments as $i)
                                <div class="rounded-lg border px-2.5 py-2 {{ $i['paid_at'] ? 'border-emerald-700/50 text-emerald-300' : 'border-slate-700 text-slate-300' }}">
                                    <b>{{ $i['n'] === 1 ? __('Acompte') : __('Échéance :n', ['n' => $i['n']]) }}</b><br>{{ $fmt((int) $i['amount_xof']) }}<br><span class="text-slate-400">{{ $i['paid_at'] ? __('payée') : __('avant le :d', ['d' => \Carbon\Carbon::parse($i['due_on'])->format('d/m/Y')]) }}</span>
                                </div>
                            @endforeach
                        </div>
                        @if($order->status === 'reserved' && ($next = $order->nextInstallment()))
                            <form method="POST" action="{{ route('visitor.art-orders.installment', $order) }}" class="mt-2 flex flex-wrap items-center gap-2">
                                @csrf
                                <span class="text-xs text-slate-300">{{ __('Régler :a :', ['a' => $fmt((int) $next['amount_xof'])]) }}</span>
                                <button name="payment_method" value="cinetpay" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5">{{ __('Mobile Money / carte') }}</button>
                                <button name="payment_method" value="wallet" class="text-xs text-slate-200 border border-slate-700 rounded-lg px-3 py-1.5">{{ __('Avec mon solde') }}</button>
                            </form>
                        @endif
                    @endif

                    @if(in_array($order->status, ['paid', 'shipped'], true))
                        <form method="POST" action="{{ route('visitor.art-orders.receipt', $order) }}" class="mt-3" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Confirmez-vous avoir reçu l\'œuvre en bon état ?')) }});">
                            @csrf
                            <button class="text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg px-3 py-1.5"><i class="fas fa-box-open mr-1"></i>{{ __('J\'ai reçu l\'œuvre') }}</button>
                        </form>
                    @endif
                    @if($order->canRequestReturn())
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs text-slate-300"><i class="fas fa-rotate-left mr-1"></i>{{ __('Demander un retour (jusqu\'au :d)', ['d' => $order->returnWindowEndsAt()->format('d/m/Y')]) }}</summary>
                            <form method="POST" action="{{ route('visitor.art-orders.return', $order) }}" class="mt-2 flex flex-wrap items-end gap-2">
                                @csrf
                                <input name="reason" required minlength="10" maxlength="500" placeholder="{{ __('Motif (ex : couleurs différentes des photos)') }}" class="{{ $in }} flex-1 min-w-[14rem]">
                                <button class="text-xs text-rose-200 border border-rose-700/60 rounded-lg px-3 py-2">{{ __('Envoyer la demande') }}</button>
                            </form>
                        </details>
                    @endif
                    @if($order->return_status)
                        <p class="mt-2 text-xs text-amber-300"><i class="fas fa-rotate-left mr-1"></i>{{ __('Retour') }} : {{ [ 'requested' => __('demandé — en attente de l\'artiste'), 'accepted' => __('accepté — renvoyez l\'œuvre dans son emballage d\'origine'), 'refused' => __('refusé par l\'artiste — contactez le support'), 'refunded' => __('remboursé sur votre solde') ][$order->return_status] ?? $order->return_status }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
