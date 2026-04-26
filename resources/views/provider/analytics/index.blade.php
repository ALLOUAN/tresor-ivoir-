@extends('layouts.app')

@section('title', 'Statistiques')
@section('page-title', 'Statistiques de votre fiche')

@section('content')
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
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <div class="flex items-start justify-between mb-3">
            <div class="{{ $kpi['bg'] }} w-9 h-9 rounded-lg flex items-center justify-center">
                <i class="fas {{ $kpi['icon'] }} {{ $kpi['color'] }} text-sm"></i>
            </div>
            @if($kpi['growth'] !== null)
            <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full
                {{ $kpi['growth'] >= 0 ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400' }}">
                {{ $kpi['growth'] >= 0 ? '+' : '' }}{{ $kpi['growth'] }}%
            </span>
            @endif
        </div>
        <p class="text-2xl font-bold text-white">{{ number_format($kpi['value'], 0, ',', ' ') }}</p>
        <p class="text-slate-400 text-xs mt-0.5">{{ $kpi['label'] }}</p>
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
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-white font-semibold text-sm mb-4">Vues de la fiche</h3>
        <canvas id="chartViews" height="180"></canvas>
    </div>
    {{-- Clicks chart --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h3 class="text-white font-semibold text-sm mb-4">Clics (téléphone + site + itinéraire)</h3>
        <canvas id="chartClicks" height="180"></canvas>
    </div>
</div>
@else
<div class="bg-slate-900 border border-slate-800 rounded-xl p-10 text-center mb-8">
    <i class="fas fa-chart-line text-slate-600 text-4xl mb-3"></i>
    <p class="text-slate-400">Aucune donnée pour cette période.</p>
    <p class="text-slate-600 text-xs mt-1">Les statistiques sont mises à jour quotidiennement.</p>
</div>
@endif

{{-- Clicks breakdown --}}
@if($totals['clicks_phone'] + $totals['clicks_website'] + $totals['clicks_direction'] > 0)
<div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
    <h3 class="text-white font-semibold text-sm mb-4">Répartition des clics</h3>
    @php
        $totalClicks = $totals['clicks_phone'] + $totals['clicks_website'] + $totals['clicks_direction'];
        $bars = [
            ['label' => 'Téléphone',   'value' => $totals['clicks_phone'],     'color' => 'bg-emerald-500'],
            ['label' => 'Site web',    'value' => $totals['clicks_website'],   'color' => 'bg-violet-500'],
            ['label' => 'Itinéraire',  'value' => $totals['clicks_direction'], 'color' => 'bg-amber-500'],
        ];
    @endphp
    <div class="space-y-3">
        @foreach($bars as $bar)
        @php $pct = $totalClicks > 0 ? round($bar['value'] / $totalClicks * 100) : 0; @endphp
        <div>
            <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-slate-300">{{ $bar['label'] }}</span>
                <span class="text-slate-400">{{ number_format($bar['value'], 0, ',', ' ') }} ({{ $pct }}%)</span>
            </div>
            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                <div class="{{ $bar['color'] }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endif {{-- end $provider check --}}

@endsection

@endsection

@push('scripts')
@if(count($chartDates) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#94a3b8', borderColor: '#334155', borderWidth: 1 } },
    scales: {
        x: { grid: { color: 'rgba(51,65,85,0.5)' }, ticks: { color: '#64748b', font: { size: 10 } } },
        y: { grid: { color: 'rgba(51,65,85,0.5)' }, ticks: { color: '#64748b', font: { size: 10 } }, beginAtZero: true }
    }
};

const labels = @json($chartDates);

new Chart(document.getElementById('chartViews'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            data: @json($chartViews),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#3b82f6',
            fill: true,
            tension: 0.4,
        }]
    },
    options: chartDefaults,
});

new Chart(document.getElementById('chartClicks'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            data: @json($chartClicks),
            backgroundColor: 'rgba(245,158,11,0.7)',
            borderRadius: 4,
        }]
    },
    options: chartDefaults,
});
</script>
@endif
@endpush
