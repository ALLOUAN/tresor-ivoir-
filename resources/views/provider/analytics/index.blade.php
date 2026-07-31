@extends('layouts.app')

@section('title', 'Statistiques')
@section('page-title', 'Statistiques de votre fiche')

@section('content')
<style>
    .premium-shimmer-card {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }
    .premium-shimmer-card::after {
        content: '';
        position: absolute;
        top: -130%;
        left: -45%;
        width: 38%;
        height: 360%;
        transform: rotate(22deg) translateX(-180%);
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.04) 35%, rgba(255,255,255,0.16) 50%, rgba(255,255,255,0.04) 65%, transparent 100%);
        pointer-events: none;
        transition: transform .85s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .premium-shimmer-card:hover::after {
        transform: rotate(22deg) translateX(520%);
    }
    @media (prefers-reduced-motion: reduce) {
        .premium-shimmer-card::after { transition: none; }
    }
</style>

@php
    $growth = function(int $current, int $prev): ?float {
        if ($prev === 0) return null;
        return round(($current - $prev) / $prev * 100, 1);
    };
    $viewsGrowth   = $growth($totals['views'], $prevTotals['views']);
    $phoneGrowth   = $growth($totals['clicks_phone'], $prevTotals['clicks_phone']);
    $websiteGrowth = $growth($totals['clicks_website'], $prevTotals['clicks_website']);
@endphp

