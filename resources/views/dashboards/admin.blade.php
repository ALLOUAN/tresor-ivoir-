@extends('layouts.app')

@section('title', 'Administration')
@section('page-title', 'Tableau de bord — Administration')

@section('content')

{{-- ── KPI cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">

    @php
    $cards = [
        ['label' => 'Utilisateurs',       'value' => number_format($stats['total_users']),          'sub' => '+' . $stats['users_today'] . ' aujourd\'hui',  'icon' => 'fa-users',          'color' => 'text-blue-400',   'bg' => 'bg-blue-900/20'],
        ['label' => 'Prestataires actifs','value' => number_format($stats['active_providers']),     'sub' => $stats['pending_providers'] . ' en attente',    'icon' => 'fa-store',          'color' => 'text-violet-400', 'bg' => 'bg-violet-900/20'],
        ['label' => 'Articles publiés',   'value' => number_format($stats['published_articles']),   'sub' => $stats['articles_review'] . ' en révision',     'icon' => 'fa-newspaper',      'color' => 'text-amber-400',  'bg' => 'bg-amber-900/20'],
        ['label' => 'Avis en attente',    'value' => number_format($stats['pending_reviews']),      'sub' => 'À modérer',                                    'icon' => 'fa-star-half-stroke','color' => 'text-rose-400',   'bg' => 'bg-rose-900/20'],
        ['label' => 'Abonnements actifs', 'value' => number_format($stats['active_subscriptions']), 'sub' => 'Forfaits en cours',                            'icon' => 'fa-gem',            'color' => 'text-emerald-400','bg' => 'bg-emerald-900/20'],
    ];
    @endphp

    @foreach($cards as $card)
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-slate-400 text-xs font-medium">{{ $card['label'] }}</span>
            <div class="w-8 h-8 rounded-lg {{ $card['bg'] }} flex items-center justify-center">
                <i class="fas {{ $card['icon'] }} {{ $card['color'] }} text-sm"></i>
            </div>
        </div>
        <div>
            <p class="text-white text-2xl font-bold">{{ $card['value'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">{{ $card['sub'] }}</p>
        </div>
    </div>
    @endforeach

    {{-- Revenue card (full width on small, spans 2 on xl) --}}
    <div class="col-span-2 md:col-span-3 xl:col-span-5 bg-gradient-to-r from-amber-900/30 to-amber-800/10 border border-amber-700/30 rounded-xl p-4 flex items-center justify-between">
        <div>
            <p class="text-amber-300 text-sm font-medium mb-1">Revenu du mois</p>
            <p class="text-white text-3xl font-bold">
                {{ number_format($stats['monthly_revenue'], 0, ',', ' ') }}
                <span class="text-amber-400 text-lg font-normal">FCFA</span>
            </p>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center">
            <i class="fas fa-coins text-amber-400 text-2xl"></i>
        </div>
    </div>
</div>

{{-- ── Two-column section ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Recent users --}}
    <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold text-sm">Derniers utilisateurs inscrits</h2>
            <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Voir tout →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase tracking-wide border-b border-slate-800">
                        <th class="text-left px-5 py-3">Utilisateur</th>
                        <th class="text-left px-5 py-3 hidden sm:table-cell">Rôle</th>
                        <th class="text-left px-5 py-3 hidden md:table-cell">Inscription</th>
                        <th class="text-left px-5 py-3">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($recent_users as $user)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-300 shrink-0">
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $user->first_name }} {{ $user->last_name }}</p>
                                    <p class="text-slate-500 text-xs">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 hidden sm:table-cell">
                            @php
                            $roleColors = ['admin'=>'rose','editor'=>'blue','provider'=>'violet','visitor'=>'emerald'];
                            $c = $roleColors[$user->role] ?? 'slate';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/40 text-{{ $c }}-300 border border-{{ $c }}-800">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell text-slate-400 text-xs">
                            {{ $user->created_at->diffForHumans() }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-block w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500 text-sm">Aucun utilisateur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sidebar stats --}}
    <div class="space-y-4">

        {{-- Providers by status --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4">Prestataires par statut</h2>
            <div class="space-y-3">
                @php
                $statusColors = ['active'=>['bar'=>'bg-emerald-500','text'=>'text-emerald-400'],'pending'=>['bar'=>'bg-amber-500','text'=>'text-amber-400'],'suspended'=>['bar'=>'bg-red-500','text'=>'text-red-400'],'inactive'=>['bar'=>'bg-slate-600','text'=>'text-slate-400']];
                $total_providers = $providers_by_status->sum() ?: 1;
                @endphp
                @forelse($providers_by_status as $status => $count)
                @php $cols = $statusColors[$status] ?? ['bar'=>'bg-slate-500','text'=>'text-slate-400']; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-300 text-xs capitalize">{{ $status }}</span>
                        <span class="{{ $cols['text'] }} text-xs font-semibold">{{ $count }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="{{ $cols['bar'] }} h-full rounded-full" style="width: {{ round($count / $total_providers * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-slate-500 text-xs">Aucune donnée.</p>
                @endforelse
            </div>
        </div>

        {{-- Subscriptions by plan --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4">Abonnements actifs</h2>
            @php
            $planColors = ['bronze'=>'text-amber-700','silver'=>'text-slate-300','gold'=>'text-amber-400'];
            @endphp
            <div class="space-y-2">
                @forelse($subscriptions_by_plan as $plan => $count)
                <div class="flex items-center justify-between bg-slate-800 rounded-lg px-3 py-2">
                    <span class="{{ $planColors[strtolower($plan)] ?? 'text-slate-300' }} text-sm font-medium capitalize">
                        <i class="fas fa-gem mr-1.5 text-xs"></i>{{ $plan }}
                    </span>
                    <span class="text-white text-sm font-bold">{{ $count }}</span>
                </div>
                @empty
                <p class="text-slate-500 text-xs">Aucun abonnement actif.</p>
                @endforelse
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-3">Actions rapides</h2>
            <div class="space-y-2">
                <a href="#" class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition">
                    <i class="fas fa-user-plus text-amber-400 w-4"></i> Créer un utilisateur
                </a>
                <a href="#" class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition">
                    <i class="fas fa-circle-check text-emerald-400 w-4"></i> Valider des prestataires
                </a>
                <a href="#" class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition">
                    <i class="fas fa-envelope-open-text text-blue-400 w-4"></i> Envoyer newsletter
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Pending reviews ────────────────────────────────────────────────── --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-star-half-stroke text-rose-400"></i>
            Avis en attente de modération
            @if($stats['pending_reviews'] > 0)
            <span class="bg-rose-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $stats['pending_reviews'] }}</span>
            @endif
        </h2>
        <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Gérer →</a>
    </div>
    <div class="divide-y divide-slate-800">
        @forelse($pending_reviews as $review)
        <div class="px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-white text-sm font-medium">{{ $review->user->first_name ?? 'Anonyme' }}</span>
                    <span class="text-slate-500 text-xs">→</span>
                    <span class="text-amber-400 text-sm">{{ $review->provider->business_name ?? '—' }}</span>
                </div>
                <p class="text-slate-400 text-xs truncate">{{ $review->comment }}</p>
                <div class="flex items-center gap-1 mt-1.5">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-700' }}"></i>
                    @endfor
                    <span class="text-slate-500 text-xs ml-1">{{ $review->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button class="px-3 py-1 bg-emerald-700 hover:bg-emerald-600 text-white text-xs rounded-lg transition">
                    <i class="fas fa-check mr-1"></i>Approuver
                </button>
                <button class="px-3 py-1 bg-slate-800 hover:bg-red-900 text-slate-300 hover:text-red-300 text-xs rounded-lg transition">
                    <i class="fas fa-times mr-1"></i>Rejeter
                </button>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-slate-500 text-sm">
            <i class="fas fa-check-circle text-emerald-400 text-2xl mb-2 block"></i>
            Aucun avis en attente. Tout est à jour !
        </div>
        @endforelse
    </div>
</div>

@endsection
