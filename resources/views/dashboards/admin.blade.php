@extends('layouts.app')

@section('title', 'Administration')
@section('page-title', 'Tableau de bord — Administration')

@section('content')

{{-- ── KPI cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">

    @php
    $cards = [
        ['label' => 'Utilisateurs',       'value' => number_format($stats['total_users']),          'sub' => '+' . $stats['users_today'] . ' aujourd\'hui',  'icon' => 'fa-users',          'color' => 'text-green-400',   'bg' => 'bg-green-900/20'],
        ['label' => 'Prestataires actifs','value' => number_format($stats['active_providers']),     'sub' => $stats['pending_providers'] . ' en attente',    'icon' => 'fa-store',          'color' => 'text-green-400', 'bg' => 'bg-green-900/20'],
        ['label' => 'Articles publiés',   'value' => number_format($stats['published_articles']),   'sub' => $stats['articles_review'] . ' en révision',     'icon' => 'fa-newspaper',      'color' => 'text-orange-400',  'bg' => 'bg-orange-900/20'],
        ['label' => 'Avis en attente',    'value' => number_format($stats['pending_reviews']),      'sub' => 'À modérer',                                    'icon' => 'fa-star-half-stroke','color' => 'text-rose-400',   'bg' => 'bg-rose-900/20'],
        ['label' => 'Abonnements actifs', 'value' => number_format($stats['active_subscriptions']), 'sub' => 'Forfaits en cours',                            'icon' => 'fa-gem',            'color' => 'text-emerald-400','bg' => 'bg-emerald-900/20'],
    ];
    @endphp

    @foreach($cards as $card)
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 flex flex-col gap-3">
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
    <div class="col-span-2 md:col-span-3 xl:col-span-5 bg-gradient-to-r from-orange-900/30 to-orange-800/10 border border-orange-700/30 rounded-xl p-4 flex items-center justify-between">
        <div>
            <p class="text-orange-300 text-sm font-medium mb-1">Revenu du mois</p>
            <p class="text-white text-3xl font-bold">
                {{ number_format($stats['monthly_revenue'], 0, ',', ' ') }}
                <span class="text-orange-400 text-lg font-normal">FCFA</span>
            </p>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-orange-500/20 flex items-center justify-center">
            <i class="fas fa-coins text-orange-400 text-2xl"></i>
        </div>
    </div>
</div>

{{-- ── Charts ──────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Revenue trend --}}
    <div class="xl:col-span-2 bg-green-900 border border-slate-800 rounded-xl p-5">
        @php
            $revenueDelta = null;
            $revVals = $revenue_by_month['values'];
            $prevMonth = $revVals[count($revVals) - 2] ?? 0;
            $curMonth = $revVals[count($revVals) - 1] ?? 0;
            if ($prevMonth > 0) {
                $revenueDelta = round(($curMonth - $prevMonth) / $prevMonth * 100, 1);
            }
        @endphp
        <div class="flex items-start justify-between mb-1">
            <div>
                <h2 class="text-white font-semibold text-sm">Revenu — 12 derniers mois</h2>
                <p class="text-slate-500 text-xs mt-0.5">Paiements complétés, par mois</p>
            </div>
            @if($revenueDelta !== null)
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold {{ $revenueDelta >= 0 ? 'bg-emerald-900/30 text-emerald-400' : 'bg-red-900/30 text-red-400' }}">
                <i class="fas {{ $revenueDelta >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                {{ $revenueDelta >= 0 ? '+' : '' }}{{ $revenueDelta }}%
            </span>
            @endif
        </div>
        <div class="h-64 mt-3">
            <canvas id="chartRevenue"></canvas>
        </div>
    </div>

    {{-- New signups trend --}}
    <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold text-sm">Nouvelles inscriptions</h2>
        <p class="text-slate-500 text-xs mt-0.5">14 derniers jours</p>
        <div class="h-64 mt-3">
            <canvas id="chartSignups"></canvas>
        </div>
    </div>
</div>

{{-- ── Two-column section ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Recent users --}}
    <div class="xl:col-span-2 bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold text-sm">Derniers utilisateurs inscrits</h2>
            <a href="#" class="text-orange-400 hover:text-orange-300 text-xs transition">Voir tout →</a>
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
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4">Prestataires par statut</h2>
            @php
                $statusMeta = [
                    'active'    => ['label' => 'Actifs',     'color' => '#059669'],
                    'pending'   => ['label' => 'En attente', 'color' => '#9f4709'],
                    'suspended' => ['label' => 'Suspendus',  'color' => '#ef4444'],
                    'inactive'  => ['label' => 'Inactifs',   'color' => '#64748b'],
                ];
                $total_providers = $providers_by_status->sum() ?: 1;
            @endphp
            @if($providers_by_status->sum() > 0)
            <div class="h-10">
                <canvas id="chartProviderStatus"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4">
                @foreach($providers_by_status as $status => $count)
                @php $meta = $statusMeta[$status] ?? ['label' => ucfirst($status), 'color' => '#64748b']; @endphp
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $meta['color'] }}"></span>
                    <span class="text-slate-400 flex-1 truncate">{{ $meta['label'] }}</span>
                    <span class="text-white font-semibold">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-500 text-xs">Aucune donnée.</p>
            @endif
        </div>

        {{-- Subscriptions by plan --}}
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-4">Abonnements actifs par forfait</h2>
            @if($subscriptions_by_plan->sum() > 0)
            <div style="height: {{ max(120, $subscriptions_by_plan->count() * 44) }}px">
                <canvas id="chartSubscriptionPlans"></canvas>
            </div>
            @else
            <p class="text-slate-500 text-xs">Aucun abonnement actif.</p>
            @endif
        </div>

        {{-- Quick actions --}}
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-sm mb-3">Actions rapides</h2>
            <div class="space-y-2">
                <a href="#" class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition">
                    <i class="fas fa-user-plus text-orange-400 w-4"></i> Créer un utilisateur
                </a>
                <a href="#" class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition">
                    <i class="fas fa-circle-check text-emerald-400 w-4"></i> Valider des prestataires
                </a>
                <a href="{{ route('admin.newsletter.index') }}" class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition">
                    <i class="fas fa-envelope-open-text text-green-400 w-4"></i> Envoyer newsletter
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Activité récente des prestataires ──────────────────────────────── --}}
<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden mb-6">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-store text-orange-400"></i>
            Activité récente des prestataires
        </h2>
        <a href="{{ route('admin.providers.index') }}" class="text-orange-400 hover:text-orange-300 text-xs transition">Tous les prestataires →</a>
    </div>
    <div class="divide-y divide-slate-800">
        @php
            $activityColors = [
                'orange' => 'bg-orange-900/30 text-orange-400',
                'emerald' => 'bg-emerald-900/30 text-emerald-400',
                'sky' => 'bg-sky-900/30 text-sky-400',
                'violet' => 'bg-violet-900/30 text-violet-400',
                'rose' => 'bg-rose-900/30 text-rose-400',
            ];
        @endphp
        @forelse($recent_provider_activity as $activity)
        <a href="{{ $activity['url'] }}" class="flex items-center gap-3 px-5 py-3 hover:bg-slate-800/50 transition">
            <div class="w-8 h-8 rounded-lg {{ $activityColors[$activity['color']] ?? 'bg-slate-800 text-slate-400' }} flex items-center justify-center shrink-0">
                <i class="fas {{ $activity['icon'] }} text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm truncate">{{ $activity['label'] }}</p>
                <p class="text-slate-500 text-xs truncate">
                    {{ $activity['title'] }}
                    @if($activity['provider'])
                        <span class="text-slate-600">·</span> {{ $activity['provider'] }}
                    @endif
                </p>
            </div>
            <span class="text-slate-500 text-xs shrink-0">{{ $activity['date']->diffForHumans() }}</span>
        </a>
        @empty
        <div class="px-5 py-8 text-center text-slate-500 text-sm">
            <i class="fas fa-inbox text-2xl mb-2 block opacity-40"></i>
            Aucune activité récente de la part des prestataires.
        </div>
        @endforelse
    </div>
