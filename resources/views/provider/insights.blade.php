@extends('layouts.app')

@section('title', 'Analyse avancée')
@section('page-title', 'Analyse avancée de votre fiche')

@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $impactStyle = ['high' => ['Priorité haute', '#fdeceb', '#c0362c'], 'medium' => ['Priorité moyenne', '#F3F8F5', '#B85600'], 'low' => ['Plus tard', '#F3F8F5', '#00874F']];
@endphp

@section('content')
@if(! $report)
    <div class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center">
        <i class="fas fa-store text-slate-600 text-4xl mb-3"></i>
        <p class="text-slate-400">Créez votre fiche prestataire pour accéder à l'analyse avancée.</p>
        <a href="{{ route('provider.profile.edit') }}" class="mt-4 inline-flex bg-orange-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">Créer ma fiche</a>
    </div>
@else
@php
    $p = $report['provider']; $rank = $report['rank']; $m = $report['metrics']; $f = $report['forecast']; $b = $report['benchmark']; $plan = $report['plan'];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <p class="text-slate-400 text-sm">
        <strong class="text-white">{{ $p->name }}</strong> ·
        forfait <span class="text-orange-400 font-semibold">{{ $adminPreview ? 'vue administrateur (toutes les analyses)' : ($plan['plan'] ?? 'sans forfait actif') }}</span>
    </p>
    <div class="flex gap-2">
        <a href="{{ route('provider.analytics') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white border border-slate-700">Statistiques de base</a>
        @if($rank >= 3 && ! $adminPreview)
            <a href="{{ route('provider.insights.export') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-500 text-white"><i class="fas fa-download mr-1"></i> Exporter (CSV)</a>
        @endif
    </div>
</div>

{{-- Complétude (tous forfaits) --}}
<section class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <h2 class="text-white font-semibold text-sm"><i class="fas fa-clipboard-check text-orange-400 mr-2"></i>Complétude de votre fiche</h2>
        <span class="text-2xl font-bold {{ $m['completeness'] >= 80 ? 'text-emerald-400' : ($m['completeness'] >= 55 ? 'text-amber-400' : 'text-red-400') }}">{{ $m['completeness'] }} %</span>
    </div>
    <div class="h-2 rounded-full bg-slate-800 overflow-hidden mb-4"><div class="h-full bg-orange-500" style="width: {{ $m['completeness'] }}%"></div></div>
    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm">
        @foreach(\App\Services\Ai\Analytics\ProviderProfileMetrics::CHECKS as [$key, $label])
            <li class="flex items-start gap-2 {{ $m['has'][$key] ? 'text-slate-300' : 'text-slate-500' }}">
                <i class="fas {{ $m['has'][$key] ? 'fa-circle-check text-emerald-400' : 'fa-circle-xmark text-red-400' }} mt-1 text-xs"></i> {{ $label }}
            </li>
        @endforeach
    </ul>
</section>

@if($rank < 2)
    {{-- Bronze : contenu avancé verrouillé --}}
    <section class="bg-green-900 border border-slate-800 rounded-xl p-6 text-center">
        <i class="fas fa-lock text-orange-400 text-2xl mb-3"></i>
        <h2 class="text-white font-semibold">Analyses avancées : forfaits Silver et Gold</h2>
        <p class="text-slate-400 text-sm max-w-xl mx-auto mt-2">
            Prévision de la fréquentation de votre fiche sur 30 jours, comparaison avec les prestataires de votre catégorie et conseils chiffrés pour améliorer votre fiche.
            Le forfait Gold ajoute l'horizon à 90 jours, la prévision de la demande de réservation, le plan d'amélioration complet et l'export.
        </p>
        <a href="{{ route('provider.billing.plans') }}" class="mt-4 inline-flex bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">Découvrir les forfaits</a>
    </section>
