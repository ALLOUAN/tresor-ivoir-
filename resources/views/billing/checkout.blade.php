@php
    $checkoutLayout = request()->routeIs('subscriptions.checkout') ? 'layouts.public-checkout' : 'layouts.app';
    $isPublicCheckout = request()->routeIs('subscriptions.checkout');
    // Achat de plusieurs secteurs : un forfait par secteur, un seul paiement (montant = somme).
    $bundleItems = ($bundleItems ?? collect())->isNotEmpty() ? $bundleItems : collect([['plan' => $plan, 'sector' => null]]);
    $bundleSector = $bundleSector ?? null;
    $bundleAvec = \App\Support\PlanBundle::encode($bundleItems->slice(1));
    $bundleMonthly = \App\Support\PlanBundle::total($bundleItems, 'monthly');
    $bundleYearly = \App\Support\PlanBundle::total($bundleItems, 'yearly');
@endphp
@extends($checkoutLayout)

@section('title', 'Paiement — ' . strtoupper($plan->code))
@section('page-title', __('Finaliser le paiement'))

@section('content')
@if($isPublicCheckout)
    @php
        $u = auth()->user();
        $monthlyPrice = $bundleMonthly;
        $yearlyPrice = $bundleYearly;
    @endphp
    <style>
        :root {
            --ti-gold: #EB6E00;
            --ti-gold-soft: rgba(235,110,0, 0.18);
            --ti-sunset: #EB6E00;
            --ti-palm: #00874F;
            --ti-lagoon: #00874F;
        }
        .checkout-glass {
            background:#D8E9E0;
            border: 1px solid #8AA99B;
            box-shadow: 0 12px 32px rgba(14,59,42,0.06);
        }
        .checkout-title-icon {
            box-shadow: 0 0 0 1px var(--ti-gold-soft), 0 8px 22px rgba(235,110,0,0.12);
        }
        .checkout-input {
            background-color:#D8E9E0 !important;
            color: #0E3B2A !important;
            border-color: #8AA99B !important;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .checkout-input::placeholder {
            color: #557466 !important;
        }
        .checkout-input:focus {
            box-shadow: 0 0 0 3px rgba(0,135,79,0.15);
            transform: translateY(-1px);
        }
        .checkout-input:-webkit-autofill,
        .checkout-input:-webkit-autofill:hover,
        .checkout-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #0E3B2A !important;
            box-shadow: 0 0 0px 1000px #ffffff inset !important;
            transition: background-color 9999s ease-out 0s;
        }
        select.checkout-input option {
            background:#D8E9E0;
            color: #0E3B2A;
        }
        .checkout-submit {
            background: var(--ti-sunset);
            color: #0E3B2A;
            box-shadow: 0 10px 28px rgba(235,110,0,0.22);
        }
        .checkout-submit:hover {
            filter: saturate(1.08) brightness(1.03);
        }
        .checkout-badge-premium {
            border-color: rgba(235,110,0,0.35);
            background: rgba(235,110,0, 0.12);
            color: #0E3B2A;
        }
        .checkout-total {
            color: #B85600;
        }
    </style>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8 items-start">
        {{-- Colonne gauche : formulaire --}}
        <div class="lg:col-span-8 space-y-6">
            @if($cinetPayConfigured)
                <div class="checkout-glass rounded-2xl p-5 sm:p-6 space-y-6">
                    <div class="flex items-center gap-2 text-white font-bold text-lg">
                        <span class="checkout-title-icon inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gold-500/15 text-gold-400"><i class="fas fa-users"></i></span>
                        {{ __('Informations de facturation') }}
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Cycle de facturation') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cycle-option cursor-pointer">
                                <input type="radio" name="billing_cycle" value="monthly" class="sr-only" checked>
                                <div class="cycle-card rounded-xl border-2 border-[#EB6E00] bg-[#EB6E00]/10 p-3 text-center transition">
                                    <p class="text-sm font-bold text-white">{{ __('Mensuel') }}</p>
                                    <p class="mt-0.5 text-sm font-semibold text-[#B85600]">{{ number_format($monthlyPrice, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('/ mois') }}</p>
                                </div>
                            </label>
                            <label class="cycle-option cursor-pointer">
                                <input type="radio" name="billing_cycle" value="yearly" class="sr-only">
                                <div class="cycle-card rounded-xl border border-white/10 bg-dark-900 p-3 text-center transition hover:border-[#EB6E00]/40">
                                    <p class="text-sm font-bold text-white">{{ __('Annuel') }}</p>
                                    <p class="mt-0.5 text-sm font-semibold text-[#B85600]">{{ number_format($yearlyPrice, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('/ an') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-200">{{ __('Canal de paiement') }}</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach($channels as $ch)
                                <label class="channel-option cursor-pointer">
                                    <input type="radio" name="channel" value="{{ $ch['code'] }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                                    <div class="channel-card rounded-xl border p-2.5 transition {{ $loop->first ? 'border-[#EB6E00] bg-[#EB6E00]/10' : 'border-white/10 bg-dark-900' }}">
                                        <div class="flex items-center gap-2">
                                            <i class="fas {{ $ch['icon'] }} w-4 shrink-0 text-center text-xs text-[#EB6E00]"></i>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold leading-tight text-white">{{ $ch['label'] }}</p>
                                                <p class="truncate text-[10px] text-gray-500">{{ $ch['desc'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Prénom') }} <span class="text-red-400">*</span></label>
                                <input type="text" id="customer_name" value="{{ e($u->first_name) }}" placeholder="{{ __('Prénom') }}"
                                       class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-[#00874F]/60 focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Nom') }} <span class="text-red-400">*</span></label>
                                <input type="text" id="customer_surname" value="{{ e($u->last_name) }}" placeholder="{{ __('Nom') }}"
                                       class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-[#00874F]/60 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Email') }} <span class="text-red-400">*</span></label>
                            <input type="email" id="customer_email" value="{{ e($u->email) }}" readonly
                                   class="w-full cursor-not-allowed rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-gray-300">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Téléphone Mobile Money') }}</label>
                            <div class="flex">
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-l-xl border border-r-0 border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-gray-300">🇨🇮 +225</span>
                                <input type="tel" id="customer_phone" value="{{ old('billing_phone', $u->phone ? preg_replace('/^\+225/', '', $u->phone) : '') }}" placeholder="07 08 75 85 00"
                                       class="checkout-input min-w-0 flex-1 rounded-r-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-[#00874F]/60 focus:outline-none">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Format : 0707267572 ou 07 07 26 75 72') }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-gray-200">
                            <span class="checkout-title-icon inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#EB6E00]/15 text-[#B85600]"><i class="fas fa-wallet"></i></span>
                            {{ __('Moyen de paiement') }}
                        </label>
                        <p class="mb-2 text-xs text-gray-500">{{ __('Sélectionnez le canal utilisé pour le paiement en ligne.') }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Code promo') }} <span class="font-normal text-gray-500">{{ __('(facultatif)') }}</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="promo-input" placeholder="{{ __('EX : IVOIRE20') }}"
                                   class="checkout-input flex-1 rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm uppercase tracking-wider text-white placeholder:normal-case placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                            <button type="button" id="promo-btn" class="rounded-xl bg-[#00874F]/20 px-4 py-2.5 text-sm font-medium text-emerald-200 transition hover:bg-[#00874F]/30">{{ __('Appliquer') }}</button>
                        </div>
                        <p id="promo-message" class="mt-1.5 hidden text-xs"></p>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 select-none">
                        <input type="checkbox" id="cgu-check" class="mt-1 h-4 w-4 shrink-0 rounded border-white/20 bg-dark-900 text-[#00874F] focus:ring-[#00874F]/50">
                        <span class="text-sm leading-relaxed text-gray-400">
                            {{ __('J\'accepte les') }} <a href="#" class="font-medium text-[#B85600] underline hover:text-[#B85600]">{{ __('conditions générales') }}</a>
                            {{ __('et la') }} <a href="#" class="font-medium text-[#B85600] underline hover:text-[#B85600]">{{ __('politique de confidentialité') }}</a>.
                        </span>
                    </label>

                    <div id="pay-error" class="hidden rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200"></div>

                    <button type="button" id="pay-btn"
                            class="checkout-submit flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-lock text-xs"></i>
                        <span id="pay-btn-label">{{ __('Payer via CinetPay') }}</span>
                    </button>
                </div>
            @else
                <div class="rounded-lg border border-orange-500/35 bg-orange-500/10 px-4 py-3 text-sm text-orange-100">
                    <i class="fas fa-triangle-exclamation mr-2"></i>
                    {{ __('La passerelle CinetPay n\'est pas encore configurée. Utilisez le mode de simulation ci-dessous pour tester le flux de paiement.') }}
                </div>

                <form method="POST" action="{{ route('subscriptions.process-offline', $plan) }}" class="checkout-glass space-y-6 rounded-2xl p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="gateway" value="cinetpay">
                    <input type="hidden" name="sector" value="{{ $bundleSector }}"><input type="hidden" name="avec" value="{{ $bundleAvec }}">

                    <div class="flex items-center gap-2 text-white font-bold text-lg">
                        <span class="checkout-title-icon inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gold-500/15 text-gold-400"><i class="fas fa-users"></i></span>
                        {{ __('Informations de facturation') }}
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Prénom') }} <span class="text-red-400">*</span></label>
                            <input type="text" name="first_name" required maxlength="100" value="{{ old('first_name', $u->first_name) }}"
                                   class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Nom') }} <span class="text-red-400">*</span></label>
                            <input type="text" name="last_name" required maxlength="100" value="{{ old('last_name', $u->last_name) }}"
                                   class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Email') }}</label>
                        <input type="email" readonly value="{{ e($u->email) }}"
                               class="w-full cursor-not-allowed rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-gray-300">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Téléphone Mobile Money') }}</label>
                        <div class="flex">
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-l-xl border border-r-0 border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-gray-300">🇨🇮 +225</span>
                            <input type="tel" name="billing_phone" value="{{ old('billing_phone', $u->phone ? preg_replace('/^\+225/', '', $u->phone) : '') }}" placeholder="07 08 75 85 00"
                                   class="checkout-input min-w-0 flex-1 rounded-r-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Format : 0707267572 ou 07 07 26 75 72') }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Adresse') }}</label>
                            <input type="text" name="billing_address" maxlength="200" value="{{ old('billing_address', $provider->address) }}"
                                   class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Ville') }}</label>
                            <input type="text" name="billing_city" maxlength="100" value="{{ old('billing_city', $provider->city) }}"
                                   class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 border-t border-white/10 pt-5 text-white font-bold text-lg">
                        <span class="checkout-title-icon inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gold-500/15 text-gold-400"><i class="fas fa-wallet"></i></span>
                        {{ __('Moyen de paiement') }}
                    </div>

                    <div class="rounded-xl border border-gold-500/35 bg-gold-500/10 p-4">
                        <p class="mb-2 text-sm font-bold text-white">{{ __('Mobile Money') }}</p>
                        <p class="mb-3 text-xs text-gray-400">{{ __('Orange Money CI, MTN MoMo, Moov, Wave') }}</p>
                        <label class="sr-only" for="public-method-select">{{ __('Opérateur / moyen') }}</label>
                        <select id="public-method-select" name="method" class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white focus:border-gold-500/50 focus:outline-none">
                            <option value="orange_money">{{ __('Orange Money') }}</option>
                            <option value="mtn_momo">{{ __('MTN MoMo') }}</option>
                            <option value="wave">{{ __('Wave') }}</option>
                            <option value="moov_money">{{ __('Moov Money') }}</option>
                            <option value="card">{{ __('Carte bancaire') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Référence / numéro de paiement') }} <span class="text-red-400">*</span></label>
                        <input type="text" name="payment_reference" required maxlength="120" value="{{ old('payment_reference') }}"
                               placeholder="{{ __('Ex. : ID transaction Orange Money, Wave…') }}"
                               class="checkout-input w-full rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm text-white placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                        @error('payment_reference')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-200">{{ __('Cycle de facturation') }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cycle-option cursor-pointer">
                                <input type="radio" name="billing_cycle" value="monthly" class="sr-only" checked>
                                <div class="cycle-card rounded-xl border-2 border-gold-500 bg-gold-500/10 p-3 text-center">
                                    <p class="text-sm font-bold text-white">{{ __('Mensuel') }}</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gold-300">{{ number_format($monthlyPrice, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('/ mois') }}</p>
                                </div>
                            </label>
                            <label class="cycle-option cursor-pointer">
                                <input type="radio" name="billing_cycle" value="yearly" class="sr-only">
                                <div class="cycle-card rounded-xl border border-white/10 bg-dark-900 p-3 text-center hover:border-gold-500/40">
                                    <p class="text-sm font-bold text-white">{{ __('Annuel') }}</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gold-300">{{ number_format($yearlyPrice, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('/ an') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-200">{{ __('Code promo') }} <span class="font-normal text-gray-500">{{ __('(facultatif)') }}</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="promo-input" name="promo_code" value="{{ old('promo_code') }}" placeholder="{{ __('EX : IVOIRE20') }}"
                                   class="checkout-input flex-1 rounded-xl border border-white/10 bg-dark-900 px-3 py-2.5 text-sm uppercase tracking-wider text-white placeholder:normal-case placeholder:text-gray-600 focus:border-gold-500/50 focus:outline-none">
                            <button type="button" id="promo-btn" class="rounded-xl bg-gold-500/15 px-4 py-2.5 text-sm font-medium text-gold-200 transition hover:bg-gold-500/25">{{ __('Appliquer') }}</button>
                        </div>
                        <p id="promo-message" class="mt-1.5 hidden text-xs"></p>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 select-none">
                        <input type="checkbox" name="accept_cgu" value="1" class="mt-1 h-4 w-4 shrink-0 rounded border-white/20 bg-dark-900 text-gold-500 focus:ring-gold-500/50" {{ old('accept_cgu') ? 'checked' : '' }} required>
                        <span class="text-sm leading-relaxed text-gray-400">
                            {{ __('J\'accepte les') }} <a href="#" class="font-medium text-gold-300 underline hover:text-gold-200">{{ __('conditions générales') }}</a>
                            {{ __('et la') }} <a href="#" class="font-medium text-gold-300 underline hover:text-gold-200">{{ __('politique de confidentialité') }}</a>.
                        </span>
                    </label>
                    @error('accept_cgu')<p class="text-xs text-red-400">{{ $message }}</p>@enderror

                    <button type="submit" id="public-pay-submit"
                            class="checkout-submit flex w-full items-center justify-center gap-2 rounded-xl bg-gold-500 py-3.5 text-sm font-bold text-dark-900 transition hover:bg-gold-400">
                        <i class="fas fa-lock text-xs"></i>
                        <span id="public-pay-btn-label">{{ __('Payer') }} {{ number_format($monthlyPrice, 0, ',', ' ') }} {{ __('FCFA →') }}</span>
                    </button>
                </form>
            @endif
        </div>

        {{-- Colonne droite : récapitulatif --}}
        <aside class="lg:col-span-4">
            <div class="checkout-glass sticky top-6 rounded-2xl p-5">
                <div class="mb-4 flex items-center gap-2 font-bold text-white">
                    <span class="checkout-title-icon inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gold-500/15 text-gold-400"><i class="fas fa-list-ul text-sm"></i></span>
                    {{ __('Récapitulatif') }}
                </div>

                <div class="checkout-badge-premium mb-4 rounded-2xl border p-4 text-white">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-white/15 text-lg"><i class="fas fa-box"></i></span>
                        <div>
                            <p class="text-sm font-semibold text-[#B85600]">{{ $bundleItems->count() > 1 ? __(':n secteurs', ['n' => $bundleItems->count()]) : __('Forfait') }}</p>
                            <p class="text-lg font-bold leading-tight">{{ $bundleItems->count() > 1 ? __('Abonnement multi-secteurs') : $plan->localized('name') }}</p>
                        </div>
                    </div>
                </div>

                <dl class="space-y-2 text-sm">
                    @foreach($bundleItems as $bi)
                        @php $bp = $bi['plan']; @endphp
                        <div class="flex justify-between gap-4 text-gray-400">
                            <dt>{{ ($bi['sector'] ?? $bp->providerCategory)?->localized('name') ?? __('Abonnement') }}</dt>
                            <dd class="font-medium text-white text-right">{{ $bp->localized('name') }}<br><span class="bundle-line text-xs text-gray-500" data-m="{{ (float) $bp->price_monthly }}" data-y="{{ (float) $bp->price_yearly }}">{{ number_format((float) $bp->price_monthly, 0, ',', ' ') }} {{ __('FCFA') }}</span></dd>
                        </div>
                    @endforeach
                    <div class="flex justify-between gap-4 text-gray-400">
                        <dt>{{ __('Durée') }}</dt>
                        <dd id="recap-duration" class="font-medium text-white">{{ __('30 jours') }}</dd>
                    </div>
                    <div class="mt-3 flex justify-between border-t border-white/10 pt-3 text-gray-400">
                        <dt>{{ __('Sous-total') }}</dt>
                        <dd id="recap-line-base" class="font-medium text-white">—</dd>
                    </div>
                    <div id="recap-discount-row" class="hidden flex justify-between text-emerald-300">
                        <dt>{{ __('Réduction') }}</dt>
                        <dd id="recap-discount">—</dd>
                    </div>
                    <div class="checkout-total flex justify-between text-base font-bold">
                        <dt>{{ __('Total') }}</dt>
                        <dd id="recap-line-total">—</dd>
                    </div>
                </dl>

                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#00874F]/15 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 ring-1 ring-[#00874F]/35"><i class="fas fa-shield-halved"></i> {{ __('SSL') }}</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#00874F]/15 px-2.5 py-1 text-[11px] font-semibold text-green-200 ring-1 ring-[#00874F]/35"><i class="fas fa-lock"></i> {{ __('Chiffré') }}</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#EB6E00]/12 px-2.5 py-1 text-[11px] font-semibold text-[#B85600] ring-1 ring-[#EB6E00]/40"><i class="fas fa-circle-check"></i> {{ __('CynetPay') }}</span>
                </div>
            </div>
        </aside>
    </div>
@else
    <div class="max-w-xl space-y-5">
        <div class="rounded-xl border border-slate-800 bg-green-900 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange-500/15">
                    <i class="fas fa-gem text-sm text-orange-400"></i>
                </div>
                <div>
                    <p class="font-bold text-white">{{ strtoupper($plan->code) }} — {{ $plan->localized('name') }}</p>
                    <p class="text-xs text-slate-400">{{ $provider->name }}</p>
                </div>
            </div>
        </div>

        @if($cinetPayConfigured)
            <div class="space-y-5 rounded-xl border border-slate-800 bg-green-900 p-5">
                <div>
                    <label class="mb-2 block text-xs font-medium text-slate-300">{{ __('Cycle de facturation') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cycle-option cursor-pointer">
                            <input type="radio" name="billing_cycle" value="monthly" class="sr-only" checked>
                            <div class="cycle-card rounded-lg border border-orange-500 bg-orange-500/8 p-3 text-center transition hover:border-orange-500/70">
                                <p class="text-sm font-bold text-white">{{ __('Mensuel') }}</p>
                                <p class="mt-0.5 font-semibold text-orange-400">{{ number_format($bundleMonthly, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                <p class="text-xs text-slate-500">{{ __('/ mois') }}</p>
                            </div>
                        </label>
                        <label class="cycle-option cursor-pointer">
                            <input type="radio" name="billing_cycle" value="yearly" class="sr-only">
                            <div class="cycle-card rounded-lg border border-slate-700 p-3 text-center transition hover:border-orange-500/50">
                                <p class="text-sm font-bold text-white">{{ __('Annuel') }}</p>
                                <p class="mt-0.5 font-semibold text-orange-400">{{ number_format($bundleYearly, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                <p class="text-xs text-slate-500">{{ __('/ an') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-medium text-slate-300">{{ __('Canal de paiement') }}</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($channels as $ch)
                            <label class="channel-option cursor-pointer">
                                <input type="radio" name="channel" value="{{ $ch['code'] }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                                <div class="channel-card rounded-lg border p-2.5 transition {{ $loop->first ? 'border-orange-500 bg-orange-500/8' : 'border-slate-700' }}">
                                    <div class="flex items-center gap-2">
                                        <i class="fas {{ $ch['icon'] }} w-4 shrink-0 text-center text-xs text-orange-400"></i>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold leading-tight text-white">{{ $ch['label'] }}</p>
                                            <p class="truncate text-[10px] text-slate-500">{{ $ch['desc'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-medium text-slate-300">{{ __('Informations de facturation') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input type="text" id="customer_name" placeholder="{{ __('Prénom') }}"
                                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-orange-500/50 focus:outline-none">
                        </div>
                        <div>
                            <input type="text" id="customer_surname" placeholder="{{ __('Nom') }}"
                                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-orange-500/50 focus:outline-none">
                        </div>
                    </div>
                    <input type="email" id="customer_email" value="{{ auth()->user()->email ?? '' }}" placeholder="{{ __('Adresse e-mail') }}"
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-orange-500/50 focus:outline-none">
                    <input type="tel" id="customer_phone" placeholder="{{ __('Téléphone (ex : +225 07 00 00 00 00)') }}"
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-orange-500/50 focus:outline-none">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-medium text-slate-300">{{ __('Code promo') }} <span class="text-slate-500">{{ __('(facultatif)') }}</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="promo-input" placeholder="{{ __('EX : IVOIRE20') }}"
                               class="flex-1 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm uppercase tracking-wider text-slate-100 placeholder:normal-case placeholder:text-slate-500 focus:border-orange-500/50 focus:outline-none">
                        <button type="button" id="promo-btn" class="rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-slate-600">{{ __('Appliquer') }}</button>
                    </div>
                    <p id="promo-message" class="mt-1.5 hidden text-xs"></p>
                </div>

                <div class="space-y-2 rounded-lg border border-slate-700 bg-slate-800/60 p-4 text-sm">
                    <div class="flex justify-between text-slate-300">
                        <span>{{ __('Sous-total') }}</span>
                        <span id="summary-base">—</span>
                    </div>
                    <div id="summary-discount-row" class="hidden flex justify-between text-emerald-400">
                        <span>{{ __('Réduction (code promo)') }}</span>
                        <span id="summary-discount">{{ __('— 0 FCFA') }}</span>
                    </div>
                    <div class="mt-1 flex justify-between border-t border-slate-700 pt-2 font-bold text-white">
                        <span>{{ __('Total à payer') }}</span>
                        <span id="summary-total">—</span>
                    </div>
                </div>

                <label class="flex cursor-pointer select-none items-start gap-2.5">
                    <input type="checkbox" id="cgu-check" class="mt-0.5 h-4 w-4 shrink-0 accent-orange-500">
                    <span class="text-xs leading-relaxed text-slate-400">
                        {{ __('J\'accepte les') }}
                        <a href="#" class="text-orange-400 underline underline-offset-2 hover:text-orange-300">{{ __('conditions générales d\'utilisation') }}</a>
                        {{ __('et confirme que les informations saisies sont exactes.') }}
                    </span>
                </label>

                <div id="pay-error" class="hidden rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400"></div>

                <button type="button" id="pay-btn"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 py-3 text-sm font-bold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fas fa-lock text-xs"></i>
                    <span id="pay-btn-label">{{ __('Payer via CinetPay') }}</span>
                </button>

                <p class="text-center text-[11px] text-slate-500">
                    <i class="fas fa-shield-halved mr-1 text-orange-400/60"></i>
                    {{ __('Paiement sécurisé par CinetPay — vos données bancaires ne sont jamais transmises à notre serveur.') }}
                </p>
            </div>
        @else
            <div class="flex items-start gap-2.5 rounded-xl border border-orange-500/30 bg-orange-500/10 px-4 py-3 text-sm text-orange-300">
                <i class="fas fa-triangle-exclamation mt-0.5 shrink-0"></i>
                <span>{{ __('La passerelle CinetPay n\'est pas encore configurée. Utilisez le mode de simulation ci-dessous pour tester le flux de paiement.') }}</span>
            </div>

            <form method="POST" action="{{ route('subscriptions.process-offline', $plan) }}" class="space-y-5 rounded-xl border border-slate-800 bg-green-900 p-5">
                @csrf
                <input type="hidden" name="sector" value="{{ $bundleSector }}"><input type="hidden" name="avec" value="{{ $bundleAvec }}">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-300">{{ __('Prénom') }} <span class="text-red-400">*</span></label>
                        <input type="text" name="first_name" required maxlength="100" value="{{ old('first_name', auth()->user()->first_name) }}"
                               class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 focus:border-orange-500/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-300">{{ __('Nom') }} <span class="text-red-400">*</span></label>
                        <input type="text" name="last_name" required maxlength="100" value="{{ old('last_name', auth()->user()->last_name) }}"
                               class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 focus:border-orange-500/50 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-300">{{ __('Référence / numéro de paiement') }} <span class="text-red-400">*</span></label>
                    <input type="text" name="payment_reference" required maxlength="120" value="{{ old('payment_reference') }}"
                           placeholder="{{ __('Ex. : ID transaction Orange Money, Wave…') }}"
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-orange-500/50 focus:outline-none">
                    @error('payment_reference')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-medium text-slate-300">{{ __('Cycle de facturation') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cycle-option cursor-pointer">
                            <input type="radio" name="billing_cycle" value="monthly" class="sr-only" checked>
                            <div class="cycle-card rounded-lg border border-orange-500 bg-orange-500/8 p-3 text-center transition">
                                <p class="text-sm font-bold text-white">{{ __('Mensuel') }}</p>
                                <p class="mt-0.5 font-semibold text-orange-400">{{ number_format($bundleMonthly, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                <p class="text-xs text-slate-500">{{ __('/ mois') }}</p>
                            </div>
                        </label>
                        <label class="cycle-option cursor-pointer">
                            <input type="radio" name="billing_cycle" value="yearly" class="sr-only">
                            <div class="cycle-card rounded-lg border border-slate-700 p-3 text-center transition hover:border-orange-500/50">
                                <p class="text-sm font-bold text-white">{{ __('Annuel') }}</p>
                                <p class="mt-0.5 font-semibold text-orange-400">{{ number_format($bundleYearly, 0, ',', ' ') }} {{ __('FCFA') }}</p>
                                <p class="text-xs text-slate-500">{{ __('/ an') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-medium text-slate-300">{{ __('Méthode de paiement (simulation)') }}</label>
                    <select name="method" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 focus:border-orange-500/50 focus:outline-none">
                        <option value="orange_money">{{ __('Orange Money') }}</option>
                        <option value="mtn_momo">{{ __('MTN MoMo') }}</option>
                        <option value="wave">{{ __('Wave') }}</option>
                        <option value="moov_money">{{ __('Moov Money') }}</option>
                        <option value="card">{{ __('Carte bancaire') }}</option>
                    </select>
                    <input type="hidden" name="gateway" value="cinetpay">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-medium text-slate-300">{{ __('Code promo') }} <span class="text-slate-500">{{ __('(facultatif)') }}</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="promo-input" name="promo_code" placeholder="{{ __('EX : IVOIRE20') }}"
                               class="flex-1 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm uppercase tracking-wider text-slate-100 placeholder:normal-case placeholder:text-slate-500 focus:border-orange-500/50 focus:outline-none">
                        <button type="button" id="promo-btn" class="rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-slate-600">{{ __('Appliquer') }}</button>
                    </div>
                    <p id="promo-message" class="mt-1.5 hidden text-xs"></p>
                </div>

                <div class="space-y-2 rounded-lg border border-slate-700 bg-slate-800/60 p-4 text-sm">
                    <div class="flex justify-between text-slate-300">
                        <span>{{ __('Sous-total') }}</span>
                        <span id="summary-base">—</span>
                    </div>
                    <div id="summary-discount-row" class="hidden flex justify-between text-emerald-400">
                        <span>{{ __('Réduction (code promo)') }}</span>
                        <span id="summary-discount">{{ __('— 0 FCFA') }}</span>
                    </div>
                    <div class="mt-1 flex justify-between border-t border-slate-700 pt-2 font-bold text-white">
                        <span>{{ __('Total à payer') }}</span>
                        <span id="summary-total">—</span>
                    </div>
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 py-3 text-sm font-bold text-white transition hover:bg-orange-600">
                    <i class="fas fa-vial text-xs"></i>
                    {{ __('Simuler le paiement (mode test)') }}
                </button>
            </form>
        @endif
    </div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const isPublicCheckout = @json($isPublicCheckout);
    const planId      = {{ $plan->id }};
    const priceM      = {{ $bundleMonthly }};
    const priceY      = {{ $bundleYearly }};
    const bundleSector = @json($bundleSector);
    const bundleAvec   = @json($bundleAvec);
    const validateUrl = '{{ route('provider.billing.promo.validate') }}';
    const csrfEl = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfEl ? csrfEl.content : '';
    const cynetConfigured = {{ $cinetPayConfigured ? 'true' : 'false' }};

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

    function summaryEls() {
        if (isPublicCheckout) {
            return {
                base: document.getElementById('recap-line-base'),
                total: document.getElementById('recap-line-total'),
                discountRow: document.getElementById('recap-discount-row'),
                discount: document.getElementById('recap-discount'),
                duration: document.getElementById('recap-duration'),
            };
        }
        return {
            base: document.getElementById('summary-base'),
            total: document.getElementById('summary-total'),
            discountRow: document.getElementById('summary-discount-row'),
            discount: document.getElementById('summary-discount'),
            duration: null,
        };
    }

    function updateSummary() {
        const base  = baseAmount();
        const total = Math.max(0, base - discountAmount);
        const els = summaryEls();
        if (els.base) els.base.textContent = fmt(base);
        if (els.total) els.total.textContent = fmt(total);
        if (els.duration) els.duration.textContent = currentCycle === 'yearly' ? '12 mois' : '30 jours';
        document.querySelectorAll('.bundle-line').forEach(function (el) { el.textContent = fmt(Number(currentCycle === 'yearly' ? el.dataset.y : el.dataset.m)); });

        const payLabel = document.getElementById('public-pay-btn-label');
        if (payLabel && !cynetConfigured && isPublicCheckout) {
            payLabel.textContent = 'Payer ' + fmt(total) + ' →';
        }
    }

    document.querySelectorAll('.cycle-option input[type=radio]').forEach(radio => {
        radio.addEventListener('change', () => {
            currentCycle   = radio.value;
            discountAmount = 0;
            appliedPromoCode = '';
            const promoInput = document.getElementById('promo-input');
            const promoMsg   = document.getElementById('promo-message');
            const els = summaryEls();
            if (promoInput) promoInput.value = '';
            if (promoMsg)   promoMsg.classList.add('hidden');
            if (els.discountRow) els.discountRow.classList.add('hidden');
            document.querySelectorAll('.cycle-card').forEach(c => {
                c.classList.remove('border-2', 'border-[#00874F]', 'bg-emerald-50/50', 'border-gold-500', 'bg-gold-500/10', 'border-[#EB6E00]', 'bg-[#EB6E00]/10', 'border-orange-500', 'bg-orange-500/8');
                c.classList.add('border');
                if (isPublicCheckout) {
                    c.classList.add('border-white/10', 'bg-dark-900');
                    c.classList.remove('border-slate-200', 'border-slate-700');
                } else {
                    c.classList.add('border-slate-700');
                    c.classList.remove('border-slate-200', 'border-white/10', 'bg-dark-900');
                }
            });
            const card = radio.closest('.cycle-option') && radio.closest('.cycle-option').querySelector('.cycle-card');
            if (card) {
                if (isPublicCheckout) {
                    card.classList.remove('border', 'border-white/10', 'bg-dark-900');
                    card.classList.add('border-2', 'border-[#EB6E00]', 'bg-[#EB6E00]/10');
                } else {
                    card.classList.remove('border-slate-700');
                    card.classList.add('border-orange-500', 'bg-orange-500/8');
                }
            }
            updateSummary();
        });
        if (radio.checked) {
            currentCycle = radio.value;
            const card = radio.closest('.cycle-option') && radio.closest('.cycle-option').querySelector('.cycle-card');
            if (card) {
                if (isPublicCheckout) {
                    card.classList.add('border-2', 'border-[#EB6E00]', 'bg-[#EB6E00]/10');
                } else {
                    card.classList.add('border-orange-500', 'bg-orange-500/8');
                    card.classList.remove('border-slate-700');
                }
            }
        }
    });

    document.querySelectorAll('.channel-option input[type=radio]').forEach(radio => {
        radio.addEventListener('change', () => {
            currentChannel = radio.value;
            document.querySelectorAll('.channel-card').forEach(c => {
                c.classList.remove('border-[#4A90D9]', 'bg-green-50/60', 'border-gold-500', 'bg-gold-500/10', 'border-[#EB6E00]', 'bg-[#EB6E00]/10', 'border-orange-500', 'bg-orange-500/8');
                if (isPublicCheckout) {
                    c.classList.add('border-white/10', 'bg-dark-900');
                    c.classList.remove('border-slate-200', 'border-slate-700');
                } else {
                    c.classList.add('border-slate-700');
                    c.classList.remove('border-slate-200', 'border-white/10', 'bg-dark-900');
                }
            });
            const chCard = radio.closest('.channel-option') && radio.closest('.channel-option').querySelector('.channel-card');
            if (chCard) {
                if (isPublicCheckout) {
                    chCard.classList.remove('border-white/10', 'bg-dark-900');
                    chCard.classList.add('border-[#EB6E00]', 'bg-[#EB6E00]/10');
                } else {
                    chCard.classList.remove('border-slate-700');
                    chCard.classList.add('border-orange-500', 'bg-orange-500/8');
                }
            }
        });
        if (radio.checked) currentChannel = radio.value;
    });

    updateSummary();

    const promoBtn = document.getElementById('promo-btn');
    if (promoBtn) {
        promoBtn.addEventListener('click', async () => {
            const promoInput = document.getElementById('promo-input');
            const code = (promoInput && promoInput.value || '').trim();
            const msg  = document.getElementById('promo-message');
            if (!msg) return;

            if (! code) {
                msg.textContent = {{ \Illuminate\Support\Js::from(__('Saisissez un code promo.')) }};
                msg.className   = 'mt-1.5 text-xs text-orange-600';
                msg.classList.remove('hidden');
                return;
            }

            promoBtn.disabled    = true;
            const prevText = promoBtn.textContent;
            promoBtn.textContent = '…';

            try {
                const res  = await fetch(validateUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ code, plan_id: planId, billing_cycle: currentCycle }),
                });
                const data = await res.json();
                const els = summaryEls();

                if (data.valid) {
                    discountAmount   = data.discount_amount;
                    appliedPromoCode = code;
                    if (els.discount) els.discount.textContent = '— ' + fmt(discountAmount);
                    if (els.discountRow) els.discountRow.classList.remove('hidden');
                    msg.textContent = '✓ ' + data.message;
                    msg.className   = 'mt-1.5 text-xs text-emerald-600';
                } else {
                    discountAmount   = 0;
                    appliedPromoCode = '';
                    if (els.discountRow) els.discountRow.classList.add('hidden');
                    msg.textContent = data.message;
                    msg.className   = 'mt-1.5 text-xs text-red-600';
                }
            } catch {
                msg.textContent = {{ \Illuminate\Support\Js::from(__('Erreur réseau. Réessayez.')) }};
                msg.className   = 'mt-1.5 text-xs text-red-600';
            }

            msg.classList.remove('hidden');
            updateSummary();
            promoBtn.disabled    = false;
            promoBtn.textContent = prevText;
        });

        const promoInputEl = document.getElementById('promo-input');
        if (promoInputEl) {
            promoInputEl.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); promoBtn.click(); }
            });
        }
    }

    if (cynetConfigured) {
        const payBtn   = document.getElementById('pay-btn');
        const payLabel = document.getElementById('pay-btn-label');
        const payError = document.getElementById('pay-error');
        if (payBtn && payLabel) {
            const initiateUrl = '{{ route('provider.payment.cynetpay.initiate') }}';

            payBtn.addEventListener('click', async () => {
                payError.classList.add('hidden');

                const name    = document.getElementById('customer_name').value.trim();
                const surname = document.getElementById('customer_surname').value.trim();
                const email   = document.getElementById('customer_email').value.trim();
                const phoneEl = document.getElementById('customer_phone');
                let phone = phoneEl ? phoneEl.value.trim() : '';
                if (isPublicCheckout && phone && !phone.startsWith('+')) {
                    const digits = phone.replace(/\D+/g, '');
                    phone = digits ? '+225' + digits.replace(/^225/, '') : '';
                }
                const cguEl = document.getElementById('cgu-check');
                const cgu     = cguEl && cguEl.checked;

                if (! name || ! surname || ! email || ! phone) {
                    payError.textContent = {{ \Illuminate\Support\Js::from(__('Veuillez remplir toutes les informations de facturation.')) }};
                    payError.classList.remove('hidden');
                    return;
                }
                if (! cgu) {
                    payError.textContent = {{ \Illuminate\Support\Js::from(__('Veuillez accepter les conditions générales d\'utilisation.')) }};
                    payError.classList.remove('hidden');
                    return;
                }

                payBtn.disabled = true;
                payLabel.textContent = {{ \Illuminate\Support\Js::from(__('Redirection en cours…')) }};

                try {
                    const res  = await fetch(initiateUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            plan_id:          planId,
                            sector:           bundleSector || undefined,
                            extras:           bundleAvec || undefined,
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
                        payError.textContent = data.message || {{ \Illuminate\Support\Js::from(__('Une erreur est survenue. Veuillez réessayer.')) }};
                        payError.classList.remove('hidden');
                        payBtn.disabled = false;
                        payLabel.textContent = 'Payer via CinetPay';
                    }
                } catch {
                    payError.textContent = {{ \Illuminate\Support\Js::from(__('Erreur réseau. Vérifiez votre connexion et réessayez.')) }};
                    payError.classList.remove('hidden');
                    payBtn.disabled = false;
                    payLabel.textContent = 'Payer via CinetPay';
                }
            });
        }
    }
})();
</script>
@endpush