</div>

{{-- ── Pending reviews ────────────────────────────────────────────────── --}}
<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-star-half-stroke text-rose-400"></i>
            Avis en attente de modération
            @if($stats['pending_reviews'] > 0)
            <span class="bg-rose-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $stats['pending_reviews'] }}</span>
            @endif
        </h2>
        <a href="#" class="text-orange-400 hover:text-orange-300 text-xs transition">Gérer →</a>
    </div>
    <div class="divide-y divide-slate-800">
        @forelse($pending_reviews as $review)
        <div class="px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-white text-sm font-medium">{{ $review->user->first_name ?? 'Anonyme' }}</span>
                    <span class="text-slate-500 text-xs">→</span>
                    <span class="text-orange-400 text-sm">{{ $review->provider->business_name ?? '—' }}</span>
                </div>
                <p class="text-slate-400 text-xs truncate">{{ $review->comment }}</p>
                <div class="flex items-center gap-1 mt-1.5">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-orange-400' : 'text-slate-700' }}"></i>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
{{--
    Les couleurs Chart.js sont dessinées sur un <canvas> : elles échappent totalement
    aux règles CSS (partials/theme-light-bridge.blade.php), qui ne peuvent recolorer
    que du texte HTML. Le site n'a plus qu'un seul thème (clair) ; isDarkMode()
    retourne donc toujours false et les graphiques utilisent la palette claire.
--}}
function isDarkMode() {
    return document.getElementById('html-root')?.classList.contains('dark') ?? true;
}

