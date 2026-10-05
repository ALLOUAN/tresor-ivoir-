@extends('layouts.visitor-public')

@section('title', 'Mon portefeuille')
@section('page-title', 'Mon portefeuille')

@section('content')
<div class="max-w-5xl mx-auto">
    @include('partials.visitor-account-nav')

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-green-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-widest mb-1">Solde disponible</p>
                    <p class="text-white text-3xl font-bold">{{ number_format($wallet->balance_available_xof, 0, ',', ' ') }} <span class="text-lg text-slate-400 font-normal">XOF</span></p>
                </div>
                @if($wallet->balance_available_xof > 0)
                <a href="#retrait" class="shrink-0 inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition">
                    <i class="fas fa-money-bill-transfer text-xs"></i> Retirer
                </a>
                @endif
            </div>
            <p class="text-slate-500 text-xs mt-2">Utilisable immédiatement pour payer l'acompte d'une réservation.</p>
        </div>

        <div class="bg-green-900 border border-slate-800 rounded-2xl p-6">
            <p class="text-white font-semibold text-sm mb-3"><i class="fas fa-plus-circle text-orange-400 mr-1.5"></i>Recharger mon solde</p>
            <div id="topup-error" class="hidden mb-3 px-3 py-2 bg-red-900/30 border border-red-700/40 rounded-lg text-red-300 text-xs"></div>
            <div class="flex items-center gap-2">
                <input type="number" id="topup-amount" min="{{ $minTopup }}" max="{{ $maxTopup }}" step="1"
                       placeholder="Montant en XOF"
                       class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                <button type="button" id="topup-submit"
                        class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition disabled:opacity-50">
                    <i class="fas fa-arrow-right" id="topup-icon"></i>
                    <span id="topup-label">Recharger</span>
                </button>
            </div>
            <p class="text-slate-600 text-[11px] mt-2">Entre {{ number_format($minTopup, 0, ',', ' ') }} et {{ number_format($maxTopup, 0, ',', ' ') }} XOF · paiement sécurisé via CinetPay.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-800">
                    <h2 class="text-white font-semibold">Historique des mouvements</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                                <th class="text-left px-5 py-3">Date</th>
                                <th class="text-left px-5 py-3">Type</th>
                                <th class="text-left px-5 py-3">Réservation</th>
                                <th class="text-right px-5 py-3">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            @forelse($transactions as $t)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="px-5 py-3 text-slate-300">{{ $t->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-3 text-slate-200">{{ $t->labelForType() }}</td>
                                    <td class="px-5 py-3 text-slate-400 text-xs">{{ $t->reservation?->reference ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right font-semibold {{ $t->amount_xof >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                                        {{ $t->amount_xof >= 0 ? '+' : '' }}{{ number_format($t->amount_xof, 0, ',', ' ') }} XOF
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-slate-500">Aucun mouvement pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-800">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @php $pendingVerification = $payoutRequests->firstWhere('status', 'pending_verification'); @endphp
            @if($pendingVerification)
            <div class="bg-green-900 border border-orange-500/40 rounded-xl p-5">
                <h2 class="text-white font-semibold text-sm mb-1"><i class="fas fa-shield-halved text-orange-400 mr-1.5"></i>Confirmez votre retrait</h2>
                <p class="text-slate-500 text-xs mb-4">Un code à 6 chiffres a été envoyé par email pour confirmer le retrait de <span class="text-orange-300 font-semibold">{{ number_format($pendingVerification->amount_xof, 0, ',', ' ') }} XOF</span>.</p>
                <form method="POST" action="{{ route('visitor.wallet.payout.verify', $pendingVerification) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="code" required maxlength="6" pattern="\d{6}" inputmode="numeric" placeholder="Code à 6 chiffres"
                           class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 tracking-widest">
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Confirmer</button>
                </form>
                <form method="POST" action="{{ route('visitor.wallet.payout.resend', $pendingVerification) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="text-orange-400 hover:text-orange-300 text-xs transition">Renvoyer le code</button>
                </form>
            </div>
            @endif

            <div id="retrait" class="bg-green-900 border border-slate-800 rounded-xl p-5 scroll-mt-24">
                <h2 class="text-white font-semibold text-sm mb-1">Demander un retrait</h2>
                <p class="text-slate-500 text-xs mb-4">Solde disponible : <span class="text-emerald-300 font-semibold">{{ number_format($wallet->balance_available_xof, 0, ',', ' ') }} XOF</span></p>

                @if($wallet->balance_available_xof <= 0)
                    <p class="text-slate-500 text-sm">Aucun solde disponible pour le moment.</p>
                @else
                <form method="POST" action="{{ route('visitor.wallet.request-payout') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-slate-400 text-xs mb-1">Montant (XOF)</label>
                        <input type="number" name="amount_xof" min="1" max="{{ $wallet->balance_available_xof }}" value="{{ old('amount_xof', $wallet->balance_available_xof) }}" required
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="block text-slate-400 text-xs mb-1">Réseau de transaction</label>
                        <select name="method" id="payout-method" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                            @foreach(\App\Models\PayoutRequest::METHOD_LABELS as $value => $label)
                                <option value="{{ $value }}" data-mobile-money="{{ in_array($value, \App\Models\PayoutRequest::MOBILE_MONEY_METHODS) ? '1' : '0' }}" @selected(old('method', $user->payout_method) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-xs mb-1" id="payout-destination-label">Numéro de téléphone</label>
                        <input type="tel" id="payout-destination" name="payout_destination" required maxlength="255"
                               value="{{ old('payout_destination', $user->payout_account_number) }}"
                               placeholder="+225 07 00 00 00 00"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
                        <i class="fas fa-paper-plane text-xs"></i> Envoyer la demande
                    </button>
                </form>
                @endif
            </div>

            @if($payoutRequests->isNotEmpty())
            <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-800">
                    <h2 class="text-white font-semibold text-sm">Mes demandes de retrait</h2>
                </div>
                <div class="divide-y divide-slate-800">
                    @foreach($payoutRequests as $req)
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <div>
                            <p class="text-white font-medium">{{ number_format($req->amount_xof, 0, ',', ' ') }} XOF</p>
                            <p class="text-slate-500 text-xs">{{ $req->created_at?->format('d/m/Y') }}</p>
                        </div>
                        @php
                            $pClass = match($req->status) {
                                'paid' => 'bg-emerald-500/20 text-emerald-300',
                                'approved' => 'bg-slate-500/20 text-slate-300',
                                'rejected', 'suspended' => 'bg-rose-500/20 text-rose-300',
                                default => 'bg-orange-500/20 text-orange-300',
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs {{ $pClass }}">{{ $req->labelForStatus() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('topup-submit');
    const input = document.getElementById('topup-amount');
    const errorBox = document.getElementById('topup-error');
    const label = document.getElementById('topup-label');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        errorBox.classList.add('hidden');
        const amount = parseInt(input.value, 10);
        if (!amount || amount <= 0) {
            errorBox.textContent = 'Merci de saisir un montant valide.';
            errorBox.classList.remove('hidden');
            return;
        }
        btn.disabled = true;
        const originalLabel = label.textContent;
        label.textContent = 'Traitement...';
        try {
            const res = await fetch('{{ route('visitor.wallet.topup.initiate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ amount_xof: amount }),
            });
            const data = await res.json();
            if (data.success && data.payment_url) {
                window.location.href = data.payment_url;
                return;
            }
            errorBox.textContent = data.message || 'Une erreur est survenue, merci de réessayer.';
            errorBox.classList.remove('hidden');
        } catch (e) {
            errorBox.textContent = 'Erreur réseau, merci de réessayer.';
            errorBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            label.textContent = originalLabel;
        }
    });
})();

(function () {
    const methodSelect = document.getElementById('payout-method');
    const destInput = document.getElementById('payout-destination');
    const destLabel = document.getElementById('payout-destination-label');
    if (!methodSelect || !destInput) return;

    function syncDestinationField() {
        const isMobileMoney = methodSelect.options[methodSelect.selectedIndex]?.dataset.mobileMoney === '1';
        if (isMobileMoney) {
            destLabel.textContent = 'Numéro de téléphone';
            destInput.type = 'tel';
            destInput.placeholder = '+225 07 00 00 00 00';
        } else {
            destLabel.textContent = 'Numéro de compte bancaire / IBAN';
            destInput.type = 'text';
            destInput.placeholder = 'CI00 0000 0000 0000 0000 0000 000';
        }
    }

    methodSelect.addEventListener('change', syncDestinationField);
    syncDestinationField();
})();
</script>
@endsection
