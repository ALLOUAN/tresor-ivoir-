{{-- Module d'achat « marché » d'une œuvre. Paramètre : $artwork. --}}
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $profile = \App\Models\ArtistProfile::forProvider($artwork->provider);
    $editions = $artwork->editions()->where('is_active', true)->get();
    $user = auth()->user();
    $myOffer = $user ? \App\Models\ArtworkOffer::where('artwork_id', $artwork->id)->where('user_id', $user->id)->whereIn('status', ['pending', 'countered', 'accepted'])->latest()->first() : null;
    $payableOffer = $myOffer && $myOffer->payablePrice() ? $myOffer : null;
    $options = $profile->deliveryOptions($artwork);
    $originalOk = $artwork->isAvailable();
    $reserved = ! $originalOk && $artwork->isReserved();
@endphp
<div id="acheter" class="rounded-2xl border border-black/10 bg-white p-4 sm:p-5 space-y-3">
    @if($reserved)
        <p class="rounded-xl bg-amber-50 border border-amber-200 px-3 py-2 text-sm text-amber-800"><i class="fas fa-lock mr-1"></i>{{ __('L\'original est réservé par un collectionneur (paiement en plusieurs fois).') }}</p>
    @endif

    @if(! $originalOk && $editions->isEmpty())
        <span class="inline-flex items-center gap-2 bg-slate-200 text-slate-500 px-6 py-3 rounded-xl font-semibold"><i class="fas fa-circle-xmark"></i> {{ __('Œuvre indisponible') }}</span>
        @if($profile->accepts_commissions)<p class="text-sm text-[#557466]">{{ __('Elle vous plaît ?') }} <a href="{{ route('artist.commission', $profile->slug) }}" class="font-semibold text-orange-700">{{ __('Commandez une œuvre sur mesure à :a', ['a' => $profile->display_name]) }}</a>.</p>@endif
    @elseif(! $user)
        <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl"><i class="fas fa-right-to-bracket"></i> {{ __('Se connecter pour acheter') }}</a>
        <p class="text-xs text-[#557466]">{{ __('Pas encore de compte ?') }} <a href="{{ route('register') }}" class="font-semibold text-orange-700">{{ __('Créer un compte') }}</a></p>
    @elseif($user->role !== 'visitor')
        <p class="text-sm text-[#557466]">{{ __('Les achats se font avec un compte visiteur.') }}</p>
    @else
        <form method="POST" action="{{ route('art.checkout', $artwork) }}" id="art-buy" data-quote="{{ route('art.quote', $artwork) }}" class="space-y-3">
            @csrf
            @if($payableOffer)
                <input type="hidden" name="offer_id" value="{{ $payableOffer->id }}">
                <p class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-2 text-sm text-emerald-800"><i class="fas fa-handshake mr-1"></i>{{ __('Offre acceptée : :a, à régler avant le :d.', ['a' => $fmt($payableOffer->payablePrice()), 'd' => $payableOffer->expires_at->format('d/m H:i')]) }}</p>
            @elseif($editions->isNotEmpty())
                <p class="text-xs font-semibold uppercase tracking-wide text-[#557466]">{{ __('Version') }}</p>
                <div class="grid gap-2">
                    @if($originalOk)
                        <label class="flex items-center justify-between gap-3 rounded-xl border border-black/10 px-3 py-2.5 cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                            <span class="flex items-center gap-2"><input type="radio" name="edition_id" value="" checked class="accent-orange-600"><span><b>{{ __('Original') }}</b> <small class="text-[#557466]">{{ __('pièce unique') }}</small></span></span><b class="whitespace-nowrap">{{ $fmt((int) $artwork->price_xof) }}</b></label>
                    @endif
                    @foreach($editions as $e)
                        <label class="flex items-center justify-between gap-3 rounded-xl border border-black/10 px-3 py-2.5 cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 {{ $e->remaining() < 1 ? 'opacity-50' : '' }}">
                            <span class="flex items-center gap-2"><input type="radio" name="edition_id" value="{{ $e->id }}" @disabled($e->remaining() < 1) @checked(! $originalOk && $loop->first && $e->remaining() > 0) class="accent-orange-600">
                                <span><b>{{ $e->label }}</b> <small class="text-[#557466]">@if($e->width_cm){{ (float) $e->width_cm }} × {{ (float) $e->height_cm }} cm · @endif{{ __('édition de :n', ['n' => $e->edition_size]) }} · {{ $e->remaining() < 1 ? __('épuisée') : trans_choice('reste :count exemplaire|reste :count exemplaires', $e->remaining()) }}</small></span></span>
                            <b class="whitespace-nowrap">{{ $fmt($e->price_xof) }}</b></label>
                    @endforeach
                </div>
            @endif

            <label class="block text-xs font-semibold uppercase tracking-wide text-[#557466]" for="art-mode">{{ __('Remise') }}</label>
            <select id="art-mode" name="delivery_mode" class="field-input">
                @foreach($options as $k => $o)<option value="{{ $k }}" @selected(old('delivery_mode') === $k)>{{ $o['label'] }}{{ $o['fee'] ? ' — '.$fmt($o['fee']) : ' — '.__('gratuit') }}</option>@endforeach
            </select>
            <div id="art-address" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <input name="delivery_address" maxlength="255" value="{{ old('delivery_address') }}" placeholder="{{ __('Adresse de livraison') }}" class="field-input sm:col-span-2" aria-label="{{ __('Adresse de livraison') }}">
                <input name="delivery_city" maxlength="120" value="{{ old('delivery_city') }}" placeholder="{{ __('Ville') }}" class="field-input" aria-label="{{ __('Ville') }}">
                <label class="sm:col-span-3 flex items-center gap-2 text-sm"><input type="checkbox" name="insurance" value="1" @checked(old('insurance', true)) class="accent-orange-600"> {{ __('Assurer le transport (:p % de la valeur)', ['p' => rtrim(rtrim(number_format($profile->insurance_percent, 2, ',', ''), '0'), ',')]) }}</label>
            </div>

            @if($artwork->layaway_allowed && $originalOk && ! $payableOffer)
                <p class="text-xs font-semibold uppercase tracking-wide text-[#557466]">{{ __('Paiement') }}</p>
                <div class="grid grid-cols-2 gap-2">
                    <label class="rounded-xl border border-black/10 px-3 py-2 cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 text-sm"><input type="radio" name="plan" value="full" checked class="accent-orange-600 mr-1">{{ __('En une fois') }}</label>
                    <label class="rounded-xl border border-black/10 px-3 py-2 cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 text-sm"><input type="radio" name="plan" value="layaway" class="accent-orange-600 mr-1">{{ __('En 3 fois') }} <small class="text-[#557466] block">{{ __('30 % pour réserver') }}</small></label>
                </div>
            @endif
            <input type="tel" name="phone" required maxlength="30" value="{{ old('phone', $user->phone) }}" placeholder="{{ __('Téléphone') }}" class="field-input" aria-label="{{ __('Téléphone') }}">
            <div id="art-sum" class="rounded-xl bg-[#F3F8F5] border border-black/10 px-3 py-2.5 text-sm space-y-1" aria-live="polite"></div>
            @if($errors->art->any())<p class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $errors->art->first() }}</p>@endif
            <div class="grid grid-cols-2 gap-2">
                <button name="payment_method" value="cinetpay" class="btn-primary py-3 rounded-xl text-sm"><i class="fas fa-mobile-screen mr-1"></i>{{ __('Mobile Money / carte') }}</button>
                <button name="payment_method" value="wallet" class="py-3 rounded-xl text-sm font-bold border border-black/15 hover:border-orange-500">{{ __('Avec mon solde') }}</button>
            </div>
        </form>

        @if($artwork->accepts_offers && $originalOk && ! $payableOffer)
            <details class="rounded-xl border border-black/10 px-3 py-2.5" @if($errors->art->has('offer')) open @endif>
                <summary class="cursor-pointer text-sm font-semibold"><i class="fas fa-handshake text-orange-600 mr-1"></i>{{ __('Faire une offre') }}</summary>
                @if($myOffer)
                    <p class="text-sm mt-2">{{ __('Votre offre : :a — :s', ['a' => $fmt($myOffer->amount_xof), 's' => $myOffer->statusLabel()]) }}</p>
                    @if($myOffer->status === 'countered' && ! $myOffer->expires_at?->isPast())
                        <p class="text-sm mt-1">{{ __('L\'artiste propose :a (valable jusqu\'au :d).', ['a' => $fmt((int) $myOffer->counter_amount_xof), 'd' => $myOffer->expires_at->format('d/m H:i')]) }}</p>
                        <form method="POST" action="{{ route('visitor.art-offers.accept', $myOffer) }}" class="mt-2 inline">@csrf<button class="btn-primary text-xs px-3 py-2 rounded-lg">{{ __('Accepter :a', ['a' => $fmt((int) $myOffer->counter_amount_xof)]) }}</button></form>
                    @endif
                    <form method="POST" action="{{ route('visitor.art-offers.withdraw', $myOffer) }}" class="mt-2 inline">@csrf<button class="text-xs text-[#557466] underline">{{ __('Retirer mon offre') }}</button></form>
                @else
                    <form method="POST" action="{{ route('art.offer', $artwork) }}" class="mt-2 space-y-2">
                        @csrf
                        <input type="number" name="amount_xof" required min="{{ (int) round($artwork->price_xof * 0.5) }}" max="{{ (int) $artwork->price_xof - 1 }}" value="{{ old('amount_xof') }}" placeholder="{{ __('Votre prix en FCFA') }}" class="field-input" aria-label="{{ __('Votre prix en FCFA') }}">
                        <input name="message" maxlength="500" value="{{ old('message') }}" placeholder="{{ __('Un mot pour l\'artiste (facultatif)') }}" class="field-input" aria-label="{{ __('Un mot pour l\'artiste') }}">
                        <button class="text-sm font-bold px-4 py-2 rounded-xl border border-black/15 hover:border-orange-500">{{ __('Envoyer l\'offre') }}</button>
                        <p class="text-[11px] text-[#557466]">{{ __('Si l\'artiste accepte, vous avez :h h pour régler au prix convenu. L\'œuvre reste en vente en attendant.', ['h' => \App\Models\ArtworkOffer::VALID_HOURS]) }}</p>
                    </form>
                @endif
            </details>
        @endif
    @endif

    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs text-[#24503D] pt-1">
        <li><i class="fas fa-certificate text-orange-600 w-4"></i>{{ __('Certificat d\'authenticité vérifiable') }}</li>
        @if($profile->returns_accepted)<li><i class="fas fa-rotate-left text-orange-600 w-4"></i>{{ __('Retour accepté sous :n jours après réception', ['n' => \App\Models\ArtworkOrder::RETURN_DAYS]) }}</li>@endif
        <li><i class="fas fa-shield-halved text-orange-600 w-4"></i>{{ __('Paiement sécurisé : l\'artiste est payé après la réception') }}</li>
        <li><i class="fas fa-truck text-orange-600 w-4"></i>{{ __('Suivi d\'expédition et assurance en option') }}</li>
    </ul>
</div>
<script>
(function () {
    const form = document.getElementById('art-buy'); if (!form) return;
    const sum = document.getElementById('art-sum'), addr = document.getElementById('art-address');
    const fmt = n => Number(n).toLocaleString('fr-FR') + ' FCFA';
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const T = {{ \Illuminate\Support\Js::from(['total' => __('Total'), 'now' => __('À payer maintenant'), 'inst' => __('Échéance :n le :d')]) }};
    function refresh() {
        addr.classList.toggle('hidden', form.querySelector('[name=delivery_mode]').value === 'pickup');
        const p = new URLSearchParams(new FormData(form)); p.delete('_token'); p.delete('phone'); p.delete('delivery_address'); p.delete('delivery_city');
        if (!form.querySelector('[name=insurance]').checked) p.delete('insurance');
        fetch(form.dataset.quote + '?' + p.toString(), { headers: { Accept: 'application/json' } }).then(r => r.json()).then(q => {
            if (!q.ok) { sum.innerHTML = '<p class="text-rose-700">' + esc(q.message) + '</p>'; return; }
            sum.innerHTML = q.lines.map(l => '<p class="flex justify-between gap-2"><span>' + esc(l.label) + '</span><span>' + fmt(l.amount_xof) + '</span></p>').join('')
                + '<p class="flex justify-between gap-2 font-bold border-t border-black/10 pt-1"><span>' + T.total + '</span><span>' + fmt(q.total) + '</span></p>'
                + (q.installments.length ? q.installments.map(i => '<p class="flex justify-between gap-2 ' + (i.n === 1 ? 'font-bold text-orange-700' : 'text-[#557466]') + '"><span>' + (i.n === 1 ? T.now : T.inst.replace(':n', i.n).replace(':d', i.due_on.split('-').reverse().join('/'))) + '</span><span>' + fmt(i.amount_xof) + '</span></p>').join('') : '');
        });
    }
    form.addEventListener('change', refresh); refresh();
})();
</script>
