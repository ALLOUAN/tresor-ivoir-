@extends('layouts.app')

@section('title', 'Paiement — ' . strtoupper($plan->code))
@section('page-title', 'Finaliser le paiement')

@section('content')
<div class="max-w-xl space-y-5">

    {{-- Plan summary --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-500/15 flex items-center justify-center shrink-0">
                <i class="fas fa-gem text-amber-400 text-sm"></i>
            </div>
            <div>
                <p class="text-white font-bold">{{ strtoupper($plan->code) }} — {{ $plan->name_fr }}</p>
                <p class="text-slate-400 text-xs">{{ $provider->name }}</p>
            </div>
        </div>
    </div>

    @if($cynetPayConfigured)
    {{-- ── MODE CYNETPAY RÉEL ───────────────────────────────────────────── --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-5">

        {{-- Billing cycle --}}
        <div>
            <label class="block text-slate-300 text-xs font-medium mb-2">Cycle de facturation</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cycle-option cursor-pointer">
                    <input type="radio" name="billing_cycle" value="monthly" class="sr-only" checked>
                    <div class="cycle-card border border-amber-500 bg-amber-500/8 rounded-lg p-3 text-center transition hover:border-amber-500/70">
                        <p class="text-white font-bold text-sm">Mensuel</p>
                        <p class="text-amber-400 font-semibold mt-0.5">{{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA</p>
                        <p class="text-slate-500 text-xs">/ mois</p>
                    </div>
                </label>
                <label class="cycle-option cursor-pointer">
                    <input type="radio" name="billing_cycle" value="yearly" class="sr-only">
                    <div class="cycle-card border border-slate-700 rounded-lg p-3 text-center transition hover:border-amber-500/50">
                        <p class="text-white font-bold text-sm">Annuel</p>
                        <p class="text-amber-400 font-semibold mt-0.5">{{ number_format((float) $plan->price_yearly, 0, ',', ' ') }} FCFA</p>
                        <p class="text-slate-500 text-xs">/ an</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Canal de paiement --}}
        <div>
            <label class="block text-slate-300 text-xs font-medium mb-2">Canal de paiement</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($channels as $ch)
                <label class="channel-option cursor-pointer">
                    <input type="radio" name="channel" value="{{ $ch['code'] }}" class="sr-only"
                           {{ $loop->first ? 'checked' : '' }}>
                    <div class="channel-card border {{ $loop->first ? 'border-amber-500 bg-amber-500/8' : 'border-slate-700' }} rounded-lg p-2.5 transition hover:border-amber-500/50">
                        <div class="flex items-center gap-2">
                            <i class="fas {{ $ch['icon'] }} text-amber-400 text-xs w-4 text-center shrink-0"></i>
                            <div class="min-w-0">
                                <p class="text-white text-xs font-semibold leading-tight">{{ $ch['label'] }}</p>
                                <p class="text-slate-500 text-[10px] truncate">{{ $ch['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Informations client --}}
        <div class="space-y-3">
            <label class="block text-slate-300 text-xs font-medium">Informations de facturation</label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <input type="text" id="customer_name" placeholder="Prénom"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-amber-500/50">
                </div>
                <div>
                    <input type="text" id="customer_surname" placeholder="Nom"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-amber-500/50">
                </div>
            </div>
            <input type="email" id="customer_email" value="{{ auth()->user()->email ?? '' }}" placeholder="Adresse e-mail"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-amber-500/50">
            <input type="tel" id="customer_phone" placeholder="Téléphone (ex : +225 07 00 00 00 00)"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-amber-500/50">
        </div>

        {{-- Code promo --}}
        <div>
            <label class="block text-slate-300 text-xs font-medium mb-2">Code promo <span class="text-slate-500">(facultatif)</span></label>
            <div class="flex gap-2">
                <input type="text" id="promo-input" placeholder="EX : IVOIRE20"
                       class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 uppercase placeholder:normal-case placeholder:text-slate-500 focus:outline-none focus:border-amber-500/50 tracking-wider">
                <button type="button" id="promo-btn"
                        class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition">
                    Appliquer
                </button>
            </div>
            <p id="promo-message" class="mt-1.5 text-xs hidden"></p>
        </div>

        {{-- Récapitulatif prix --}}
        <div class="bg-slate-800/60 rounded-lg p-4 border border-slate-700 space-y-2 text-sm">
            <div class="flex justify-between text-slate-300">
                <span>Sous-total</span>
                <span id="summary-base">—</span>
            </div>
            <div id="summary-discount-row" class="flex justify-between text-emerald-400 hidden">
                <span>Réduction (code promo)</span>
                <span id="summary-discount">— 0 FCFA</span>
            </div>
            <div class="flex justify-between text-white font-bold border-t border-slate-700 pt-2 mt-1">
                <span>Total à payer</span>
                <span id="summary-total">—</span>
            </div>
        </div>

        {{-- CGU --}}
        <label class="flex items-start gap-2.5 cursor-pointer select-none">
            <input type="checkbox" id="cgu-check" class="mt-0.5 accent-amber-500 w-4 h-4 shrink-0">
            <span class="text-slate-400 text-xs leading-relaxed">
                J'accepte les
                <a href="#" class="text-amber-400 underline underline-offset-2 hover:text-amber-300">conditions générales d'utilisation</a>
                et confirme que les informations saisies sont exactes.
            </span>
        </label>

        {{-- Erreurs --}}
        <div id="pay-error" class="hidden bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm"></div>

        {{-- Bouton de paiement --}}
        <button type="button" id="pay-btn"
                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-lg text-sm transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-lock text-xs"></i>
            <span id="pay-btn-label">Payer via CinetPay</span>
        </button>

        <p class="text-center text-slate-500 text-[11px]">
            <i class="fas fa-shield-halved text-amber-400/60 mr-1"></i>
            Paiement sécurisé par CinetPay — vos données bancaires ne sont jamais transmises à notre serveur.
        </p>
    </div>

    @else
    {{-- ── MODE FALLBACK (CinetPay non configuré) ──────────────────────── --}}
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3 text-amber-300 text-sm flex items-start gap-2.5">
        <i class="fas fa-triangle-exclamation mt-0.5 shrink-0"></i>
        <span>La passerelle CinetPay n'est pas encore configurée. Utilisez le mode de simulation ci-dessous pour tester le flux de paiement.</span>
    </div>

    <form method="POST" action="{{ route('subscriptions.process-offline', $plan) }}" class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-5">
        @csrf

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-slate-300 text-xs font-medium mb-1.5">Prénom <span class="text-red-400">*</span></label>
                <input type="text" name="first_name" required maxlength="100" value="{{ old('first_name', auth()->user()->first_name) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-amber-500/50">
            </div>
            <div>
                <label class="block text-slate-300 text-xs font-medium mb-1.5">Nom <span class="text-red-400">*</span></label>
                <input type="text" name="last_name" required maxlength="100" value="{{ old('last_name', auth()->user()->last_name) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-amber-500/50">
            </div>
        </div>

        <div>
            <label class="block text-slate-300 text-xs font-medium mb-1.5">Référence / numéro de paiement <span class="text-red-400">*</span></label>
            <input type="text" name="payment_reference" required maxlength="120" value="{{ old('payment_reference') }}"
                   placeholder="Ex. : ID transaction Orange Money, Wave…"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:border-amber-500/50">
            @error('payment_reference')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-slate-300 text-xs font-medium mb-2">Cycle de facturation</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cycle-option cursor-pointer">
                    <input type="radio" name="billing_cycle" value="monthly" class="sr-only" checked>
                    <div class="cycle-card border border-amber-500 bg-amber-500/8 rounded-lg p-3 text-center transition">
                        <p class="text-white font-bold text-sm">Mensuel</p>
                        <p class="text-amber-400 font-semibold mt-0.5">{{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA</p>
                        <p class="text-slate-500 text-xs">/ mois</p>
                    </div>
                </label>
                <label class="cycle-option cursor-pointer">
                    <input type="radio" name="billing_cycle" value="yearly" class="sr-only">
                    <div class="cycle-card border border-slate-700 rounded-lg p-3 text-center transition hover:border-amber-500/50">
                        <p class="text-white font-bold text-sm">Annuel</p>
                        <p class="text-amber-400 font-semibold mt-0.5">{{ number_format((float) $plan->price_yearly, 0, ',', ' ') }} FCFA</p>
                        <p class="text-slate-500 text-xs">/ an</p>
                    </div>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-slate-300 text-xs font-medium mb-2">Méthode de paiement (simulation)</label>
            <select name="method" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-amber-500/50">
                <option value="orange_money">Orange Money</option>
                <option value="mtn_momo">MTN MoMo</option>
                <option value="wave">Wave</option>
                <option value="moov_money">Moov Money</option>
                <option value="card">Carte bancaire</option>
            </select>
            <input type="hidden" name="gateway" value="cinetpay">
        </div>

        <div>
            <label class="block text-slate-300 text-xs font-medium mb-2">Code promo <span class="text-slate-500">(facultatif)</span></label>
            <div class="flex gap-2">
                <input type="text" id="promo-input" name="promo_code" placeholder="EX : IVOIRE20"
                       class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100 uppercase placeholder:normal-case placeholder:text-slate-500 focus:outline-none focus:border-amber-500/50 tracking-wider">
                <button type="button" id="promo-btn"
                        class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition">
                    Appliquer
                </button>
            </div>
            <p id="promo-message" class="mt-1.5 text-xs hidden"></p>
        </div>

        <div class="bg-slate-800/60 rounded-lg p-4 border border-slate-700 space-y-2 text-sm">
            <div class="flex justify-between text-slate-300">
                <span>Sous-total</span>
                <span id="summary-base">—</span>
            </div>
            <div id="summary-discount-row" class="flex justify-between text-emerald-400 hidden">
                <span>Réduction (code promo)</span>
                <span id="summary-discount">— 0 FCFA</span>
            </div>
            <div class="flex justify-between text-white font-bold border-t border-slate-700 pt-2 mt-1">
                <span>Total à payer</span>
                <span id="summary-total">—</span>
            </div>
        </div>

        <button type="submit"
                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-lg text-sm transition flex items-center justify-center gap-2">
            <i class="fas fa-vial text-xs"></i>
            Simuler le paiement (mode test)
        </button>
    </form>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    const planId      = {{ $plan->id }};
    const priceM      = {{ (float) $plan->price_monthly }};
    const priceY      = {{ (float) $plan->price_yearly }};
    const validateUrl = '{{ route('provider.billing.promo.validate') }}';
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;
    const cynetConfigured = {{ $cynetPayConfigured ? 'true' : 'false' }};

    let discountAmount  = 0;
    let currentCycle    = 'monthly';
    let currentChannel  = 'ALL';
    let appliedPromoCode = '';

    function fmt(n) {
        return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';
    }

    function baseAmount() {
        return currentCycle === 'yearly' ? priceY : priceM;
    }

    function updateSummary() {
        const base  = baseAmount();
        const total = Math.max(0, base - discountAmount);
        const bEl   = document.getElementById('summary-base');
        const tEl   = document.getElementById('summary-total');
        if (bEl) bEl.textContent = fmt(base);
        if (tEl) tEl.textContent = fmt(total);
    }

    // ── Cycle selection ──
    document.querySelectorAll('.cycle-option input[type=radio]').forEach(radio => {
        radio.addEventListener('change', () => {
            currentCycle   = radio.value;
            discountAmount = 0;
            appliedPromoCode = '';
            const promoInput = document.getElementById('promo-input');
            const promoMsg   = document.getElementById('promo-message');
            const discRow    = document.getElementById('summary-discount-row');
            if (promoInput) promoInput.value = '';
            if (promoMsg)   promoMsg.classList.add('hidden');
            if (discRow)    discRow.classList.add('hidden');
            document.querySelectorAll('.cycle-card').forEach(c => {
                c.classList.remove('border-amber-500', 'bg-amber-500/8');
                c.classList.add('border-slate-700');
            });
            radio.closest('.cycle-option').querySelector('.cycle-card').classList.add('border-amber-500', 'bg-amber-500/8');
            radio.closest('.cycle-option').querySelector('.cycle-card').classList.remove('border-slate-700');
            updateSummary();
        });
        if (radio.checked) {
            currentCycle = radio.value;
            radio.closest('.cycle-option').querySelector('.cycle-card').classList.add('border-amber-500', 'bg-amber-500/8');
            radio.closest('.cycle-option').querySelector('.cycle-card').classList.remove('border-slate-700');
        }
    });

    // ── Channel selection ──
    document.querySelectorAll('.channel-option input[type=radio]').forEach(radio => {
        radio.addEventListener('change', () => {
            currentChannel = radio.value;
            document.querySelectorAll('.channel-card').forEach(c => {
                c.classList.remove('border-amber-500', 'bg-amber-500/8');
                c.classList.add('border-slate-700');
            });
            radio.closest('.channel-option').querySelector('.channel-card').classList.add('border-amber-500', 'bg-amber-500/8');
            radio.closest('.channel-option').querySelector('.channel-card').classList.remove('border-slate-700');
        });
        if (radio.checked) currentChannel = radio.value;
    });

    updateSummary();

    // ── Promo validation ──
    const promoBtn = document.getElementById('promo-btn');
    if (promoBtn) {
        promoBtn.addEventListener('click', async () => {
            const code = (document.getElementById('promo-input').value || '').trim();
            const msg  = document.getElementById('promo-message');

            if (! code) {
                msg.textContent = 'Saisissez un code promo.';
                msg.className   = 'mt-1.5 text-xs text-amber-400';
                msg.classList.remove('hidden');
                return;
            }

            promoBtn.disabled    = true;
            promoBtn.textContent = '…';

            try {
                const res  = await fetch(validateUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ code, plan_id: planId, billing_cycle: currentCycle }),
                });
                const data = await res.json();

                if (data.valid) {
                    discountAmount   = data.discount_amount;
                    appliedPromoCode = code;
                    document.getElementById('summary-discount').textContent = '— ' + fmt(discountAmount);
                    document.getElementById('summary-discount-row').classList.remove('hidden');
                    msg.textContent = '✓ ' + data.message;
                    msg.className   = 'mt-1.5 text-xs text-emerald-400';
                } else {
                    discountAmount   = 0;
                    appliedPromoCode = '';
                    document.getElementById('summary-discount-row').classList.add('hidden');
                    msg.textContent = data.message;
                    msg.className   = 'mt-1.5 text-xs text-red-400';
                }
            } catch {
                msg.textContent = 'Erreur réseau. Réessayez.';
                msg.className   = 'mt-1.5 text-xs text-red-400';
            }

            msg.classList.remove('hidden');
            updateSummary();
            promoBtn.disabled    = false;
            promoBtn.textContent = 'Appliquer';
        });

        const promoInput = document.getElementById('promo-input');
        if (promoInput) {
            promoInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); promoBtn.click(); }
            });
        }
    }

    // ── CynetPay AJAX payment (mode réel) ──
    if (cynetConfigured) {
        const payBtn   = document.getElementById('pay-btn');
        const payLabel = document.getElementById('pay-btn-label');
        const payError = document.getElementById('pay-error');
        const initiateUrl = '{{ route('provider.payment.cynetpay.initiate') }}';

        payBtn.addEventListener('click', async () => {
            payError.classList.add('hidden');

            const name    = document.getElementById('customer_name').value.trim();
            const surname = document.getElementById('customer_surname').value.trim();
            const email   = document.getElementById('customer_email').value.trim();
            const phone   = document.getElementById('customer_phone').value.trim();
            const cgu     = document.getElementById('cgu-check').checked;

            if (! name || ! surname || ! email || ! phone) {
                payError.textContent = 'Veuillez remplir toutes les informations de facturation.';
                payError.classList.remove('hidden');
                return;
            }
            if (! cgu) {
                payError.textContent = 'Veuillez accepter les conditions générales d\'utilisation.';
                payError.classList.remove('hidden');
                return;
            }

            payBtn.disabled = true;
            payLabel.textContent = 'Redirection en cours…';

            try {
                const res  = await fetch(initiateUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        plan_id:          planId,
                        billing_cycle:    currentCycle,
                        channel:          currentChannel,
                        customer_name:    name,
                        customer_surname: surname,
                        customer_email:   email,
                        customer_phone:   phone,
                        promo_code:       appliedPromoCode || undefined,
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    if (data.free) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.href = data.payment_url;
                    }
                } else {
                    payError.textContent = data.message || 'Une erreur est survenue. Veuillez réessayer.';
                    payError.classList.remove('hidden');
                    payBtn.disabled = false;
                    payLabel.textContent = 'Payer via CinetPay';
                }
            } catch {
                payError.textContent = 'Erreur réseau. Vérifiez votre connexion et réessayez.';
                payError.classList.remove('hidden');
                payBtn.disabled = false;
                payLabel.textContent = 'Payer via CinetPay';
            }
        });
    }
})();
</script>
@endpush