function chartPalette() {
    const dark = isDarkMode();
    return {
        surface: dark ? '#e9e5d9' : '#e9e5d9',
        grid: dark ? 'rgba(148,163,184,0.14)' : 'rgba(28,25,21,0.08)',
        tick: dark ? '#94a3b8' : '#544f47',
        tickStrong: dark ? '#e2e8f0' : '#1c1915',
        tooltipTitle: dark ? '#f8fafc' : '#1c1915',
        tooltipBody: dark ? '#cbd5e1' : '#44413a',
        tooltipBorder: dark ? 'rgba(148,163,184,0.25)' : 'rgba(28,25,21,0.14)',
    };
}

function baseChartDefaults(p) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: p.surface,
                titleColor: p.tooltipTitle,
                bodyColor: p.tooltipBody,
                borderColor: p.tooltipBorder,
                borderWidth: 1,
                padding: 10,
                displayColors: false,
                titleFont: { weight: '600' },
            },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: p.tick, font: { size: 10 } },
                border: { display: false },
            },
            y: {
                grid: { color: p.grid, drawBorder: false },
                ticks: { color: p.tick, font: { size: 10 } },
                border: { display: false },
                beginAtZero: true,
            },
        },
    };
}

const revenueLabels = @json($revenue_by_month['labels']);
const revenueValues = @json($revenue_by_month['values']);
const signupsLabels = @json($signups_by_day['labels']);
const signupsValues = @json($signups_by_day['values']);
const statusData = @json($providers_by_status);
const statusMeta = {
    active:    { label: 'Actifs',     color: '#059669' },
    pending:   { label: 'En attente', color: '#9f4709' },
    suspended: { label: 'Suspendus',  color: '#ef4444' },
    inactive:  { label: 'Inactifs',   color: '#64748b' },
};
const planData = @json($subscriptions_by_plan);
const planMeta = {
    bronze: { label: 'Bronze', color: '#a54a0b' },
    silver: { label: 'Argent', color: '#0891b2' },
    gold:   { label: 'Or',     color: '#a16207' },
};

const dashboardCharts = {};

function buildRevenueChart() {
    const canvas = document.getElementById('chartRevenue');
    if (!canvas) return;
    dashboardCharts.revenue?.destroy();
    const p = chartPalette();
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(242, 121, 15,0.4)');
    gradient.addColorStop(1, 'rgba(242, 121, 15,0.02)');

    dashboardCharts.revenue = new Chart(canvas, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                data: revenueValues,
                borderColor: '#f2790f',
                backgroundColor: gradient,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointBackgroundColor: '#fa9a3c',
                pointHoverBorderWidth: 2,
                pointHoverBorderColor: p.surface,
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            ...baseChartDefaults(p),
            plugins: {
                ...baseChartDefaults(p).plugins,
                tooltip: {
                    ...baseChartDefaults(p).plugins.tooltip,
                    callbacks: {
                        label: (ctx) => new Intl.NumberFormat('fr-FR').format(ctx.parsed.y) + ' FCFA',
                    },
                },
            },
            scales: {
                ...baseChartDefaults(p).scales,
                y: {
                    ...baseChartDefaults(p).scales.y,
                    ticks: {
                        ...baseChartDefaults(p).scales.y.ticks,
                        callback: (v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v),
                    },
                },
            },
        },
    });
}

