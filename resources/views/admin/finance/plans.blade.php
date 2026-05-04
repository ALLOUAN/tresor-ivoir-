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

                {{-- ── Fonctionnalités publiques ── --}}
                <div class="md:col-span-4 border border-slate-700 rounded-xl p-4 bg-slate-800/50">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">
                            <i class="fas fa-list-check mr-1 text-amber-400"></i>
                            Fonctionnalités affichées sur la page publique
                        </p>
                        <button type="button" class="add-feature-btn text-xs bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 px-2.5 py-1 rounded" data-target="create">
                            <i class="fas fa-plus mr-1"></i>Ajouter
                        </button>
                    </div>
                    <div id="features-list-create" class="space-y-2 mb-1">
                        {{-- Rempli par JS --}}
                    </div>
                    <input type="hidden" name="features_json" id="features-json-create">
                </div>

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

{{-- ═══════════════ CYCLES DE FACTURATION ═══════════════ --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold mb-1">Cycles de facturation affichés publiquement</h2>
    <p class="text-slate-500 text-xs mb-4">Activez ou désactivez les boutons de cycle sur la page <code class="text-amber-400">/abonnements</code>.</p>

    @if(session('cycle_success'))
        <div class="mb-4 text-sm text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-4 py-2">
            {{ session('cycle_success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.payments.settings.save') }}" class="flex flex-wrap items-end gap-6">
        @csrf

        {{-- Mensuel --}}
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" name="cycle_monthly_active" value="1"
                       id="toggle_monthly"
                       @checked(($cycleSettings['cycle_monthly_active'] ?? '1') === '1')
                       class="sr-only peer">
                <div class="w-11 h-6 rounded-full bg-slate-700 peer-checked:bg-amber-500 transition-colors duration-200
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full
                            after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">Mensuel</p>
                <p class="text-xs text-slate-500">Afficher l'option mensuelle</p>
            </div>
        </label>

        {{-- Annuel --}}
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" name="cycle_yearly_active" value="1"
                       id="toggle_yearly"
                       @checked(($cycleSettings['cycle_yearly_active'] ?? '1') === '1')
                       class="sr-only peer">
                <div class="w-11 h-6 rounded-full bg-slate-700 peer-checked:bg-amber-500 transition-colors duration-200
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full
                            after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">Annuel</p>
                <p class="text-xs text-slate-500">Afficher l'option annuelle</p>
            </div>
        </label>

        {{-- Badge économie annuel --}}
        <div class="flex flex-col gap-1">
            <label for="cycle_yearly_savings_label" class="text-xs text-slate-400 font-medium uppercase tracking-wide">Badge annuel</label>
            <input type="text"
                   name="cycle_yearly_savings_label"
                   id="cycle_yearly_savings_label"
                   value="{{ $cycleSettings['cycle_yearly_savings_label'] ?? '-20%' }}"
                   placeholder="-20%"
                   maxlength="20"
                   class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white w-28 focus:border-amber-500 focus:outline-none">
            <p class="text-xs text-slate-600">Badge affiché sur le bouton Annuel</p>
        </div>

        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold h-fit">
            Enregistrer
        </button>
    </form>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-800"><h2 class="text-white font-semibold">Forfaits existants</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="px-4 py-3 text-left">Forfait</th>
                    <th class="px-4 py-3 text-left">Prix (M / T / S / A)</th>
                    <th class="px-4 py-3 text-left">Fonctionnalités</th>
                    <th class="px-4 py-3 text-left">Souscriptions</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
            @foreach($plans as $plan)
                <tr class="hover:bg-slate-800/30">
                    <td class="px-4 py-3">
                        <p class="text-white font-semibold">{{ strtoupper($plan->code) }} — {{ $plan->name_fr }}</p>
                        <p class="text-slate-500 text-xs mt-0.5">{{ $plan->covered_levels ?: '—' }}</p>
                        @if($plan->benefits_text)
                            <p class="text-slate-600 text-xs mt-1 max-w-xs truncate">{{ $plan->benefits_text }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs font-mono">
                        <div class="space-y-0.5 text-slate-300">
                            <div><span class="text-slate-500 w-5 inline-block">M</span> {{ number_format((float) $plan->price_monthly, 0, ',', ' ') }} FCFA</div>
                            @if($plan->price_quarterly)
                            <div><span class="text-slate-500 w-5 inline-block">T</span> {{ number_format((float) $plan->price_quarterly, 0, ',', ' ') }} FCFA</div>
                            @endif
                            @if($plan->price_semiannual)
                            <div><span class="text-slate-500 w-5 inline-block">S</span> {{ number_format((float) $plan->price_semiannual, 0, ',', ' ') }} FCFA</div>
                            @endif
                            <div><span class="text-slate-500 w-5 inline-block">A</span> {{ number_format((float) $plan->price_yearly, 0, ',', ' ') }} FCFA</div>
                        </div>
                        <div class="text-slate-600 text-xs mt-1">{{ $plan->photos_limit }} photos · {{ number_format($plan->description_chars) }} car.</div>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $featureIcons = [
                                ['label' => 'Badge',       'ok' => $plan->has_verified_badge],
                                ['label' => 'Vidéo',       'ok' => $plan->has_video],
                                ['label' => 'Accueil',     'ok' => $plan->has_homepage],
                                ['label' => 'Newsletter',  'ok' => $plan->has_newsletter],
                                ['label' => 'Social',      'ok' => $plan->has_social_posts],
                            ];
                        @endphp
                        <div class="flex flex-wrap gap-1">
                            @foreach($featureIcons as $fi)
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $fi['ok'] ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-700/50 text-slate-600 line-through' }}">
                                    {{ $fi['label'] }}
                                </span>
                            @endforeach
                        </div>
                        <div class="text-slate-500 text-xs mt-1.5">
                            Stats : <span class="text-slate-300">{{ $plan->stats_level }}</span> ·
                            Support : <span class="text-slate-300">{{ $plan->support_level }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-white font-semibold text-center">{{ $subscriptionsByPlan[$plan->code] ?? 0 }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded-full {{ $plan->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300' }}">
                            {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 flex items-center gap-2">
                        <button
                            type="button"
                            class="edit-plan-btn text-xs bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 px-3 py-1.5 rounded"
                            data-plan='@json($plan)'
                            data-action="{{ route('admin.plans.update', $plan) }}"
                        ><i class="fas fa-pen text-xs mr-1"></i>Modifier</button>
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

{{-- ═══════════════ MODAL MODIFIER FORFAIT ═══════════════ --}}
<div id="edit-plan-modal" class="fixed inset-0 z-50 hidden">
    <div id="edit-plan-overlay" class="absolute inset-0 bg-black/70"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-5xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
                <h2 class="text-white font-semibold">Modifier le forfait</h2>
                <button type="button" id="close-edit-plan-modal" class="text-slate-400 hover:text-white text-lg"><i class="fas fa-xmark"></i></button>
            </div>
            <form id="edit-plan-form" method="POST" action="" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-5">
                @csrf
                @method('PATCH')
                <input name="code" id="edit_code" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-400 cursor-not-allowed" readonly>
                <input name="name_fr" id="edit_name_fr" placeholder="Nom FR" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="name_en" id="edit_name_en" placeholder="Nom EN" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="covered_levels" id="edit_covered_levels" placeholder="Niveaux couverts" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_monthly" id="edit_price_monthly" type="number" step="1" placeholder="Prix mensuel (FCFA)" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_quarterly" id="edit_price_quarterly" type="number" step="1" placeholder="Prix trimestriel (FCFA)" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_semiannual" id="edit_price_semiannual" type="number" step="1" placeholder="Prix semestriel (FCFA)" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="price_yearly" id="edit_price_yearly" type="number" step="1" placeholder="Prix annuel (FCFA)" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="photos_limit" id="edit_photos_limit" type="number" placeholder="Limite photos" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="description_chars" id="edit_description_chars" type="number" placeholder="Limite description (caractères)" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <select name="min_duration_months" id="edit_min_duration_months" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="1">Mensuel (1 mois)</option>
                    <option value="3">Trimestriel (3 mois)</option>
                    <option value="6">Semestriel (6 mois)</option>
                    <option value="12">Annuel (12 mois)</option>
                </select>
                <select name="support_level" id="edit_support_level" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="email">Support email</option>
                    <option value="chat">Support chat</option>
                    <option value="dedicated">Support dédié</option>
                </select>
                <select name="stats_level" id="edit_stats_level" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                    <option value="basic">Stats basiques</option>
                    <option value="advanced">Stats avancées</option>
                    <option value="full">Stats complètes</option>
                </select>
                <input name="sort_order" id="edit_sort_order" type="number" placeholder="Ordre d'affichage" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="group_target" id="edit_group_target" placeholder="Forfait spécial groupes/établissements" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm md:col-span-2">
                <input name="promo_starts_at" id="edit_promo_starts_at" type="datetime-local" placeholder="Début promo" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="promo_ends_at" id="edit_promo_ends_at" type="datetime-local" placeholder="Fin promo" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <textarea name="benefits_text" id="edit_benefits_text" placeholder="Description et avantages inclus" class="md:col-span-4 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm h-20"></textarea>

                {{-- ── Fonctionnalités publiques ── --}}
                <div class="md:col-span-4 border border-slate-700 rounded-xl p-4 bg-slate-800/50">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">
                            <i class="fas fa-list-check mr-1 text-amber-400"></i>
                            Fonctionnalités affichées sur la page publique
                        </p>
                        <button type="button" class="add-feature-btn text-xs bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 px-2.5 py-1 rounded" data-target="edit">
                            <i class="fas fa-plus mr-1"></i>Ajouter
                        </button>
                    </div>
                    <div id="features-list-edit" class="space-y-2 mb-1">
                        {{-- Rempli par JS --}}
                    </div>
                    <input type="hidden" name="features_json" id="features-json-edit">
                </div>

                <div class="md:col-span-4 flex flex-wrap gap-4 text-sm text-slate-300">
                    <label><input type="checkbox" name="is_active" id="edit_is_active" value="1"> Actif</label>
                    <label><input type="checkbox" name="is_promotional" id="edit_is_promotional" value="1"> Forfait promotionnel</label>
                    <label><input type="checkbox" name="is_unlimited_features" id="edit_is_unlimited_features" value="1"> Fonctionnalités illimitées</label>
                    <label><input type="checkbox" name="has_video" id="edit_has_video" value="1"> Vidéo</label>
                    <label><input type="checkbox" name="has_newsletter" id="edit_has_newsletter" value="1"> Newsletter</label>
                    <label><input type="checkbox" name="has_homepage" id="edit_has_homepage" value="1"> Homepage</label>
                    <label><input type="checkbox" name="has_social_posts" id="edit_has_social_posts" value="1"> Social posts</label>
                    <label><input type="checkbox" name="has_verified_badge" id="edit_has_verified_badge" value="1"> Badge vérifié</label>
                </div>
                <div class="md:col-span-4 flex items-center gap-2">
                    <button class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Enregistrer les modifications</button>
                    <button type="button" id="cancel-edit-plan-modal" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {

    // ════════════════════════════════════════════════════════════════════════
    // ÉDITEUR DE FONCTIONNALITÉS
    // ════════════════════════════════════════════════════════════════════════

    const DEFAULT_FEATURES = [
        { label: 'Badge vérifié',         included: false },
        { label: 'Photos ()',             included: true  },
        { label: 'Vidéo de présentation', included: false },
        { label: 'Mise en avant accueil', included: false },
        { label: 'Campagne newsletter',   included: false },
        { label: 'Posts réseaux sociaux', included: false },
        { label: 'Statistiques avancées', included: false },
        { label: 'Support prioritaire',   included: false },
    ];

    function buildFeatureRow(feature) {
        const row = document.createElement('div');
        row.className = 'feature-row flex items-center gap-2 group';
        row.innerHTML = `
            <span class="text-slate-500 cursor-grab select-none px-1" title="Glisser pour réordonner">
                <i class="fas fa-grip-vertical text-xs"></i>
            </span>
            <input type="text"
                   value="${escHtml(feature.label)}"
                   placeholder="Libellé de la fonctionnalité…"
                   class="feature-label flex-1 bg-slate-700 border border-slate-600 rounded px-2.5 py-1.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
            <label class="flex items-center gap-1.5 text-sm text-slate-300 cursor-pointer whitespace-nowrap select-none">
                <input type="checkbox" class="feature-included accent-amber-500" ${feature.included ? 'checked' : ''}>
                <span class="text-xs">Inclus</span>
            </label>
            <button type="button"
                    class="remove-feature text-slate-600 hover:text-red-400 px-1 opacity-0 group-hover:opacity-100 transition-opacity"
                    title="Supprimer">
                <i class="fas fa-xmark text-xs"></i>
            </button>`;
        row.querySelector('.remove-feature').addEventListener('click', () => row.remove());
        return row;
    }

    function escHtml(str) {
        return (str ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function initFeaturesList(listId, features) {
        const list = document.getElementById(listId);
        if (!list) return;
        list.innerHTML = '';
        (features && features.length ? features : DEFAULT_FEATURES).forEach(f => {
            list.appendChild(buildFeatureRow(f));
        });
    }

    function serializeFeatures(listId, hiddenId) {
        const list   = document.getElementById(listId);
        const hidden = document.getElementById(hiddenId);
        if (!list || !hidden) return;
        const features = [...list.querySelectorAll('.feature-row')].map(row => ({
            label:    row.querySelector('.feature-label').value.trim(),
            included: row.querySelector('.feature-included').checked,
        })).filter(f => f.label !== '');
        hidden.value = JSON.stringify(features);
    }

    // Boutons "Ajouter une fonctionnalité"
    document.querySelectorAll('.add-feature-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.target; // 'create' ou 'edit'
            const list = document.getElementById(`features-list-${target}`);
            if (list) list.appendChild(buildFeatureRow({ label: '', included: true }));
        });
    });

    // ════════════════════════════════════════════════════════════════════════
    // MODAL CRÉER
    // ════════════════════════════════════════════════════════════════════════

    const createModal   = document.getElementById('create-plan-modal');
    const createOverlay = document.getElementById('create-plan-overlay');
    const openButton    = document.getElementById('open-create-plan-modal');
    const closeButton   = document.getElementById('close-create-plan-modal');
    const cancelButton  = document.getElementById('cancel-create-plan-modal');

    const openCreate  = () => {
        initFeaturesList('features-list-create', DEFAULT_FEATURES);
        createModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };
    const closeCreate = () => {
        createModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openButton?.addEventListener('click', openCreate);
    closeButton?.addEventListener('click', closeCreate);
    cancelButton?.addEventListener('click', closeCreate);
    createOverlay?.addEventListener('click', closeCreate);

    // Sérialiser avant soumission du formulaire de création
    createModal?.querySelector('form')?.addEventListener('submit', () => {
        serializeFeatures('features-list-create', 'features-json-create');
    });

    // ════════════════════════════════════════════════════════════════════════
    // MODAL MODIFIER
    // ════════════════════════════════════════════════════════════════════════

    const editModal   = document.getElementById('edit-plan-modal');
    const editOverlay = document.getElementById('edit-plan-overlay');
    const editForm    = document.getElementById('edit-plan-form');

    const openEdit  = () => { editModal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); };
    const closeEdit = () => { editModal.classList.add('hidden');    document.body.classList.remove('overflow-hidden'); };

    document.getElementById('close-edit-plan-modal')?.addEventListener('click', closeEdit);
    document.getElementById('cancel-edit-plan-modal')?.addEventListener('click', closeEdit);
    editOverlay?.addEventListener('click', closeEdit);

    editForm?.addEventListener('submit', () => {
        serializeFeatures('features-list-edit', 'features-json-edit');
    });

    const bool = v => v === true || v === 1 || v === '1';

    document.querySelectorAll('.edit-plan-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const plan = JSON.parse(btn.dataset.plan);
            editForm.action = btn.dataset.action;

            // Champs texte / numériques
            editForm.querySelector('#edit_code').value               = plan.code               ?? '';
            editForm.querySelector('#edit_name_fr').value            = plan.name_fr            ?? '';
            editForm.querySelector('#edit_name_en').value            = plan.name_en            ?? '';
            editForm.querySelector('#edit_covered_levels').value     = plan.covered_levels     ?? '';
            editForm.querySelector('#edit_price_monthly').value      = plan.price_monthly      ?? '';
            editForm.querySelector('#edit_price_quarterly').value    = plan.price_quarterly    ?? '';
            editForm.querySelector('#edit_price_semiannual').value   = plan.price_semiannual   ?? '';
            editForm.querySelector('#edit_price_yearly').value       = plan.price_yearly       ?? '';
            editForm.querySelector('#edit_photos_limit').value       = plan.photos_limit       ?? '';
            editForm.querySelector('#edit_description_chars').value  = plan.description_chars  ?? '';
            editForm.querySelector('#edit_group_target').value       = plan.group_target       ?? '';
            editForm.querySelector('#edit_sort_order').value         = plan.sort_order         ?? '';
            editForm.querySelector('#edit_benefits_text').value      = plan.benefits_text      ?? '';
            editForm.querySelector('#edit_promo_starts_at').value    = (plan.promo_starts_at   ?? '').replace(' ', 'T').slice(0, 16);
            editForm.querySelector('#edit_promo_ends_at').value      = (plan.promo_ends_at     ?? '').replace(' ', 'T').slice(0, 16);

            // Listes déroulantes
            editForm.querySelector('#edit_min_duration_months').value = plan.min_duration_months ?? '1';
            editForm.querySelector('#edit_support_level').value       = plan.support_level       ?? 'email';
            editForm.querySelector('#edit_stats_level').value         = plan.stats_level         ?? 'basic';

            // Cases à cocher
            editForm.querySelector('#edit_is_active').checked             = bool(plan.is_active);
            editForm.querySelector('#edit_is_promotional').checked        = bool(plan.is_promotional);
            editForm.querySelector('#edit_is_unlimited_features').checked = bool(plan.is_unlimited_features);
            editForm.querySelector('#edit_has_video').checked             = bool(plan.has_video);
            editForm.querySelector('#edit_has_newsletter').checked        = bool(plan.has_newsletter);
            editForm.querySelector('#edit_has_homepage').checked          = bool(plan.has_homepage);
            editForm.querySelector('#edit_has_social_posts').checked      = bool(plan.has_social_posts);
            editForm.querySelector('#edit_has_verified_badge').checked    = bool(plan.has_verified_badge);

            // Fonctionnalités
            initFeaturesList('features-list-edit', plan.features_json ?? null);

            openEdit();
        });
    });

    // ── Fermeture Échap ──────────────────────────────────────────────────────
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (!createModal.classList.contains('hidden')) closeCreate();
        if (!editModal.classList.contains('hidden'))   closeEdit();
    });

})();
</script>
@endsection
