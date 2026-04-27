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
                  {{ $period === $val ? 'bg-amber-500 text-white' : 'bg-slate-800 text-slate-400 hover:text-white border border-slate-700' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

@if(! $provider)
<div class="bg-slate-900 border border-slate-800 rounded-xl p-8 text-center">
    <i class="fas fa-store text-slate-600 text-4xl mb-3"></i>
    <p class="text-slate-400">Créez votre fiche prestataire pour accéder aux statistiques.</p>
    <a href="{{ route('provider.profile.edit') }}" class="mt-4 inline-flex bg-amber-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">Créer ma fiche</a>
</div>
@else

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @php
        $kpis = [
            ['icon' => 'fa-eye',        'label' => 'Vues de la fiche',      'value' => $totals['views'],              'growth' => $viewsGrowth,   'color' => 'text-blue-400',    'bg' => 'bg-blue-500/10'],
            ['icon' => 'fa-phone',      'label' => 'Clics téléphone',       'value' => $totals['clicks_phone'],       'growth' => $phoneGrowth,   'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/10'],
            ['icon' => 'fa-globe',      'label' => 'Clics site web',        'value' => $totals['clicks_website'],     'growth' => $websiteGrowth, 'color' => 'text-violet-400',  'bg' => 'bg-violet-500/10'],
            ['icon' => 'fa-location-dot','label'=> 'Clics itinéraire',      'value' => $totals['clicks_direction'],   'growth' => null,           'color' => 'text-amber-400',   'bg' => 'bg-amber-500/10'],
            ['icon' => 'fa-star',       'label' => 'Nouveaux avis',         'value' => $totals['new_reviews'],        'growth' => null,           'color' => 'text-yellow-400',  'bg' => 'bg-yellow-500/10'],
            ['icon' => 'fa-magnifying-glass','label'=>'Apparitions recherche','value'=> $totals['search_appearances'],'growth'=> null,           'color' => 'text-sky-400',     'bg' => 'bg-sky-500/10'],
        ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="premium-shimmer-card bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-700/70 rounded-xl p-4 shadow-lg shadow-black/20">
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
    <div class="bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-blue-500/20 rounded-xl p-5 shadow-lg shadow-blue-900/20">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold text-sm">Vues de la fiche</h3>
            <span class="text-[10px] px-2 py-1 rounded-full bg-blue-500/15 text-blue-300 border border-blue-400/20">Tendance</span>
        </div>
        <canvas id="chartViews" height="180"></canvas>
    </div>
    {{-- Clicks chart --}}
    <div class="bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-amber-500/20 rounded-xl p-5 shadow-lg shadow-amber-900/20">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold text-sm">Clics (téléphone + site + itinéraire)</h3>
            <span class="text-[10px] px-2 py-1 rounded-full bg-amber-500/15 text-amber-300 border border-amber-400/20">Performance</span>
        </div>
        <canvas id="chartClicks" height="180"></canvas>
    </div>
</div>
@else
<div class="bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-700/70 rounded-xl p-10 text-center mb-8 shadow-lg shadow-black/20">
    <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center mb-4">
        <i class="fas fa-chart-line text-slate-500 text-2xl"></i>
    </div>
    <p class="text-slate-200 font-medium">Aucune donnée pour cette période.</p>
    <p class="text-slate-500 text-xs mt-1">Dès que votre fiche reçoit des interactions, les graphiques modernes apparaissent ici.</p>
</div>
@endif

{{-- Clicks breakdown --}}
@if($totals['clicks_phone'] + $totals['clicks_website'] + $totals['clicks_direction'] > 0)
<div class="bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-fuchsia-500/20 rounded-xl p-5 shadow-lg shadow-fuchsia-900/20">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-white font-semibold text-sm">Répartition des clics</h3>
        <span class="text-[10px] px-2 py-1 rounded-full bg-fuchsia-500/15 text-fuchsia-300 border border-fuchsia-400/20">Doughnut</span>
    </div>
    @php
        $totalClicks = $totals['clicks_phone'] + $totals['clicks_website'] + $totals['clicks_direction'];
        $bars = [
            ['label' => 'Téléphone',   'value' => $totals['clicks_phone'],     'color' => 'bg-emerald-500'],
            ['label' => 'Site web',    'value' => $totals['clicks_website'],   'color' => 'bg-violet-500'],
            ['label' => 'Itinéraire',  'value' => $totals['clicks_direction'], 'color' => 'bg-amber-500'],
        ];
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-center">
        <div class="lg:col-span-2">
            <canvas id="chartClicksDoughnut" height="220"></canvas>
        </div>
        <div class="lg:col-span-3 space-y-3">
            @foreach($bars as $bar)
            @php $pct = $totalClicks > 0 ? round($bar['value'] / $totalClicks * 100) : 0; @endphp
            <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-2">
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
<div class="bg-linear-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-700/70 rounded-xl p-5 shadow-lg shadow-black/20">
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
const chartSurface = '#0f172a';
const gridColor = 'rgba(148,163,184,0.14)';
const tickColor = '#94a3b8';

const chartDefaults = {
    responsive: true,
    maintainAspectRatio: true,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: chartSurface,
            titleColor: '#f8fafc',
            bodyColor: '#cbd5e1',
            borderColor: 'rgba(148,163,184,0.25)',
            borderWidth: 1,
            padding: 10,
            displayColors: false,
            titleFont: { weight: '600' },
        },
    },
    scales: {
        x: {
            grid: { color: 'rgba(30,41,59,0.35)', drawBorder: false },
            ticks: { color: tickColor, font: { size: 10 } },
            border: { display: false },
        },
        y: {
            grid: { color: gridColor, drawBorder: false },
            ticks: { color: tickColor, font: { size: 10 } },
            border: { display: false },
            beginAtZero: true,
        },
    },
};

const labels = @json($chartDates);
const viewsCanvas = document.getElementById('chartViews');
const clicksCanvas = document.getElementById('chartClicks');
const clicksDoughnutCanvas = document.getElementById('chartClicksDoughnut');

const viewsCtx = viewsCanvas.getContext('2d');
const viewsGradient = viewsCtx.createLinearGradient(0, 0, 0, 220);
viewsGradient.addColorStop(0, 'rgba(59,130,246,0.45)');
viewsGradient.addColorStop(1, 'rgba(59,130,246,0.03)');

new Chart(viewsCanvas, {
    type: 'line',
    data: {
        labels,
        datasets: [{
            data: @json($chartViews),
            borderColor: '#60a5fa',
            backgroundColor: viewsGradient,
            borderWidth: 2.5,
            pointRadius: 0,
            pointHoverRadius: 4,
            pointBackgroundColor: '#93c5fd',
            pointHoverBorderWidth: 2,
            pointHoverBorderColor: '#0f172a',
            fill: true,
            tension: 0.38,
        }]
    },
    options: chartDefaults,
});

const clicksCtx = clicksCanvas.getContext('2d');
const barGradient = clicksCtx.createLinearGradient(0, 0, 0, 220);
barGradient.addColorStop(0, 'rgba(251,191,36,0.95)');
barGradient.addColorStop(1, 'rgba(245,158,11,0.55)');

new Chart(clicksCanvas, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            data: @json($chartClicks),
            backgroundColor: barGradient,
            borderRadius: 8,
            borderSkipped: false,
            maxBarThickness: 28,
            hoverBackgroundColor: 'rgba(251,191,36,0.9)',
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            ...chartDefaults.scales,
            x: {
                ...chartDefaults.scales.x,
                grid: { display: false },
            },
        },
    },
});

if (clicksDoughnutCanvas) {
    new Chart(clicksDoughnutCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Téléphone', 'Site web', 'Itinéraire'],
            datasets: [{
                data: @json([(int) $totals['clicks_phone'], (int) $totals['clicks_website'], (int) $totals['clicks_direction']]),
                backgroundColor: [
                    'rgba(16,185,129,0.92)',
                    'rgba(168,85,247,0.92)',
                    'rgba(245,158,11,0.92)',
                ],
                borderColor: 'rgba(15,23,42,0.9)',
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
                    backgroundColor: chartSurface,
                    titleColor: '#f8fafc',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(148,163,184,0.25)',
                    borderWidth: 1,
                    padding: 10,
                },
            },
        },
    });
}
</script>
@endif
@endpush
