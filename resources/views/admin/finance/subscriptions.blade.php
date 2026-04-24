@extends('layouts.app')

@section('title', 'Abonnements')
@section('page-title', 'Gestion des abonnements')

@section('content')
@php
    $showCreateSubscriptionModal = old('provider_id') || old('plan_id') || old('starts_at') || old('ends_at');
@endphp

<div class="flex items-center gap-3 mb-6">
    <button type="button" id="open-create-subscription-modal" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
        Créer un nouvel abonnement
    </button>
    <span class="text-slate-500 text-sm">Cliquez pour ouvrir le formulaire de création.</span>
</div>

<div id="create-subscription-modal" class="fixed inset-0 z-50 {{ $showCreateSubscriptionModal ? '' : 'hidden' }}">
    <div id="create-subscription-overlay" class="absolute inset-0 bg-black/70"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-3xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
                <h2 class="text-white font-semibold">Créer un nouvel abonnement</h2>
                <button type="button" id="close-create-subscription-modal" class="text-slate-400 hover:text-white text-lg">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5">
                @csrf
                <div>
                    <label class="block text-slate-300 text-xs mb-1">Prestataire</label>
                    <select name="provider_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="">Sélectionner...</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" @selected((string) old('provider_id') === (string) $provider->id)>{{ $provider->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-xs mb-1">Forfait</label>
                    <select name="plan_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="">Sélectionner...</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>{{ strtoupper($plan->code) }} - {{ $plan->name_fr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-xs mb-1">Statut</label>
                    <select name="status" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="active" @selected(old('status', 'active') === 'active')>Actif</option>
                        <option value="suspended" @selected(old('status') === 'suspended')>Suspendu</option>
                        <option value="cancelled" @selected(old('status') === 'cancelled')>Annulé</option>
                        <option value="expired" @selected(old('status') === 'expired')>Expiré</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-xs mb-1">Cycle de facturation</label>
                    <select name="billing_cycle" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="monthly" @selected(old('billing_cycle', 'monthly') === 'monthly')>Mensuel</option>
                        <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Annuel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-xs mb-1">Méthode de paiement</label>
                    <select name="payment_method" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="orange_money" @selected(old('payment_method', 'orange_money') === 'orange_money')>Orange Money</option>
                        <option value="mtn_momo" @selected(old('payment_method') === 'mtn_momo')>MTN MoMo</option>
                        <option value="wave" @selected(old('payment_method') === 'wave')>Wave</option>
                        <option value="moov_money" @selected(old('payment_method') === 'moov_money')>Moov Money</option>
                        <option value="card" @selected(old('payment_method') === 'card')>Carte</option>
                        <option value="paypal" @selected(old('payment_method') === 'paypal')>PayPal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-xs mb-1">Début</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-slate-300 text-xs mb-1">Fin</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="text-slate-300 text-sm"><input type="checkbox" name="auto_renew" value="1" @checked(old('auto_renew', 1))> Auto-renouvellement</label>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-300 text-xs mb-1">Motif d'annulation (optionnel)</label>
                    <textarea name="cancellation_reason" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100" placeholder="Seulement si statut annulé">{{ old('cancellation_reason') }}</textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Créer abonnement</button>
                    <button type="button" id="cancel-create-subscription-modal" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Total abonnements</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Actifs</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($stats['active']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Renouvellement auto</p>
        <p class="text-blue-400 text-2xl font-bold mt-1">{{ number_format($stats['renewing']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Expire sous 30 jours</p>
        <p class="text-amber-400 text-2xl font-bold mt-1">{{ number_format($stats['expiring_soon']) }}</p>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-white font-semibold">Abonnements expirant bientôt (30 jours)</h2>
        <a href="{{ route('admin.subscriptions.index', ['expiring_soon' => 1]) }}" class="text-amber-400 text-xs hover:text-amber-300">Voir uniquement ceux-ci</a>
    </div>
    <div class="space-y-2">
        @forelse($expiringSoonSubscriptions as $expiring)
            @php $daysLeft = $expiring->ends_at ? max(0, now()->diffInDays($expiring->ends_at, false)) : 0; @endphp
            <div class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-white text-sm font-medium">{{ $expiring->provider->name ?? '—' }} - {{ strtoupper($expiring->plan->code ?? 'N/A') }}</p>
                    <p class="text-slate-400 text-xs">
                        Expire le {{ optional($expiring->ends_at)->format('d/m/Y') }} (dans {{ $daysLeft }} jour(s))
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.subscriptions.extend', $expiring) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="extend_by_months" class="bg-slate-700 border border-slate-600 rounded-lg px-2 py-1.5 text-xs text-slate-100">
                        <option value="1">+1 mois</option>
                        <option value="3">+3 mois</option>
                        <option value="6">+6 mois</option>
                        <option value="12">+12 mois</option>
                    </select>
                    <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">
                        Prolonger
                    </button>
                </form>
            </div>
        @empty
            <p class="text-slate-500 text-sm">Aucun abonnement actif n'expire dans les 30 prochains jours.</p>
        @endforelse
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Abonnements</h2>
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un prestataire..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les statuts</option>
                @foreach(['active' => 'Actif', 'suspended' => 'Suspendu', 'cancelled' => 'Annulé', 'expired' => 'Expiré'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="billing_cycle" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les cycles</option>
                <option value="monthly" @selected($cycle === 'monthly')>Mensuel</option>
                <option value="yearly" @selected($cycle === 'yearly')>Annuel</option>
            </select>
            <label class="text-slate-300 text-sm flex items-center gap-2">
                <input type="checkbox" name="expiring_soon" value="1" @checked($expiringSoonOnly)>
                Expirent bientôt (30 jours)
            </label>
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.subscriptions.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Prestataire</th>
                    <th class="text-left px-5 py-3">Forfait</th>
                    <th class="text-left px-5 py-3">Cycle</th>
                    <th class="text-left px-5 py-3">Paiement</th>
                    <th class="text-left px-5 py-3">Période</th>
                    <th class="text-left px-5 py-3">Auto-renouvellement</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($subscriptions as $subscription)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 text-white">{{ $subscription->provider->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ strtoupper($subscription->plan->code ?? 'N/A') }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ $subscription->billing_cycle === 'yearly' ? 'Annuel' : 'Mensuel' }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ str_replace('_', ' ', ucfirst($subscription->payment_method ?? '')) ?: '—' }}</td>
                        <td class="px-5 py-3 text-slate-300">
                            {{ optional($subscription->starts_at)->format('d/m/Y') ?? '-' }} -
                            {{ optional($subscription->ends_at)->format('d/m/Y') ?? '-' }}
                            @if($subscription->last_edited_at && $subscription->last_edited_at->gt(now()->subDays(7)))
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                        Modifié récemment - {{ $subscription->last_edited_at->format('d/m/Y H:i') }} par {{ $subscription->lastEditedBy->full_name ?? 'admin' }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-300">{{ $subscription->auto_renew ? 'Oui' : 'Non' }}</td>
                        <td class="px-5 py-3">
                            @php
                                $statusClass = match($subscription->status) {
                                    'active' => 'bg-emerald-500/20 text-emerald-300',
                                    'suspended' => 'bg-orange-500/20 text-orange-300',
                                    'cancelled' => 'bg-red-500/20 text-red-300',
                                    default => 'bg-slate-500/20 text-slate-300',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs {{ $statusClass }}">{{ $subscription->status }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <button
                                type="button"
                                class="open-edit-subscription-modal bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold px-3 py-1.5 rounded"
                                data-target="edit-subscription-modal-{{ $subscription->id }}"
                            >
                                Modifier
                            </button>
                        </td>
                    </tr>

                    <div id="edit-subscription-modal-{{ $subscription->id }}" class="fixed inset-0 z-50 hidden">
                        <div class="edit-subscription-overlay absolute inset-0 bg-black/70"></div>
                        <div class="relative min-h-full flex items-center justify-center p-4">
                            <div class="w-full max-w-3xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
                                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
                                    <h2 class="text-white font-semibold">Modifier abonnement</h2>
                                    <button type="button" class="close-edit-subscription-modal text-slate-400 hover:text-white text-lg" data-target="edit-subscription-modal-{{ $subscription->id }}">
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block text-slate-300 text-xs mb-1">Prestataire</label>
                                        <select name="provider_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider->id }}" @selected((int) $subscription->provider_id === (int) $provider->id)>{{ $provider->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-300 text-xs mb-1">Forfait</label>
                                        <select name="plan_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}" @selected((int) $subscription->plan_id === (int) $plan->id)>{{ strtoupper($plan->code) }} - {{ $plan->name_fr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-300 text-xs mb-1">Statut</label>
                                        <select name="status" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                            <option value="active" @selected($subscription->status === 'active')>Actif</option>
                                            <option value="suspended" @selected($subscription->status === 'suspended')>Suspendu</option>
                                            <option value="cancelled" @selected($subscription->status === 'cancelled')>Annulé</option>
                                            <option value="expired" @selected($subscription->status === 'expired')>Expiré</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-300 text-xs mb-1">Cycle de facturation</label>
                                        <select name="billing_cycle" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                            <option value="monthly" @selected($subscription->billing_cycle === 'monthly')>Mensuel</option>
                                            <option value="yearly" @selected($subscription->billing_cycle === 'yearly')>Annuel</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-300 text-xs mb-1">Méthode de paiement</label>
                                        <select name="payment_method" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                            <option value="orange_money" @selected($subscription->payment_method === 'orange_money')>Orange Money</option>
                                            <option value="mtn_momo" @selected($subscription->payment_method === 'mtn_momo')>MTN MoMo</option>
                                            <option value="wave" @selected($subscription->payment_method === 'wave')>Wave</option>
                                            <option value="moov_money" @selected($subscription->payment_method === 'moov_money')>Moov Money</option>
                                            <option value="card" @selected($subscription->payment_method === 'card')>Carte</option>
                                            <option value="paypal" @selected($subscription->payment_method === 'paypal')>PayPal</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-300 text-xs mb-1">Début</label>
                                        <input type="datetime-local" name="starts_at" value="{{ optional($subscription->starts_at)->format('Y-m-d\\TH:i') }}" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                    </div>
                                    <div>
                                        <label class="block text-slate-300 text-xs mb-1">Fin</label>
                                        <input type="datetime-local" name="ends_at" value="{{ optional($subscription->ends_at)->format('Y-m-d\\TH:i') }}" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-slate-300 text-sm"><input type="checkbox" name="auto_renew" value="1" @checked($subscription->auto_renew)> Auto-renouvellement</label>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-slate-300 text-xs mb-1">Motif d'annulation (optionnel)</label>
                                        <textarea name="cancellation_reason" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ $subscription->cancellation_reason }}</textarea>
                                    </div>
                                    <div class="md:col-span-2 flex items-center gap-2">
                                        <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Enregistrer</button>
                                        <button type="button" class="close-edit-subscription-modal bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg" data-target="edit-subscription-modal-{{ $subscription->id }}">Annuler</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-500">Aucun abonnement trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $subscriptions->links() }}
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('create-subscription-modal');
        const openButton = document.getElementById('open-create-subscription-modal');
        const closeButton = document.getElementById('close-create-subscription-modal');
        const cancelButton = document.getElementById('cancel-create-subscription-modal');
        const overlay = document.getElementById('create-subscription-overlay');

        if (!modal || !openButton || !closeButton || !cancelButton || !overlay) {
            return;
        }

        const openModal = function () {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = function () {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        openButton.addEventListener('click', openModal);
        closeButton.addEventListener('click', closeModal);
        cancelButton.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);

        document.querySelectorAll('.open-edit-subscription-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const targetModal = targetId ? document.getElementById(targetId) : null;
                if (!targetModal) return;
                targetModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });
        });

        document.querySelectorAll('.close-edit-subscription-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const targetModal = targetId ? document.getElementById(targetId) : null;
                if (!targetModal) return;
                targetModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        });

        document.querySelectorAll('.edit-subscription-overlay').forEach(function (overlayEl) {
            overlayEl.addEventListener('click', function () {
                const targetModal = overlayEl.closest('.fixed.inset-0.z-50');
                if (!targetModal) return;
                targetModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }

            if (event.key === 'Escape') {
                document.querySelectorAll('.fixed.inset-0.z-50').forEach(function (modalEl) {
                    if (!modalEl.classList.contains('hidden')) {
                        modalEl.classList.add('hidden');
                    }
                });
                document.body.classList.remove('overflow-hidden');
            }
        });
    })();
</script>
@endsection
