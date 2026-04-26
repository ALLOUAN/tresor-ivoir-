@extends('layouts.app')

@section('title', 'Analytics')
@section('page-title', 'Analytics — Articles & prestataires')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <p class="text-slate-500 text-sm">Données agrégées par jour depuis les tables <code class="text-slate-400">article_analytics</code> et <code class="text-slate-400">provider_analytics</code>.</p>
    </div>
    <form method="get" action="{{ route('admin.analytics.index') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label for="from" class="block text-xs text-slate-400 mb-1">Du</label>
            <input type="date" name="from" id="from" value="{{ $from->toDateString() }}"
                   class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>
        <div>
            <label for="to" class="block text-xs text-slate-400 mb-1">Au</label>
            <input type="date" name="to" id="to" value="{{ $to->toDateString() }}"
                   class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>
        <button type="submit" class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-filter text-xs"></i> Appliquer
        </button>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
            <i class="fas fa-newspaper text-amber-400"></i> Articles (période)
        </h2>
        @if(! $hasArticleAnalytics)
            <p class="text-slate-500 text-sm">Table des métriques articles absente.</p>
        @else
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3 text-center">
                    <p class="text-2xl font-bold text-white">{{ number_format($articleKpis['views']) }}</p>
                    <p class="text-slate-500 text-xs mt-1">Vues</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3 text-center">
                    <p class="text-2xl font-bold text-white">{{ number_format($articleKpis['shares']) }}</p>
                    <p class="text-slate-500 text-xs mt-1">Partages</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3 text-center">
                    <p class="text-2xl font-bold text-white">{{ number_format($articleKpis['visitors']) }}</p>
                    <p class="text-slate-500 text-xs mt-1">Visiteurs uniques</p>
                </div>
            </div>
            @if($articleTotals->isEmpty())
                <p class="text-slate-500 text-sm">Aucune donnée sur cette période. Les métriques sont alimentées lorsque le site enregistre des vues (collecte côté application).</p>
            @else
                <div class="overflow-x-auto rounded-lg border border-slate-800">
                    <table class="w-full text-sm">
                        <thead class="text-slate-500 text-xs uppercase border-b border-slate-800 bg-slate-800/40">
                            <tr>
                                <th class="text-left px-4 py-2">Article</th>
                                <th class="text-right px-4 py-2">Vues</th>
                                <th class="text-right px-4 py-2 hidden sm:table-cell">Partages</th>
                                <th class="text-right px-4 py-2 hidden md:table-cell">Visiteurs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($articleTotals as $row)
                                <tr class="hover:bg-slate-800/40">
                                    <td class="px-4 py-2 text-slate-200">
                                        {{ \Illuminate\Support\Str::limit($articleTitles[$row->article_id] ?? 'Article #'.$row->article_id, 64) }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-white font-medium">{{ number_format((int) $row->total_views) }}</td>
                                    <td class="px-4 py-2 text-right text-slate-400 hidden sm:table-cell">{{ number_format((int) $row->total_shares) }}</td>
                                    <td class="px-4 py-2 text-right text-slate-400 hidden md:table-cell">{{ number_format((int) $row->total_visitors) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
            <i class="fas fa-store text-violet-400"></i> Prestataires (période)
        </h2>
        @if(! $hasProviderAnalytics)
            <p class="text-slate-500 text-sm">Table des métriques prestataires absente.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3 text-center">
                    <p class="text-xl font-bold text-white">{{ number_format($providerKpis['views']) }}</p>
                    <p class="text-slate-500 text-xs mt-1">Vues fiche</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3 text-center">
                    <p class="text-xl font-bold text-white">{{ number_format($providerKpis['clicks_phone']) }}</p>
                    <p class="text-slate-500 text-xs mt-1">Clics téléphone</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3 text-center">
                    <p class="text-xl font-bold text-white">{{ number_format($providerKpis['clicks_website']) }}</p>
                    <p class="text-slate-500 text-xs mt-1">Clics site web</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3 text-center">
                    <p class="text-xl font-bold text-white">{{ number_format($providerKpis['search_appearances']) }}</p>
                    <p class="text-slate-500 text-xs mt-1">Apparitions recherche</p>
                </div>
            </div>
            @if($providerTotals->isEmpty())
                <p class="text-slate-500 text-sm">Aucune donnée sur cette période.</p>
            @else
                <div class="overflow-x-auto rounded-lg border border-slate-800">
                    <table class="w-full text-sm">
                        <thead class="text-slate-500 text-xs uppercase border-b border-slate-800 bg-slate-800/40">
                            <tr>
                                <th class="text-left px-4 py-2">Prestataire</th>
                                <th class="text-right px-4 py-2">Vues</th>
                                <th class="text-right px-4 py-2 hidden sm:table-cell">Tél.</th>
                                <th class="text-right px-4 py-2 hidden sm:table-cell">Site</th>
                                <th class="text-right px-4 py-2 hidden md:table-cell">Recherche</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($providerTotals as $row)
                                <tr class="hover:bg-slate-800/40">
                                    <td class="px-4 py-2 text-slate-200">
                                        {{ \Illuminate\Support\Str::limit($providerNames[$row->provider_id] ?? 'Prestataire #'.$row->provider_id, 48) }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-white font-medium">{{ number_format((int) $row->total_views) }}</td>
                                    <td class="px-4 py-2 text-right text-slate-400 hidden sm:table-cell">{{ number_format((int) $row->total_phone) }}</td>
                                    <td class="px-4 py-2 text-right text-slate-400 hidden sm:table-cell">{{ number_format((int) $row->total_site) }}</td>
                                    <td class="px-4 py-2 text-right text-slate-400 hidden md:table-cell">{{ number_format((int) $row->total_search) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
