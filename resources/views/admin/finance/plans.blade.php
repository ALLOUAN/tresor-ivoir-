@extends('layouts.app')

@section('title', 'Forfaits')
@section('page-title', 'Gestion des forfaits et promotions')

@section('content')
@php
    $showCreatePlanModal = old('code') || old('name_fr') || old('name_en') || old('price_monthly') || old('price_yearly');
@endphp

<div class="flex items-center gap-3 mb-6">
    <button type="button" id="open-create-plan-modal" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
        Créer un forfait
    </button>
    <span class="text-slate-500 text-sm">Cliquez pour ouvrir le formulaire de création.</span>
</div>

<div id="create-plan-modal" class="fixed inset-0 z-50 {{ $showCreatePlanModal ? '' : 'hidden' }}">
    <div id="create-plan-overlay" class="absolute inset-0 bg-black/70"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-5xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
                <h2 class="text-white font-semibold">Créer un forfait</h2>
                <button type="button" id="close-create-plan-modal" class="text-slate-400 hover:text-white text-lg">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.plans.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-5">
                @csrf
                <input name="code" value="{{ old('code') }}" placeholder="Code: bronze/silver/gold" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="name_fr" value="{{ old('name_fr') }}" placeholder="Nom FR" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="name_en" value="{{ old('name_en') }}" placeholder="Nom EN" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="covered_levels" value="{{ old('covered_levels') }}" placeholder="Niveaux couverts" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_monthly" value="{{ old('price_monthly') }}" type="number" step="0.01" placeholder="Prix mensuel" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_quarterly" value="{{ old('price_quarterly') }}" type="number" step="0.01" placeholder="Prix trimestriel" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_semiannual" value="{{ old('price_semiannual') }}" type="number" step="0.01" placeholder="Prix semestriel" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_yearly" value="{{ old('price_yearly') }}" type="number" step="0.01" placeholder="Prix annuel" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="photos_limit" value="{{ old('photos_limit') }}" type="number" placeholder="Limite photos" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="description_chars" value="{{ old('description_chars') }}" type="number" placeholder="Limite description" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <select name="min_duration_months" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="1" @selected(old('min_duration_months') == 1)>Mensuel</option><option value="3" @selected(old('min_duration_months') == 3)>Trimestriel</option><option value="6" @selected(old('min_duration_months') == 6)>Semestriel</option><option value="12" @selected(old('min_duration_months') == 12)>Annuel</option>
                </select>
                <select name="support_level" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="email" @selected(old('support_level') === 'email')>Support email</option><option value="chat" @selected(old('support_level') === 'chat')>Support chat</option><option value="dedicated" @selected(old('support_level') === 'dedicated')>Support dédié</option>
                </select>
                <select name="stats_level" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="basic" @selected(old('stats_level') === 'basic')>Stats basic</option><option value="advanced" @selected(old('stats_level') === 'advanced')>Stats avancées</option><option value="full" @selected(old('stats_level') === 'full')>Stats complètes</option>
                </select>
                <input name="group_target" value="{{ old('group_target') }}" placeholder="Forfait spécial groupes/établissements" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <textarea name="benefits_text" placeholder="Description et avantages inclus" class="md:col-span-4 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">{{ old('benefits_text') }}</textarea>
                <div class="md:col-span-4 flex flex-wrap gap-4 text-sm text-slate-300">
                    <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', 1))> Actif</label>
                    <label><input type="checkbox" name="is_promotional" value="1" @checked(old('is_promotional'))> Forfait promotionnel</label>
                    <label><input type="checkbox" name="is_unlimited_features" value="1" @checked(old('is_unlimited_features'))> Fonctionnalités illimitées</label>
                    <label><input type="checkbox" name="has_video" value="1" @checked(old('has_video'))> Vidéo</label>
                    <label><input type="checkbox" name="has_newsletter" value="1" @checked(old('has_newsletter'))> Newsletter</label>
                    <label><input type="checkbox" name="has_homepage" value="1" @checked(old('has_homepage'))> Homepage</label>
                    <label><input type="checkbox" name="has_social_posts" value="1" @checked(old('has_social_posts'))> Social posts</label>
                    <label><input type="checkbox" name="has_verified_badge" value="1" @checked(old('has_verified_badge'))> Badge vérifié</label>
                </div>
                <div class="md:col-span-4 flex items-center gap-2">
                    <button class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Créer forfait</button>
                    <button type="button" id="cancel-create-plan-modal" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-800"><h2 class="text-white font-semibold">Forfaits existants</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-800 text-slate-500 text-xs uppercase"><th class="px-4 py-3 text-left">Forfait</th><th class="px-4 py-3 text-left">Durées/Prix</th><th class="px-4 py-3 text-left">Souscriptions</th><th class="px-4 py-3 text-left">Statut</th><th class="px-4 py-3 text-left">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-800">
            @foreach($plans as $plan)
                <tr>
                    <td class="px-4 py-3">
                        <p class="text-white font-semibold">{{ strtoupper($plan->code) }} - {{ $plan->name_fr }}</p>
                        <p class="text-slate-500 text-xs">{{ $plan->covered_levels ?: 'Niveaux non définis' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-300 text-xs">
                        M: {{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} |
                        T: {{ number_format((float) $plan->price_quarterly, 0, ',', ' ') }} |
                        S: {{ number_format((float) $plan->price_semiannual, 0, ',', ' ') }} |
                        A: {{ number_format((float) $plan->price_yearly, 0, ',', ' ') }}
                    </td>
                    <td class="px-4 py-3 text-white font-semibold">{{ $subscriptionsByPlan[$plan->code] ?? 0 }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded-full {{ $plan->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300' }}">
                            {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.plans.toggle', $plan) }}">@csrf @method('PATCH')
                            <button class="text-xs bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded">{{ $plan->is_active ? 'Désactiver' : 'Activer' }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold mb-4">Créer un code promo / réduction</h2>
    <form method="POST" action="{{ route('admin.promo-codes.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        @csrf
        <input name="code" placeholder="Code promo" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
        <select name="plan_id" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
            <option value="">Tous les forfaits</option>
            @foreach($plans as $plan)<option value="{{ $plan->id }}">{{ strtoupper($plan->code) }}</option>@endforeach
        </select>
        <select name="discount_type" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
            <option value="percent">Pourcentage</option><option value="fixed">Montant fixe</option>
        </select>
        <input name="discount_value" type="number" step="0.01" placeholder="Valeur remise" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
        <input name="starts_at" type="datetime-local" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
        <input name="ends_at" type="datetime-local" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
        <input name="max_uses" type="number" placeholder="Utilisations max" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
        <label class="text-sm text-slate-300 flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked> Actif</label>
        <textarea name="description" placeholder="Description promo" class="md:col-span-4 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm"></textarea>
        <div class="md:col-span-4"><button class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Créer code promo</button></div>
    </form>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800"><h2 class="text-white font-semibold">Codes promo existants</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-800 text-slate-500 text-xs uppercase"><th class="px-4 py-3 text-left">Code</th><th class="px-4 py-3 text-left">Plan</th><th class="px-4 py-3 text-left">Réduction</th><th class="px-4 py-3 text-left">Période</th><th class="px-4 py-3 text-left">Statut</th><th class="px-4 py-3 text-left"></th></tr></thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($promoCodes as $promo)
                    <tr>
                        <td class="px-4 py-3 text-white font-semibold">{{ $promo->code }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ $promo->plan?->code ? strtoupper($promo->plan->code) : 'Tous' }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ $promo->discount_type === 'percent' ? $promo->discount_value.'%' : number_format((float) $promo->discount_value,0,',',' ').' FCFA' }}</td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ optional($promo->starts_at)->format('d/m/Y') ?? '-' }} -> {{ optional($promo->ends_at)->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full {{ $promo->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300' }}">{{ $promo->is_active ? 'Actif' : 'Inactif' }}</span></td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.promo-codes.toggle', $promo) }}">@csrf @method('PATCH')
                                <button class="text-xs bg-slate-700 hover:bg-slate-600 px-3 py-1.5 rounded">{{ $promo->is_active ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-800">{{ $promoCodes->links() }}</div>
</div>

<script>
    (function () {
        const modal = document.getElementById('create-plan-modal');
        const openButton = document.getElementById('open-create-plan-modal');
        const closeButton = document.getElementById('close-create-plan-modal');
        const cancelButton = document.getElementById('cancel-create-plan-modal');
        const overlay = document.getElementById('create-plan-overlay');

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

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
</script>
@endsection
