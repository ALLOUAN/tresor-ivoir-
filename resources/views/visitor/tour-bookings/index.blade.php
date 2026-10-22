@extends('layouts.visitor-public')

@section('title', __('Mes voyages'))
@section('page-title', __('Mes voyages'))

@section('content')
@php
    $tones = [
        'paid' => 'text-emerald-300 bg-emerald-500/10',
        'deposit_paid' => 'text-amber-300 bg-amber-500/10',
        'pending_payment' => 'text-slate-300 bg-slate-800',
        'failed' => 'text-rose-300 bg-rose-500/10',
        'cancelled' => 'text-slate-400 bg-slate-800',
    ];
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $in = 'w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100';
@endphp
<div class="max-w-5xl mx-auto">
    @include('partials.visitor-account-nav')

    @if(session('success'))<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-sm">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-sm">{{ $errors->first() }}</div>@endif

    <p class="text-slate-400 text-sm mb-4">{{ __('Vos circuits réservés : solde à régler, feuille de route, liste des voyageurs et annulation.') }}</p>

    <div class="space-y-3">
        @forelse($bookings as $b)
            <div class="bg-green-900 border border-slate-800 rounded-2xl p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-white font-semibold">{{ $b->tour_name }}</p>
                        <p class="text-slate-400 text-sm mt-0.5">
                            {{ ucfirst($b->departure_date->locale(app()->getLocale())->isoFormat('ddd D MMM YYYY')) }}
                            · {{ trans_choice(':count voyageur|:count voyageurs', $b->travelers_count) }} · {{ $b->reference }}
                        </p>
                        @if($b->pickup_point)<p class="text-xs mt-0.5 text-slate-300"><i class="fas fa-van-shuttle mr-1"></i>{{ $b->pickup_point }}</p>@endif
                        <p class="text-sm mt-1 text-slate-200">
                            {{ __('Payé') }} {{ $fmt($b->amount_paid_xof) }} / {{ $fmt($b->amount_total_xof) }}
                            @if($b->refund_xof)<span class="text-emerald-300"> · {{ __('Remboursé :a sur votre solde', ['a' => $fmt($b->refund_xof)]) }}</span>@endif
                        </p>
                        @if($b->canPayBalance())
                            <p class="text-xs mt-1 {{ $b->balance_due_on?->isPast() ? 'text-rose-300' : 'text-amber-300' }}"><i class="fas fa-clock mr-1"></i>{{ __('Solde de :a à régler avant le :d', ['a' => $fmt($b->balanceXof()), 'd' => $b->balance_due_on?->format('d/m/Y')]) }}</p>
                        @endif
                        @if($b->departure && $b->isActive())
                            <p class="text-xs mt-1 {{ $b->departure->isGuaranteed() ? 'text-emerald-300' : 'text-slate-400' }}">
                                <i class="fas {{ $b->departure->isGuaranteed() ? 'fa-circle-check' : 'fa-hourglass-half' }} mr-1"></i>
                                {{ $b->departure->isGuaranteed() ? __('Départ garanti') : __('Départ à confirmer : encore :n voyageur(s) attendu(s) d\'ici le :d', ['n' => $b->departure->missingForGuarantee(), 'd' => $b->departure->decisionDate()->format('d/m/Y')]) }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $tones[$b->status] ?? '' }}">{{ $b->statusLabel() }}</span>
                        @if($b->isActive())
                            <a href="{{ $b->confirmationUrl() }}" class="text-xs font-semibold text-slate-100 border border-slate-700 hover:border-slate-500 rounded-lg px-3 py-1.5"><i class="fas fa-map mr-1"></i>{{ __('Feuille de route') }}</a>
                        @endif
                    </div>
                </div>

                @if($b->canPayBalance() || ($b->status === 'pending_payment' || $b->status === 'failed') && $b->departure?->isOpenForBooking())
                    <form method="POST" action="{{ route('visitor.tour-bookings.pay', $b) }}" class="mt-3 flex flex-wrap items-center gap-2">
                        @csrf
                        <span class="text-xs text-slate-300">{{ $b->canPayBalance() ? __('Régler le solde :') : __('Finaliser le paiement :') }}</span>
                        <button type="submit" name="payment_method" value="cinetpay" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-1.5">{{ __('Mobile Money / carte') }}</button>
                        <button type="submit" name="payment_method" value="wallet" class="text-xs text-slate-200 border border-slate-700 hover:border-slate-500 rounded-lg px-3 py-1.5">{{ __('Avec mon solde') }}</button>
                    </form>
                @endif

                @if($b->isActive())
                    <details class="mt-3" @if(! $b->travelersComplete() && $errors->isEmpty()) open @endif>
                        <summary class="cursor-pointer text-sm {{ $b->travelersComplete() ? 'text-slate-300' : 'text-amber-300' }}">
                            <i class="fas fa-id-card mr-1"></i>{{ $b->travelersComplete() ? __('Liste des voyageurs') : __('Complétez la liste des voyageurs (demandée par l\'agence)') }}
                        </summary>
                        <form method="POST" action="{{ route('visitor.tour-bookings.travelers', $b) }}" class="mt-3 space-y-2">
                            @csrf @method('PUT')
                            @for($i = 0; $i < $b->travelers_count; $i++)
                                @php $t = $b->travelers[$i] ?? []; @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <input type="text" name="travelers[{{ $i }}][name]" maxlength="120" value="{{ $t['name'] ?? '' }}" placeholder="{{ __('Voyageur :n — nom complet', ['n' => $i + 1]) }}" class="{{ $in }}" aria-label="{{ __('Voyageur :n — nom complet', ['n' => $i + 1]) }}">
                                    <input type="text" name="travelers[{{ $i }}][id_number]" maxlength="40" value="{{ $t['id_number'] ?? '' }}" placeholder="{{ __('N° de pièce d\'identité') }}" class="{{ $in }}" aria-label="{{ __('N° de pièce d\'identité') }}">
                                    <input type="tel" name="travelers[{{ $i }}][phone]" maxlength="30" value="{{ $t['phone'] ?? '' }}" placeholder="{{ __('Téléphone (facultatif)') }}" class="{{ $in }}" aria-label="{{ __('Téléphone (facultatif)') }}">
                                </div>
                            @endfor
                            <input type="text" name="emergency_contact" maxlength="200" value="{{ $b->emergency_contact }}" placeholder="{{ __('Contact d\'urgence (nom et téléphone)') }}" class="{{ $in }}" aria-label="{{ __('Contact d\'urgence (nom et téléphone)') }}">
                            <button type="submit" class="text-xs font-semibold bg-orange-500 hover:bg-orange-600 text-black rounded-lg px-3 py-2">{{ __('Enregistrer') }}</button>
                        </form>
                    </details>
                @endif

                @if($b->canBeCancelledByClient())
                    @php $refund = $b->refundIfCancelledNow(); @endphp
                    <form method="POST" action="{{ route('visitor.tour-bookings.cancel', $b) }}" class="mt-3"
                          onsubmit="return confirm({{ \Illuminate\Support\Js::from($refund > 0 ? __('Annuler ce voyage ? :a seront crédités sur votre solde.', ['a' => $fmt($refund)]) : __('Annuler ce voyage ? Selon les conditions du circuit, aucun remboursement n\'est dû.')) }});">
                        @csrf
                        <button type="submit" class="text-xs text-rose-300 hover:text-rose-200 border border-slate-700 rounded-lg px-3 py-1.5">
                            {{ __('Annuler') }} · {{ $refund > 0 ? __('remboursement :a (:p %)', ['a' => $fmt($refund), 'p' => $b->refundPercentNow()]) : __('non remboursable à ce stade') }}
                        </button>
                    </form>
                @endif
            </div>
        @empty
            @include('partials.visitor-empty', ['icon' => 'route', 'title' => __('Aucun voyage réservé pour le moment.'), 'text' => __('Circuits et séjours organisés par des agences locales.'), 'cta' => ['url' => route('travel-agency.index'), 'label' => __('Découvrir les agences et leurs circuits'), 'icon' => 'route']])
        @endforelse
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
</div>
@endsection