{{-- Period selector --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <p class="text-slate-400 text-sm">Période : <span class="text-white font-medium">{{ $period }} derniers jours</span></p>
    <div class="flex gap-2">
        @foreach([7 => '7 j', 30 => '30 j', 90 => '90 j'] as $val => $label)
        <a href="{{ route('provider.analytics', ['period' => $val]) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition
                  {{ $period === $val ? 'bg-orange-500 text-white' : 'bg-slate-800 text-slate-400 hover:text-white border border-slate-700' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

@if(! $provider)
<div class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center">
    <i class="fas fa-store text-slate-600 text-4xl mb-3"></i>
    <p class="text-slate-400">Créez votre fiche prestataire pour accéder aux statistiques.</p>
    <a href="{{ route('provider.profile.edit') }}" class="mt-4 inline-flex bg-orange-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">Créer ma fiche</a>
</div>
@else

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @php
        $kpis = [
            ['icon' => 'fa-eye',        'label' => 'Vues de la fiche',      'value' => $totals['views'],              'growth' => $viewsGrowth,   'color' => 'text-green-400',    'bg' => 'bg-green-500/10'],
            ['icon' => 'fa-phone',      'label' => 'Clics téléphone',       'value' => $totals['clicks_phone'],       'growth' => $phoneGrowth,   'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10'],
            ['icon' => 'fa-globe',      'label' => 'Clics site web',        'value' => $totals['clicks_website'],     'growth' => $websiteGrowth, 'color' => 'text-green-400',  'bg' => 'bg-green-500/10'],
            ['icon' => 'fa-location-dot','label'=> 'Clics itinéraire',      'value' => $totals['clicks_direction'],   'growth' => null,           'color' => 'text-orange-400',   'bg' => 'bg-orange-500/10'],
            ['icon' => 'fa-star',       'label' => 'Nouveaux avis',         'value' => $totals['new_reviews'],        'growth' => null,           'color' => 'text-orange-400',  'bg' => 'bg-orange-500/10'],
            ['icon' => 'fa-magnifying-glass','label'=>'Apparitions recherche','value'=> $totals['search_appearances'],'growth'=> null,           'color' => 'text-green-400',     'bg' => 'bg-green-500/10'],
        ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="premium-shimmer-card bg-linear-to-br from-green-900 via-green-900 to-green-950 border border-slate-700/70 rounded-xl p-4 shadow-lg shadow-green-950/20">
        <div class="flex items-start justify-between mb-3">
            <div class="{{ $kpi['bg'] }} w-10 h-10 rounded-xl flex items-center justify-center border border-white/5">
                <i class="fas {{ $kpi['icon'] }} {{ $kpi['color'] }} text-sm"></i>
            </div>
            @if($kpi['growth'] !== null)
            <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full
                {{ $kpi['growth'] >= 0 ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400' }}">
                {{ $kpi['growth'] >= 0 ? '+' : '' }}{{ $kpi['growth'] }}%
            </span>
            @endif
        </div>
        <p class="text-2xl font-bold text-white tracking-tight">{{ number_format($kpi['value'], 0, ',', ' ') }}</p>
        <p class="text-slate-400 text-xs mt-1">{{ $kpi['label'] }}</p>
        @if($kpi['growth'] !== null)
        <p class="text-slate-600 text-xs mt-1">vs période précédente</p>
        @endif
    </div>
    @endforeach
</div>

{{-- Charts --}}
@if(count($chartDates) > 0)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
    {{-- Views chart --}}
    <div class="bg-linear-to-br from-green-900 via-green-900 to-green-950 border border-green-500/20 rounded-xl p-5 shadow-lg shadow-green-900/20">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold text-sm">Vues de la fiche</h3>
            <span class="text-[10px] px-2 py-1 rounded-full bg-green-500/15 text-green-300 border border-green-400/20">Tendance</span>
        </div>
        <canvas id="chartViews" height="180"></canvas>
    </div>
    {{-- Clicks chart --}}
    <div class="bg-linear-to-br from-green-900 via-green-900 to-green-950 border border-orange-500/20 rounded-xl p-5 shadow-lg shadow-orange-900/20">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold text-sm">Clics (téléphone + site + itinéraire)</h3>
            <span class="text-[10px] px-2 py-1 rounded-full bg-orange-500/15 text-orange-300 border border-orange-400/20">Performance</span>
        </div>
        <canvas id="chartClicks" height="180"></canvas>
    </div>
</div>
@else
<div class="bg-linear-to-br from-green-900 via-green-900 to-green-950 border border-slate-700/70 rounded-xl p-10 text-center mb-8 shadow-lg shadow-green-950/20">
    <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center mb-4">
        <i class="fas fa-chart-line text-slate-500 text-2xl"></i>
    </div>
    <p class="text-slate-200 font-medium">Aucune donnée pour cette période.</p>
    <p class="text-slate-500 text-xs mt-1">Dès que votre fiche reçoit des interactions, les graphiques modernes apparaissent ici.</p>
</div>
@endif

{{-- Clicks breakdown --}}
@if($totals['clicks_phone'] + $totals['clicks_website'] + $totals['clicks_direction'] > 0)
<div class="bg-linear-to-br from-green-900 via-green-900 to-green-950 border border-green-500/20 rounded-xl p-5 shadow-lg shadow-green-900/20">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-white font-semibold text-sm">Répartition des clics</h3>
        <span class="text-[10px] px-2 py-1 rounded-full bg-green-500/15 text-green-300 border border-green-400/20">Doughnut</span>
    </div>
    @php
        $totalClicks = $totals['clicks_phone'] + $totals['clicks_website'] + $totals['clicks_direction'];
        $bars = [
            ['label' => 'Téléphone',   'value' => $totals['clicks_phone'],     'color' => 'bg-emerald-500'],
            ['label' => 'Site web',    'value' => $totals['clicks_website'],   'color' => 'bg-green-500'],
            ['label' => 'Itinéraire',  'value' => $totals['clicks_direction'], 'color' => 'bg-orange-500'],
        ];
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-center">
        <div class="lg:col-span-2">
            <canvas id="chartClicksDoughnut" height="220"></canvas>
        </div>
        <div class="lg:col-span-3 space-y-3">
            @foreach($bars as $bar)
            @php $pct = $totalClicks > 0 ? round($bar['value'] / $totalClicks * 100) : 0; @endphp
            <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-green-900/70 px-3 py-2">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full {{ $bar['color'] }}"></span>
                    <span class="text-slate-300">{{ $bar['label'] }}</span>
                </div>
                <span class="text-slate-300 text-xs font-medium">{{ number_format($bar['value'], 0, ',', ' ') }} ({{ $pct }}%)</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if(($totals['clicks_phone'] + $totals['clicks_website'] + $totals['clicks_direction']) === 0)
<div class="bg-linear-to-br from-green-900 via-green-900 to-green-950 border border-slate-700/70 rounded-xl p-5 shadow-lg shadow-green-950/20">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-white font-semibold text-sm">Répartition des clics</h3>
        <span class="text-[10px] px-2 py-1 rounded-full bg-slate-700/70 text-slate-300 border border-slate-600">Doughnut</span>
    </div>
    <div class="text-center py-8">
        <i class="fas fa-circle-notch text-slate-600 text-2xl mb-2"></i>
        <p class="text-slate-400 text-sm">Pas encore de clics à répartir.</p>
    </div>
</div>
@endif

@endif {{-- end $provider check --}}

@endsection

@push('scripts')
@if(count($chartDates) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
{{-- Chart.js dessine sur <canvas> : ces couleurs échappent au pont CSS mode-clair,
     donc on les recalcule depuis l'état réel du thème et on reconstruit au changement. --}}
function isDarkMode() {
    return document.getElementById('html-root')?.classList.contains('dark') ?? true;
}
function palette() {
    const dark = isDarkMode();
    return {
        surface: dark ? '#e9e5d9' : '#e9e5d9',
        ring: dark ? 'rgba(255, 255, 255,0.9)' : 'rgba(255,255,255,0.9)',
        tick: dark ? '#94a3b8' : '#544f47',
        gridX: dark ? 'rgba(255, 255, 255,0.35)' : 'rgba(28,25,21,0.08)',
        gridY: dark ? 'rgba(148,163,184,0.14)' : 'rgba(28,25,21,0.08)',
        tooltipTitle: dark ? '#f8fafc' : '#1c1915',
        tooltipBody: dark ? '#cbd5e1' : '#44413a',
        tooltipBorder: dark ? 'rgba(148,163,184,0.25)' : 'rgba(28,25,21,0.14)',
    };
}

function chartDefaults(p) {
    return {
        responsive: true,
        maintainAspectRatio: true,
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
                grid: { color: p.gridX, drawBorder: false },
                ticks: { color: p.tick, font: { size: 10 } },
                border: { display: false },
            },
            y: {
                grid: { color: p.gridY, drawBorder: false },
                ticks: { color: p.tick, font: { size: 10 } },
                border: { display: false },
                beginAtZero: true,
            },
        },
    };
}

const labels = @json($chartDates);
const viewsData = @json($chartViews);
const clicksData = @json($chartClicks);
const doughnutData = @json([(int) $totals['clicks_phone'], (int) $totals['clicks_website'], (int) $totals['clicks_direction']]);
const viewsCanvas = document.getElementById('chartViews');
const clicksCanvas = document.getElementById('chartClicks');
const clicksDoughnutCanvas = document.getElementById('chartClicksDoughnut');

let viewsChart, clicksChart, doughnutChart;

function buildAnalyticsCharts() {
    const p = palette();
    const defaults = chartDefaults(p);

    const viewsCtx = viewsCanvas.getContext('2d');
    const viewsGradient = viewsCtx.createLinearGradient(0, 0, 0, 220);
    viewsGradient.addColorStop(0, 'rgba(59,130,246,0.45)');
    viewsGradient.addColorStop(1, 'rgba(59,130,246,0.03)');

    viewsChart?.destroy();
    viewsChart = new Chart(viewsCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: viewsData,
                borderColor: '#60a5fa',
                backgroundColor: viewsGradient,
                borderWidth: 2.5,
                pointRadius: 0,
                pointHoverRadius: 4,
                pointBackgroundColor: '#93c5fd',
                pointHoverBorderWidth: 2,
                pointHoverBorderColor: p.surface,
                fill: true,
                tension: 0.38,
            }]
        },
        options: defaults,
    });

    const clicksCtx = clicksCanvas.getContext('2d');
    const barGradient = clicksCtx.createLinearGradient(0, 0, 0, 220);
    barGradient.addColorStop(0, 'rgba(250, 154, 60,0.95)');
    barGradient.addColorStop(1, 'rgba(242, 121, 15,0.55)');

    clicksChart?.destroy();
    clicksChart = new Chart(clicksCanvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: clicksData,
                backgroundColor: barGradient,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 28,
                hoverBackgroundColor: 'rgba(250, 154, 60,0.9)',
            }]
        },
        options: {
            ...defaults,
            scales: {
                ...defaults.scales,
                x: { ...defaults.scales.x, grid: { display: false } },
            },
        },
    });

    if (clicksDoughnutCanvas) {
        doughnutChart?.destroy();
        doughnutChart = new Chart(clicksDoughnutCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Téléphone', 'Site web', 'Itinéraire'],
                datasets: [{
                    data: doughnutData,
                    backgroundColor: [
                        'rgba(16,185,129,0.92)',
                        'rgba(168,85,247,0.92)',
                        'rgba(242, 121, 15,0.92)',
                    ],
                    borderColor: p.ring,
                    borderWidth: 3,
                    hoverOffset: 8,
                    cutout: '68%',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: p.surface,
                        titleColor: p.tooltipTitle,
                        bodyColor: p.tooltipBody,
                        borderColor: p.tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                    },
                },
            },
        });
    }
}

buildAnalyticsCharts();
const htmlRootEl = document.getElementById('html-root');
if (htmlRootEl) {
    new MutationObserver(buildAnalyticsCharts).observe(htmlRootEl, { attributes: true, attributeFilter: ['class'] });
}
</script>
@endif
@endpush