@else
    {{-- Prévision --}}
    <section class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
        <h2 class="text-white font-semibold text-sm mb-1"><i class="fas fa-chart-line text-orange-400 mr-2"></i>Prévision de fréquentation ({{ $rank >= 3 ? '90' : '30' }} jours)</h2>
        @if($f['status'] !== 'ok')
            <p class="text-slate-400 text-sm mt-3"><i class="fas fa-hourglass-half mr-2 text-orange-400"></i>{{ $f['message'] }}</p>
        @else
            @if($f['simulated_share'] > 0)
                <p class="text-xs mt-2 rounded-lg px-3 py-2" style="background:#F3F8F5;border:1px solid #F7C396;color:#B85600"><i class="fas fa-flask mr-1"></i>{{ round($f['simulated_share'] * 100) }} % de l'historique est simulé (démonstration) : la prévision illustre le fonctionnement, pas votre fréquentation réelle.</p>
            @endif
            @php
                $hist = array_values($report['recent'] ?? []);
                $fc = $f['forecast'];
                $show = array_slice($fc, 0, $rank >= 3 ? 90 : 30);
                $W = 760; $H = 230; $padL = 36; $padB = 22; $padT = 10;
                $nAll = count($hist) + count($show);
                $max = max(1, max(array_merge($hist ?: [0], array_column($show, 'hi'))));
                $x = fn ($i) => $padL + ($W - $padL - 6) * $i / max(1, $nAll - 1);
                $y = fn ($v) => $padT + ($H - $padT - $padB) * (1 - $v / $max);
                $histPts = collect($hist)->map(fn ($v, $i) => round($x($i), 1).','.round($y($v), 1))->implode(' ');
                $off = count($hist);
                $fcPts = collect($show)->map(fn ($r, $i) => round($x($off + $i), 1).','.round($y($r['yhat']), 1))->implode(' ');
                $bandTop = collect($show)->map(fn ($r, $i) => round($x($off + $i), 1).','.round($y($r['hi']), 1))->implode(' ');
                $bandBot = collect($show)->map(fn ($r, $i) => [$i, $r])->reverse()->map(fn ($t) => round($x($off + $t[0]), 1).','.round($y($t[1]['lo']), 1))->implode(' ');
            @endphp
            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full mt-3" role="img" aria-label="Vues quotidiennes : historique et prévision">
                <line x1="{{ $padL }}" y1="{{ $y(0) }}" x2="{{ $W - 6 }}" y2="{{ $y(0) }}" stroke="#F7C396" stroke-width="1"/>
                <line x1="{{ $padL }}" y1="{{ $y($max / 2) }}" x2="{{ $W - 6 }}" y2="{{ $y($max / 2) }}" stroke="#D8E9E0" stroke-width="1" stroke-dasharray="3 4"/>
                <text x="4" y="{{ $y($max) + 10 }}" font-size="11" fill="#557466">{{ round($max) }}</text>
                <text x="4" y="{{ $y($max / 2) + 4 }}" font-size="11" fill="#557466">{{ round($max / 2) }}</text>
                <line x1="{{ $x($off - 0.5) }}" y1="{{ $padT }}" x2="{{ $x($off - 0.5) }}" y2="{{ $H - $padB }}" stroke="#EB6E00" stroke-width="1" stroke-dasharray="4 4"/>
                <text x="{{ $x($off - 0.5) + 6 }}" y="{{ $padT + 10 }}" font-size="11" fill="#EB6E00">aujourd'hui</text>
                <polygon points="{{ $bandTop }} {{ $bandBot }}" fill="#EB6E00" opacity=".16"/>
                <polyline points="{{ $histPts }}" fill="none" stroke="#00874F" stroke-width="2"/>
                <polyline points="{{ $fcPts }}" fill="none" stroke="#EB6E00" stroke-width="2" stroke-dasharray="6 4"/>
            </svg>
            <p class="text-xs text-slate-500 mt-1"><span style="color:#00874F">▬</span> vues réelles · <span style="color:#EB6E00">- - -</span> prévision, avec sa fourchette à 80 % (zone orange)</p>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3">
                    <p class="text-slate-500 text-xs">{{ $f['totals']['span'] }} prochains jours</p>
                    <p class="text-white text-xl font-bold">{{ $fmt($f['totals']['next']) }} vues</p>
                    <p class="text-slate-500 text-xs">entre {{ $fmt($f['totals']['lo']) }} et {{ $fmt($f['totals']['hi']) }}</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3">
                    <p class="text-slate-500 text-xs">Par rapport aux {{ $f['totals']['span'] }} jours écoulés</p>
                    <p class="text-xl font-bold {{ ($f['totals']['change'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ $f['totals']['change'] === null ? '—' : (($f['totals']['change'] >= 0 ? '+' : '').str_replace('.', ',', $f['totals']['change']).' %') }}</p>
                    <p class="text-slate-500 text-xs">{{ $fmt($f['totals']['previous']) }} vues</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3">
                    <p class="text-slate-500 text-xs">Confiance</p>
                    <p class="text-white text-xl font-bold capitalize">{{ $f['confidence'] }}</p>
                    <p class="text-slate-500 text-xs">{{ $f['history_days'] }} jours d'historique</p>
                </div>
                <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3">
                    <p class="text-slate-500 text-xs">Jours les plus visités</p>
                    @php $topDays = collect($f['weekday'])->sortByDesc('share')->take(2); @endphp
                    <p class="text-white text-xl font-bold">{{ $topDays->pluck('day')->implode(' · ') }}</p>
                    <p class="text-slate-500 text-xs">{{ $topDays->map(fn ($d) => $d['share'].' %')->implode(' · ') }} des vues</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3">
                Méthode : {{ ['moyenne' => 'moyenne des 7 derniers jours', 'semaine' => 'même jour de la semaine précédente', 'tendance' => 'niveau récent × profil des jours de la semaine, avec tendance amortie'][$f['model']] ?? $f['model'] }}, choisie parce qu'elle a été la plus précise sur votre propre historique
                @if($f['backtest']) (erreur moyenne de {{ str_replace('.', ',', $f['backtest']['models'][$f['model']]['mae']) }} vues par jour lors des essais passés) @endif.
                @if($f['seasonal_source'] === 'hypothèse') L'effet de saison (hausse en saison sèche et en fin d'année, baisse en saison des pluies) repose sur une hypothèse tant que vous n'avez pas 12 mois de données. @endif
            </p>
            @if($rank >= 3)
                <div class="mt-4 overflow-x-auto"><table class="w-full text-sm">
                    <thead><tr class="text-left text-slate-500 text-xs uppercase border-b border-slate-800"><th class="py-2 pr-4">Semaine</th><th class="py-2 pr-4">Vues prévues</th><th class="py-2">Fourchette (80 %)</th></tr></thead>
                    <tbody>
                    @foreach($f['weeks'] as $w)
                        <tr class="border-b border-slate-800/60 text-slate-300"><td class="py-2 pr-4">{{ \Illuminate\Support\Carbon::parse($w['from'])->locale('fr')->translatedFormat('j M') }} → {{ \Illuminate\Support\Carbon::parse($w['to'])->locale('fr')->translatedFormat('j M') }}</td><td class="py-2 pr-4 text-white">{{ $fmt($w['yhat']) }}</td><td class="py-2">{{ $fmt($w['lo']) }} – {{ $fmt($w['hi']) }}</td></tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        @endif
    </section>

    {{-- Demande de réservation (Gold) --}}
    @if($report['bookings'])
        @php $bk = $report['bookings']; @endphp
        <section class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
            <h2 class="text-white font-semibold text-sm mb-2"><i class="fas fa-calendar-check text-orange-400 mr-2"></i>Demande de réservation (4 prochaines semaines)</h2>
            @if($bk['status'] === 'ok')
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($bk['forecast'] as $w)
                        <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-3">
                            <p class="text-slate-500 text-xs">Semaine du {{ \Illuminate\Support\Carbon::parse($w['from'])->locale('fr')->translatedFormat('j M') }}</p>
                            <p class="text-white text-xl font-bold">{{ str_replace('.', ',', $w['yhat']) }} <span class="text-sm font-normal text-slate-400">demandes</span></p>
                            <p class="text-slate-500 text-xs">de {{ str_replace('.', ',', $w['lo']) }} à {{ str_replace('.', ',', $w['hi']) }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 mt-2">Estimation indicative : moyenne des 4 dernières semaines ajustée de la saison (hypothèse). Les petits volumes rendent la prévision peu précise.</p>
            @else
                <p class="text-slate-400 text-sm">{{ $bk['message'] }}</p>
            @endif
        </section>
    @endif

    {{-- Comparaison --}}
    <section class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
        <h2 class="text-white font-semibold text-sm mb-1"><i class="fas fa-scale-balanced text-orange-400 mr-2"></i>Votre fiche comparée à votre catégorie</h2>
        @if($b['scope'] === 'none')
            <p class="text-slate-400 text-sm mt-2">Pas assez de prestataires actifs pour une comparaison fiable pour le moment.</p>
        @else
            <p class="text-xs text-slate-500 mb-3">Comparaison avec {{ $b['scope_label'] }} — {{ $b['peers'] }} fiches. « Rang » : part des fiches comparées que la vôtre dépasse.</p>
            <div class="overflow-x-auto"><table class="w-full text-sm">
                <thead><tr class="text-left text-slate-500 text-xs uppercase border-b border-slate-800"><th class="py-2 pr-4">Indicateur</th><th class="py-2 pr-4">Votre fiche</th><th class="py-2 pr-4">Médiane</th>@if($rank >= 3)<th class="py-2 pr-4">Quart supérieur</th>@endif<th class="py-2">Rang</th></tr></thead>
                <tbody>
                @foreach($b['rows'] as $row)
                    <tr class="border-b border-slate-800/60 text-slate-300">
                        <td class="py-2.5 pr-4">{{ $row['label'] }}</td>
                        <td class="py-2.5 pr-4 text-white font-medium">{{ $row['key'] === 'rating' && $m['rating_count'] === 0 ? '—' : str_replace('.', ',', $row['mine']).$row['unit'] }}</td>
                        <td class="py-2.5 pr-4">{{ str_replace('.', ',', $row['median']) }}{{ $row['unit'] }}</td>
                        @if($rank >= 3)<td class="py-2.5 pr-4">{{ str_replace('.', ',', $row['top_quartile']) }}{{ $row['unit'] }}</td>@endif
                        <td class="py-2.5"><span class="{{ $row['status'] === 'top' ? 'text-emerald-400' : ($row['status'] === 'low' ? 'text-red-400' : 'text-slate-300') }}">{{ $row['percentile'] }} %</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </section>

    {{-- Suggestions --}}
    <section class="bg-green-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold text-sm mb-3"><i class="fas fa-lightbulb text-orange-400 mr-2"></i>Comment améliorer votre fiche</h2>
        @forelse($report['advice'] as $a)
            @php [$il, $ib, $ic] = $impactStyle[$a['impact']]; @endphp
            <div class="py-3 border-t border-slate-800/70 first:border-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded" style="background:{{ $ib }};color:{{ $ic }}">{{ $il }}</span>
                    <p class="text-white text-sm font-semibold">{{ $a['title'] }}</p>
                </div>
                <p class="text-slate-400 text-sm">{{ $a['evidence'] }}</p>
                <p class="text-slate-300 text-sm mt-0.5">→ {{ $a['action'] }} @if($a['link'])<a href="{{ $a['link'] }}" class="text-orange-400 hover:underline ml-1">Ouvrir</a>@endif</p>
            </div>
        @empty
            <p class="text-slate-400 text-sm"><i class="fas fa-circle-check text-emerald-400 mr-2"></i>Votre fiche est bien renseignée : aucune action prioritaire pour le moment.</p>
        @endforelse
        @if($rank < 3 && $report['advice_total'] > count($report['advice']))
            <p class="text-sm text-slate-400 mt-3 pt-3 border-t border-slate-800/70"><i class="fas fa-lock text-orange-400 mr-1"></i>{{ $report['advice_total'] - count($report['advice']) }} autre(s) suggestion(s) dans le plan d'amélioration complet du forfait Gold. <a href="{{ route('provider.billing.plans') }}" class="text-orange-400 hover:underline">Voir les forfaits</a></p>
        @endif
        <p class="text-xs text-slate-500 mt-3">Ces conseils reposent sur ce que mesure la plateforme ; ils n'annoncent pas de gain chiffré.</p>
    </section>
@endif
@endif
@endsection
