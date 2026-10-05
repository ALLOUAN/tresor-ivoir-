@extends('layouts.app')

@section('title', 'Réservation')
@section('page-title', 'Détail de la réservation')

@section('header-actions')
    <a href="{{ route('provider.reservations.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i>
        Retour à la liste
    </a>
@endsection

@section('content')

<div class="max-w-3xl space-y-6">

    @if(session('success'))
    <div class="px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
    </div>
    @endif

    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
        <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-slate-500 text-xs uppercase tracking-wide">Chambre réservée</p>
                <h2 class="text-white font-serif text-xl font-semibold mt-1 break-words">{{ $reservation->room_name }}</h2>
            </div>
            <div class="shrink-0 text-right text-xs text-slate-500">
                <p>{{ $reservation->created_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="p-5 sm:p-6 space-y-5">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                    <i class="fas fa-user"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $reservation->full_name }}</p>
                    <a href="mailto:{{ $reservation->email }}" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $reservation->email }}</a>
                    @if($reservation->phone)
                        <p class="text-slate-400 text-sm">{{ $reservation->phone }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Arrivée</p>
                    <p class="text-white text-sm font-medium">{{ $reservation->check_in?->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Départ</p>
                    <p class="text-white text-sm font-medium">{{ $reservation->check_out?->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Chambres</p>
                    <p class="text-white text-sm font-medium">{{ $reservation->rooms_count }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Personnes</p>
                    <p class="text-white text-sm font-medium">{{ $reservation->guests_count }}</p>
                </div>
            </div>

            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Montant estimé</p>
                <p class="text-white text-lg font-bold">
                    {{ $reservation->total_xof ? number_format($reservation->total_xof, 0, ',', ' ').' XOF' : 'Sur demande' }}
                    <span class="text-slate-500 text-xs font-normal">({{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }})</span>
                </p>
            </div>

            @if($reservation->message)
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-2">Message du client</p>
                <div class="rounded-lg border border-slate-800 bg-green-950/80 px-4 py-3 text-slate-200 text-sm whitespace-pre-wrap break-words">{{ $reservation->message }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($reservation->payment_status !== \App\Models\Reservation::PAYMENT_UNPAID)
    <div class="bg-green-900 border border-orange-500/25 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Paiement en ligne</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Statut</p>
                <p class="text-white text-sm font-medium">{{ $reservation->labelForPaymentStatus() }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Acompte</p>
                <p class="text-white text-sm font-medium">{{ number_format((int) $reservation->deposit_amount_xof) }} XOF</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Fiche d'enregistrement voyageur</h3>
        @if($reservation->guestRegistration)
            @php $reg = $reservation->guestRegistration; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Voyageur</p>
                    <p class="text-white text-sm font-medium">{{ $reg->full_name }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Document</p>
                    <p class="text-white text-sm font-medium">{{ $reg->labelForDocumentType() }} — {{ $reg->document_number }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Type de voyage</p>
                    <p class="text-white text-sm font-medium">{{ $reg->labelForTravelType() }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Téléphone</p>
                    <p class="text-white text-sm font-medium">{{ $reg->phone_country_code }} {{ $reg->phone_number }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Envoyée le</p>
                    <p class="text-white text-sm font-medium">{{ $reg->submitted_at?->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $reg->document_scan_front_url }}" target="_blank" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition"><i class="fas fa-id-card mr-1"></i>Pièce recto</a>
                <a href="{{ $reg->document_scan_back_url }}" target="_blank" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition"><i class="fas fa-id-card mr-1"></i>Pièce verso</a>
                <a href="{{ $reg->selfie_url }}" target="_blank" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition"><i class="fas fa-camera-retro mr-1"></i>Selfie</a>
                <a href="{{ $reg->signature_url }}" target="_blank" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition"><i class="fas fa-signature mr-1"></i>Signature</a>
            </div>
        @else
            <p class="text-slate-500 text-sm"><i class="fas fa-circle-info mr-1"></i>Le client n'a pas encore complété sa fiche d'enregistrement.</p>
        @endif
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-1">Statut de la réservation</h3>
        <p class="text-slate-500 text-xs mb-4">
            Statut actuel : <span class="text-slate-300 font-medium">{{ $reservation->labelForStatus() }}</span>
        </p>

        @if($reservation->status === \App\Models\Reservation::STATUS_CANCELLED)
            <p class="text-slate-500 text-sm">Cette réservation a été annulée.</p>
        @else
            <form method="POST" action="{{ route('provider.reservations.status', $reservation) }}" class="flex flex-wrap items-center gap-3">
                @csrf
                @method('PATCH')
                @if($reservation->status === \App\Models\Reservation::STATUS_NEW)
                    <button type="submit" name="status" value="{{ \App\Models\Reservation::STATUS_CONFIRMED }}"
                            class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                        <i class="fas fa-check"></i> Confirmer la réservation
                    </button>
                @endif
                <button type="submit" name="status" value="{{ \App\Models\Reservation::STATUS_CANCELLED }}"
                        onclick="return confirm('Annuler cette réservation ?');"
                        class="inline-flex items-center gap-2 text-rose-400 hover:text-rose-300 text-sm border border-rose-500/40 rounded-lg px-4 py-2.5 hover:bg-rose-500/10 transition">
                    <i class="fas fa-ban"></i> Annuler la réservation
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
