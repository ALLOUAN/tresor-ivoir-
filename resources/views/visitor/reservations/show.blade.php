@extends('layouts.visitor-public')

@section('title', 'Réservation '.$reservation->reference)
@section('page-title', __('Détails de la réservation'))

@section('content')
<div class="max-w-2xl mx-auto">
    @include('partials.visitor-account-nav')

    @php
        $badgeColors = [
            'upcoming' => 'text-blue-300 bg-blue-500/10',
            'ongoing' => 'text-emerald-300 bg-emerald-500/10',
            'completed' => 'text-orange-300 bg-orange-500/10',
            'cancelled' => 'text-rose-300 bg-rose-500/10',
        ];
        $roomSubtotal = (int) $reservation->room_price_xof * (int) $reservation->nights * (int) $reservation->rooms_count;
        $fees = max(0, (int) $reservation->total_xof - $roomSubtotal);
        $balance = max(0, (int) $reservation->total_xof - (int) $reservation->deposit_amount_xof);
        $isPaid = $reservation->payment_status === \App\Models\Reservation::PAYMENT_DEPOSIT_PAID;
    @endphp

    <div class="bg-green-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-widest">{{ __('Réservation') }}</p>
                <p class="text-white text-lg font-bold">{{ $reservation->reference }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeColors[$reservation->computed_status] ?? 'text-slate-400 bg-slate-800' }}">
                {{ $reservation->labelForComputedStatus() }}
            </span>
        </div>

        <div class="p-5 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Établissement') }}</p>
                    <p class="text-white text-sm font-medium">{{ $reservation->accommodation_name }}</p>
                    <p class="text-slate-500 text-xs mt-1">
                        {{ $reservation->accommodation?->type_label }}
                        @if($reservation->accommodation?->city)
                            <br>{{ $reservation->accommodation->city->name }} · {{ $reservation->accommodation->city->region_administrative }}
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Séjour') }}</p>
                    <p class="text-white text-sm font-medium">{{ $reservation->room_name }}</p>
                    <p class="text-slate-500 text-xs mt-1">
                        {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }}
                        <br>{{ $reservation->nights }} {{ __('nuit') }}{{ $reservation->nights > 1 ? 's' : '' }} · {{ $reservation->rooms_count }} {{ __('chambre') }}{{ $reservation->rooms_count > 1 ? 's' : '' }} · {{ $reservation->guests_count }} {{ __('pers.') }}
                    </p>
                </div>
            </div>

            <div>
                <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Tarification') }}</p>
                <div class="bg-slate-800/60 border border-slate-700 rounded-xl divide-y divide-slate-700 text-sm">
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-slate-400">{{ __('Tarif par nuit') }}</span>
                        <span class="text-slate-200">{{ number_format((int) $reservation->room_price_xof, 0, ',', ' ') }} {{ __('FCFA') }}</span>
                    </div>
                    @if($fees > 0)
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-slate-400">{{ __('Frais de service') }}</span>
                        <span class="text-slate-200">{{ number_format($fees, 0, ',', ' ') }} {{ __('FCFA') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-white font-semibold">{{ __('Montant total') }}</span>
                        <span class="text-white font-semibold">{{ number_format((int) $reservation->total_xof, 0, ',', ' ') }} {{ __('FCFA') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-emerald-400">{{ __('Acompte payé') }}</span>
                        <span class="text-emerald-400 font-medium">{{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} {{ __('FCFA') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-amber-400">{{ __('Solde à régler sur place') }}</span>
                        <span class="text-amber-400 font-medium">{{ number_format($balance, 0, ',', ' ') }} {{ __('FCFA') }}</span>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Statuts') }}</p>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-300">{{ __('Réservation :') }} {{ $reservation->labelForStatus() }}</span>
                    <span class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-300">{{ __('Paiement :') }} {{ $reservation->labelForPaymentStatus() }}</span>
                </div>
            </div>

            @php $deadline = $reservation->freeCancellationDeadline(); @endphp
            <div class="pt-2 border-t border-slate-800">
                <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Annulation') }}</p>
                @if($reservation->status === \App\Models\Reservation::STATUS_CANCELLED)
                    <p class="text-sm text-slate-300">
                        {{ __('Réservation annulée') }}{{ $reservation->cancelled_at ? __(' le ').$reservation->cancelled_at->format('d/m/Y') : '' }}.
                        @if($reservation->payment_status === \App\Models\Reservation::PAYMENT_REFUNDED)
                            {{ __('Votre acompte a été remboursé.') }}
                        @elseif((int) $reservation->refund_due_xof > 0)
                            {{ __('Votre acompte de') }} {{ number_format((int) $reservation->refund_due_xof, 0, ',', ' ') }} {{ __('FCFA va vous être remboursé.') }}
                        @elseif($reservation->refund_due_xof === 0)
                            {{ __('L\'annulation est intervenue hors délai : l\'acompte n\'est pas remboursable.') }}
                        @endif
                    </p>
                @else
                    <p class="text-sm text-slate-300 mb-3">
                        {{ __('Annulation gratuite jusqu\'au') }} <strong class="text-white">{{ $deadline->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</strong>
                        ({{ $reservation->freeCancellationDays() }} {{ __('jour') }}{{ $reservation->freeCancellationDays() > 1 ? 's' : '' }} {{ __('avant l\'arrivée) : l\'acompte vous est alors remboursé en totalité. Après cette date, l\'acompte n\'est plus remboursable.') }}
                    </p>
                    @if($reservation->clientCanCancel())
                        @php $refundNow = $reservation->clientRefundIfCancelledNow(); @endphp
                        <form method="POST" action="{{ route('visitor.reservations.cancel', $reservation) }}"
                              onsubmit="return confirm(@js($isPaid ? ($refundNow > 0 ? 'Annuler cette réservation ? Votre acompte de '.number_format($refundNow, 0, ',', ' ').' FCFA vous sera remboursé.' : 'Annuler cette réservation ? Le délai d\'annulation gratuite est dépassé : l\'acompte ne sera pas remboursé.') : 'Annuler cette réservation ?'));">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-rose-900/40 border border-slate-700 text-rose-300 text-sm font-semibold transition">
                                <i class="fas fa-ban text-xs"></i> {{ __('Annuler ma réservation') }}
                            </button>
                        </form>
                    @endif
                @endif
            </div>

            @php $dispute = \App\Models\Dispute::where('reservation_id', $reservation->id)->first(); @endphp
            @if($dispute || \App\Models\Dispute::canOpenFor($reservation))
            <div class="pt-2 border-t border-slate-800">
                <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Un problème sur place ?') }}</p>
                @if($dispute)
                    <div class="bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-3 text-sm">
                        <p class="text-white font-medium">{{ $dispute->reasonLabel() }} — <span class="{{ $dispute->isOpen() ? 'text-amber-300' : 'text-emerald-300' }}">{{ $dispute->statusLabel() }}</span></p>
                        <p class="text-slate-400 text-xs mt-1">{{ __('Signalé le') }} {{ $dispute->created_at->format('d/m/Y') }}</p>
                        @if($dispute->resolution)
                            <p class="text-slate-300 text-xs mt-2">{{ __('Décision :') }} {{ $dispute->resolution }}</p>
                        @endif
                    </div>
                @else
                    <details class="bg-slate-800/40 border border-slate-700 rounded-xl px-4 py-3">
                        <summary class="text-sm text-slate-200 cursor-pointer">{{ __('Signaler un problème avec cette réservation') }}</summary>
                        <form method="POST" action="{{ route('visitor.reservations.dispute', $reservation) }}" class="space-y-2 mt-3">
                            @csrf
                            <select name="reason" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                @foreach(\App\Models\Dispute::REASONS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <textarea name="description" rows="3" required minlength="20" maxlength="3000"
                                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                                      placeholder="{{ __('Décrivez ce qui s\'est passé (date, personnes rencontrées, échanges…)') }}"></textarea>
                            <p class="text-slate-500 text-[11px]">{{ __('Le paiement à l\'établissement est suspendu pendant l\'examen. Réponse sous 72 h.') }}</p>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600/80 hover:bg-rose-500 text-white text-sm font-semibold transition">
                                <i class="fas fa-flag text-xs"></i> {{ __('Envoyer le signalement') }}
                            </button>
                        </form>
                    </details>
                @endif
            </div>
            @endif

            <div class="pt-2 border-t border-slate-800">
                <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Contacter le prestataire') }}</p>
                <form method="POST" action="{{ route('visitor.conversations.store') }}" class="space-y-2">
                    @csrf
                    <input type="hidden" name="provider_id" value="{{ $reservation->provider_id }}">
                    <input type="hidden" name="context_type" value="reservation">
                    <input type="hidden" name="context_id" value="{{ $reservation->id }}">
                    <input type="hidden" name="subject" value="Réservation {{ $reservation->reference }}">
                    <textarea name="message" rows="2" required maxlength="4000"
                              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                              placeholder="{{ __('Une question sur cette réservation...') }}"></textarea>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold transition">
                        <i class="fas fa-paper-plane text-xs"></i> {{ __('Envoyer au prestataire') }}
                    </button>
                </form>
            </div>

            @if($isPaid)
                <div class="pt-2 border-t border-slate-800">
                    <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Fiche d\'enregistrement voyageur') }}</p>
                    @if($reservation->guestRegistration)
                        <a href="{{ $reservation->guestRegistrationUrl() }}" class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 text-sm font-medium transition">
                            <i class="fas fa-circle-check"></i> {{ __('Complétée — voir le récapitulatif') }}
                        </a>
                    @else
                        <a href="{{ $reservation->guestRegistrationUrl() }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold transition">
                            <i class="fas fa-id-card text-xs"></i> {{ __('Compléter ma fiche d\'enregistrement') }}
                        </a>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-800">
                    <a href="{{ $reservation->receiptUrl() }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold transition">
                        <i class="fas fa-receipt text-xs"></i> {{ __('Voir mon reçu') }}
                    </a>
                    <a href="{{ $reservation->receiptPdfUrl() }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-sm font-semibold transition">
                        <i class="fas fa-download text-xs"></i> {{ __('Télécharger le PDF') }}
                    </a>
                </div>
            @else
                <div class="pt-2 border-t border-slate-800">
                    <div class="px-4 py-3 bg-amber-900/20 border border-amber-700/30 rounded-xl text-amber-200 text-xs flex items-center gap-2">
                        <i class="fas fa-circle-info"></i>
                        {{ __('Le reçu sera disponible ici dès que l\'acompte de réservation sera payé.') }}
                    </div>
                </div>
            @endif

            @if($reservation->payments->isNotEmpty())
            <div class="pt-2 border-t border-slate-800">
                <p class="text-orange-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ __('Historique des tentatives de paiement') }}</p>
                <div class="space-y-2">
                    @foreach($reservation->payments as $payment)
                    <div class="flex items-center justify-between text-xs bg-slate-800/60 border border-slate-700 rounded-lg px-3 py-2">
                        <span class="text-slate-400">{{ $payment->created_at?->format('d/m/Y H:i') }}</span>
                        <span class="text-slate-300">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</span>
                        <span class="font-semibold {{ $payment->status === 'completed' ? 'text-emerald-400' : ($payment->status === 'failed' ? 'text-rose-400' : 'text-amber-400') }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-5">
        <a href="{{ route('visitor.reservations.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm transition">
            <i class="fas fa-arrow-left text-xs"></i> {{ __('Retour à mes réservations') }}
        </a>
    </div>
</div>
@endsection