function buildSignupsChart() {
    const canvas = document.getElementById('chartSignups');
    if (!canvas) return;
    dashboardCharts.signups?.destroy();
    const p = chartPalette();
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(59,130,246,0.95)');
    gradient.addColorStop(1, 'rgba(59,130,246,0.45)');

    dashboardCharts.signups = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: signupsLabels,
            datasets: [{
                data: signupsValues,
                backgroundColor: gradient,
                borderRadius: 4,
                borderSkipped: false,
                maxBarThickness: 18,
                hoverBackgroundColor: '#60a5fa',
            }],
        },
        options: {
            ...baseChartDefaults(p),
            scales: {
                ...baseChartDefaults(p).scales,
                y: { ...baseChartDefaults(p).scales.y, ticks: { ...baseChartDefaults(p).scales.y.ticks, precision: 0 } },
            },
        },
    });
}

function buildProviderStatusChart() {
    const canvas = document.getElementById('chartProviderStatus');
    if (!canvas) return;
    dashboardCharts.providerStatus?.destroy();
    const p = chartPalette();
    const statusDatasets = Object.entries(statusData).map(([status, count]) => ({
        label: statusMeta[status]?.label ?? status,
        data: [count],
        backgroundColor: statusMeta[status]?.color ?? '#64748b',
        borderRadius: 4,
        borderSkipped: false,
        borderWidth: 2,
        borderColor: p.surface,
    }));

    dashboardCharts.providerStatus = new Chart(canvas, {
        type: 'bar',
        data: { labels: ['Prestataires'], datasets: statusDatasets },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: p.surface,
                    titleColor: p.tooltipTitle,
                    bodyColor: p.tooltipBody,
                    borderColor: p.tooltipBorder,
                    borderWidth: 1,
                    padding: 10,
                    callbacks: { title: (items) => statusDatasets[items[0].datasetIndex].label },
                },
            },
            scales: {
                x: { stacked: true, display: false },
                y: { stacked: true, display: false },
            },
        },
    });
}

function buildPlansChart() {
    const canvas = document.getElementById('chartSubscriptionPlans');
    if (!canvas) return;
    dashboardCharts.plans?.destroy();
    const p = chartPalette();
    const planLabels = Object.keys(planData).map((k) => planMeta[k.toLowerCase()]?.label ?? k);
    const planColors = Object.keys(planData).map((k) => planMeta[k.toLowerCase()]?.color ?? '#64748b');

    dashboardCharts.plans = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: planLabels,
            datasets: [{
                data: Object.values(planData),
                backgroundColor: planColors,
                borderRadius: 4,
                borderSkipped: false,
                maxBarThickness: 22,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: p.surface,
                    titleColor: p.tooltipTitle,
                    bodyColor: p.tooltipBody,
                    borderColor: p.tooltipBorder,
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                },
            },
            scales: {
                x: { grid: { color: p.grid, drawBorder: false }, ticks: { color: p.tick, font: { size: 10 }, precision: 0 }, border: { display: false }, beginAtZero: true },
                y: { grid: { display: false }, ticks: { color: p.tickStrong, font: { size: 12, weight: '600' } }, border: { display: false } },
            },
        },
    });
}

function buildAllDashboardCharts() {
    buildRevenueChart();
    buildSignupsChart();
    buildProviderStatusChart();
    buildPlansChart();
}

buildAllDashboardCharts();

const htmlRoot = document.getElementById('html-root');
if (htmlRoot) {
    new MutationObserver(buildAllDashboardCharts).observe(htmlRoot, { attributes: true, attributeFilter: ['class'] });
}
</script>
@endpush
