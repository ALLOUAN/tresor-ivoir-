@extends('layouts.app')

@section('title', 'Intelligence artificielle')
@section('page-title', 'Intelligence artificielle — évaluation')

@php
    $labels = ['popularite' => 'Tri par popularité (référence)', 'contenu_regles' => 'Contenu + règles métier', 'hybride' => 'Hybride (avec collaboratif)'];
    $pct = fn ($v) => number_format($v * 100, 1, ',', ' ').' %';
    $best = fn ($metric) => collect($reco['methods'])->sortByDesc($metric)->keys()->first();
@endphp

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <p class="text-slate-500 text-sm max-w-3xl">
        Suivi des modules IA (recommandation, planificateur, recherche, assistant, modération, rédaction). Les mesures ci-dessous sont recalculées à chaque affichage.
    </p>
    <form method="get" class="flex items-end gap-2">
        <label class="text-xs text-slate-400">k
            <select name="k" onchange="this.form.submit()" class="ml-1 bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-sm text-slate-100">
                @foreach([3,5,8,10] as $o)<option value="{{ $o }}" {{ $k === $o ? 'selected' : '' }}>{{ $o }}</option>@endforeach
            </select>
        </label>
    </form>
</div>

{{-- Données --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Fiches du catalogue', $stats['catalog'], 'fa-database'],
        ['Consultations réelles', $stats['interactions'], 'fa-eye'],
        ['Consultations simulées', $stats['simulated'], 'fa-flask'],
        ['Acteurs actifs (≥ 2 fiches)', $stats['active_actors'].' / '.$stats['actors'], 'fa-users'],
        ['Profils déclarés', $stats['profiles'], 'fa-clipboard-question'],
        ['Séjours générés', $stats['trips'].' ('.$stats['trips_7d'].' sur 7 j)', 'fa-route'],
        ['Rédaction du séjour', $stats['llm'], 'fa-pen-nib'],
    ] as [$label, $val, $icon])
        <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
            <p class="text-slate-500 text-xs flex items-center gap-2"><i class="fas {{ $icon }} text-orange-400"></i>{{ $label }}</p>
            <p class="text-white text-lg font-bold mt-1">{{ $val }}</p>
        </div>
    @endforeach
</div>

@if($stats['simulated'] > 0)
    <p class="mb-6 text-sm rounded-lg border px-4 py-3" style="background:#F3F8F5;border-color:#F7C396;color:#B85600">
        <i class="fas fa-flask mr-2"></i>{{ $stats['simulated'] }} consultations sont <strong>simulées</strong> (commande <code>ai:seed-simulated</code>) pour démontrer le filtrage collaboratif.
        Elles faussent les résultats ci-dessous : à retirer avec <code>php artisan ai:seed-simulated --fresh --actors=0</code> une fois l'usage réel suffisant.
    </p>
@endif

{{-- Recommandation --}}
<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold text-sm mb-1 flex items-center gap-2"><i class="fas fa-wand-magic-sparkles text-orange-400"></i> Recommandation hybride — évaluation hors ligne</h2>
    <p class="text-slate-500 text-xs mb-4">
        Méthode « leave-one-out » : pour {{ $reco['actors'] }} visiteurs ({{ round($reco['simulated_share'] * 100) }} % simulés), on retire la dernière fiche
        de leur historique puis on regarde si elle remonte dans les {{ $reco['k'] }} premières. Catalogue : {{ $reco['catalog'] }} fiches.
    </p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 text-xs uppercase tracking-wide border-b border-slate-800">
                    <th class="py-2 pr-4">Méthode</th><th class="py-2 pr-4">Précision&#64;{{ $reco['k'] }}</th><th class="py-2 pr-4">Rappel&#64;{{ $reco['k'] }}</th>
                    <th class="py-2 pr-4">NDCG&#64;{{ $reco['k'] }}</th><th class="py-2 pr-4">Diversité</th><th class="py-2 pr-4">Couverture du catalogue</th><th class="py-2">Adresses peu connues</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reco['methods'] as $name => $m)
                    <tr class="border-b border-slate-800/60 text-slate-300">
                        <td class="py-2.5 pr-4 font-medium text-white">{{ $labels[$name] }}</td>
                        <td class="py-2.5 pr-4 {{ $best('precision') === $name ? 'text-emerald-400 font-bold' : '' }}">{{ $pct($m['precision']) }}</td>
                        <td class="py-2.5 pr-4 {{ $best('rappel') === $name ? 'text-emerald-400 font-bold' : '' }}">{{ $pct($m['rappel']) }}</td>
                        <td class="py-2.5 pr-4 {{ $best('ndcg') === $name ? 'text-emerald-400 font-bold' : '' }}">{{ number_format($m['ndcg'], 3, ',', '') }}</td>
                        <td class="py-2.5 pr-4 {{ $best('diversite') === $name ? 'text-emerald-400 font-bold' : '' }}">{{ number_format($m['diversite'], 3, ',', '') }}</td>
                        <td class="py-2.5 pr-4 {{ $best('couverture') === $name ? 'text-emerald-400 font-bold' : '' }}">{{ $pct($m['couverture']) }}</td>
                        <td class="py-2.5 {{ $best('longue_traine') === $name ? 'text-emerald-400 font-bold' : '' }}">{{ $pct($m['longue_traine']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="text-slate-500 text-xs mt-3">En vert : meilleure valeur de la colonne. Les seuils à atteindre sont à fixer avec l'encadrant.</p>
</div>

{{-- Planificateur --}}
<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold text-sm mb-1 flex items-center gap-2"><i class="fas fa-route text-orange-400"></i> Planificateur d'itinéraires — cas de test scénarisés</h2>
    <p class="text-slate-500 text-xs mb-4">Chaque scénario est rejoué avec les données réelles du catalogue.</p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 text-xs uppercase tracking-wide border-b border-slate-800">
                    <th class="py-2 pr-4">Scénario</th><th class="py-2 pr-4">Durée</th><th class="py-2 pr-4">Budget</th><th class="py-2 pr-4">Km / jour</th>
                    <th class="py-2 pr-4">Étapes / jour</th><th class="py-2 pr-4">Intérêts couverts</th><th class="py-2 pr-4">Adresses peu connues</th><th class="py-2">Coût</th>
                </tr>
            </thead>
            <tbody>
                @foreach($planner['scenarios'] as $sc)
                    <tr class="border-b border-slate-800/60 text-slate-300 align-top">
                        <td class="py-2.5 pr-4 font-medium text-white">{{ $sc['name'] }}
                            @foreach($sc['warnings'] as $w)<p class="text-xs font-normal mt-0.5" style="color:#B85600">{{ \Illuminate\Support\Str::limit($w, 90) }}</p>@endforeach
                        </td>
                        <td class="py-2.5 pr-4">{!! $sc['days_ok'] ? '<span class="text-emerald-400">respectée</span>' : '<span class="text-red-400">écart</span>' !!}</td>
                        <td class="py-2.5 pr-4">{!! $sc['budget_ok'] ? '<span class="text-emerald-400">respecté</span>' : '<span class="text-red-400">dépassé</span>' !!}</td>
                        <td class="py-2.5 pr-4">{{ $sc['km_per_day'] }}</td>
                        <td class="py-2.5 pr-4">{{ $sc['stops_per_day'] }}</td>
                        <td class="py-2.5 pr-4">{{ round($sc['interest_coverage'] * 100) }} %</td>
                        <td class="py-2.5 pr-4">{{ round($sc['long_tail'] * 100) }} %</td>
                        <td class="py-2.5">{{ number_format($sc['total_cost'], 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Recherche intelligente --}}
<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold text-sm mb-1 flex items-center gap-2"><i class="fas fa-magnifying-glass text-orange-400"></i> Recherche intelligente — requêtes annotées</h2>
    <p class="text-slate-500 text-xs mb-4">
        Comparaison avec la recherche par mots-clés actuelle (la requête entière doit figurer dans le texte de la fiche). Le « jeu principal » a servi à régler le moteur ;
        le « jeu de validation » regroupe des requêtes écrites après coup, sans réglage : c'est la mesure à retenir.
    </p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 text-xs uppercase tracking-wide border-b border-slate-800">
                <th class="py-2 pr-4">Jeu</th><th class="py-2 pr-4">Système</th><th class="py-2 pr-4">Bonne fiche en 1er</th><th class="py-2 pr-4">Dans les 3 premières</th><th class="py-2 pr-4">MRR</th><th class="py-2 pr-4">Précision@3</th><th class="py-2">Sans résultat</th>
            </tr></thead>
            <tbody>
            @foreach([['Principal ('.$search['queries'].' requêtes)', $search], ['Validation ('.$searchHeldOut['queries'].' requêtes)', $searchHeldOut]] as [$setLabel, $set])
                @foreach(['mots_cles' => 'Mots-clés (actuel)', 'intelligente' => 'Intelligente'] as $sys => $sysLabel)
                    @php $m = $set['systems'][$sys]; @endphp
                    <tr class="border-b border-slate-800/60 text-slate-300">
                        <td class="py-2.5 pr-4 text-white">{{ $loop->first ? $setLabel : '' }}</td>
                        <td class="py-2.5 pr-4 {{ $sys === 'intelligente' ? 'text-white font-medium' : '' }}">{{ $sysLabel }}</td>
                        <td class="py-2.5 pr-4">{{ $pct($m['hit1']) }}</td><td class="py-2.5 pr-4">{{ $pct($m['hit3']) }}</td>
                        <td class="py-2.5 pr-4">{{ number_format($m['mrr'], 2, ',', '') }}</td><td class="py-2.5 pr-4">{{ $pct($m['precision3']) }}</td><td class="py-2.5">{{ $pct($m['sans_resultat']) }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
    @php $missed = collect($searchHeldOut['details'])->filter(fn ($d) => empty($d['intelligente'])); @endphp
    @if($missed->isNotEmpty())
        <p class="text-slate-500 text-xs mt-3">Requêtes de validation manquées : {{ $missed->pluck('q')->map(fn ($q) => '« '.$q.' »')->implode(', ') }}.</p>
    @endif
    <p class="text-slate-500 text-xs mt-2">Requêtes enregistrées sur le site : {{ $search['logged'] }} dont {{ $search['no_result_logged'] }} sans aucun résultat (classique et intelligent).
        @if($noResult->isNotEmpty()) Les plus fréquentes : {{ $noResult->map(fn ($r) => '« '.$r->query.' » ('.$r->n.')')->implode(', ') }}. @endif</p>
</div>

{{-- Assistant conversationnel --}}
<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold text-sm mb-1 flex items-center gap-2"><i class="fas fa-comments text-orange-400"></i> Assistant conversationnel — questions annotées</h2>
    <p class="text-slate-500 text-xs mb-4">
        Questions dont la réponse figure (ou non) dans le site. « Source citée » : la bonne fiche, page ou FAQ apparaît parmi les sources de la réponse ;
        « refus » : sur une question hors sujet, l'assistant dit qu'il n'a pas trouvé au lieu d'inventer. Le jeu de validation a été écrit après le réglage.
    </p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 text-xs uppercase tracking-wide border-b border-slate-800">
                <th class="py-2 pr-4">Jeu</th><th class="py-2 pr-4">Questions</th><th class="py-2 pr-4">Répondues</th><th class="py-2 pr-4">Bonne source citée</th><th class="py-2 pr-4">Bonne source en 1er</th><th class="py-2">Refus hors sujet</th>
            </tr></thead>
            <tbody>
            @foreach([['Principal', $chat['main']], ['Validation', $chat['validation']]] as [$cl, $c])
                <tr class="border-b border-slate-800/60 text-slate-300">
                    <td class="py-2.5 pr-4 text-white">{{ $cl }}</td>
                    <td class="py-2.5 pr-4">{{ $c['answerable'] }} + {{ $c['unanswerable'] }} hors sujet</td>
                    <td class="py-2.5 pr-4">{{ $pct($c['answered_rate']) }}</td><td class="py-2.5 pr-4">{{ $pct($c['source_cited_rate']) }}</td>
                    <td class="py-2.5 pr-4">{{ $pct($c['first_source_rate']) }}</td><td class="py-2.5">{{ $pct($c['refusal_rate']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @php $miss = collect($chat['validation']['rows'])->concat($chat['main']['rows'])->where('ok', false); @endphp
    @if($miss->isNotEmpty())<p class="text-slate-500 text-xs mt-3">Ratées : {{ $miss->map(fn ($r) => '« '.$r['q'].' »')->implode(', ') }}.</p>@endif
    <p class="text-slate-500 text-xs mt-2">Garde-fous contre un modèle de langage qui inventerait (réponses simulées) : <strong class="text-slate-300">{{ $chat['guards']['correct'] }} / {{ $chat['guards']['cases'] }}</strong> cas corrects (source citée obligatoire, chiffres vérifiés, HTML retiré).</p>
    @php $cl = $chat['logs']; @endphp
    <p class="text-slate-500 text-xs mt-2">
        Usage réel : {{ $cl['total'] }} question{{ $cl['total'] > 1 ? 's' : '' }}
        @if(($cl['total'] ?? 0) > 0) dont {{ $cl['answered'] }} répondue{{ $cl['answered'] > 1 ? 's' : '' }} et {{ $cl['no_source'] }} sans source ; avis : {{ $cl['useful'] }} utile{{ $cl['useful'] > 1 ? 's' : '' }} sur {{ $cl['rated'] }}.
            @if($cl['top_unanswered']->isNotEmpty()) Questions sans réponse les plus fréquentes — à documenter dans le site : {{ $cl['top_unanswered']->map(fn ($r) => '« '.$r->question.' » ('.$r->n.')')->implode(', ') }}. @endif
        @endif
    </p>
</div>

{{-- Aide à la rédaction --}}
<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold text-sm mb-1 flex items-center gap-2"><i class="fas fa-pen-nib text-orange-400"></i> Aide à la rédaction et à la traduction</h2>
    <p class="text-slate-500 text-xs mb-4">Résumé et SEO fonctionnent sans service externe ; titres alternatifs et traduction exigent un modèle de langage ({{ $stats['llm'] }}). Rien n'est appliqué sans clic de l'utilisateur.</p>
    @php $w = $writing; @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 text-sm">
        <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-4">
            <p class="text-white font-semibold mb-2">Résumé par extraction ({{ $w['summary']['articles'] }} articles)</p>
            <p class="text-slate-300">Dans la limite de 300 caractères : <strong class="text-white">{{ $pct($w['summary']['within_limit']) }}</strong></p>
            <p class="text-slate-300">Mots clés repris : <strong class="text-white">{{ $pct($w['summary']['coverage']) }}</strong> <span class="text-slate-500">(simple coupe des 300 premiers caractères : {{ $pct($w['summary']['baseline_coverage']) }})</span></p>
            <p class="text-slate-500 text-xs mt-2">L'extraction ne fait pas mieux que la coupe du début sur ce critère ; son intérêt est de s'arrêter en fin de phrase. La qualité attendue vient du mode modèle de langage.</p>
        </div>
        <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-4">
            <p class="text-white font-semibold mb-2">Métadonnées SEO</p>
            <p class="text-slate-300">Limites respectées (titre ≤ 70, description ≤ 165) : <strong class="text-white">{{ $pct($w['seo']['within_limits']) }}</strong> ({{ $w['seo']['articles'] }} articles)</p>
            <p class="text-slate-300 mt-2">Contrôles des productions d'un modèle (simulées) : <strong class="text-white">{{ $w['guards']['correct'] }} / {{ $w['guards']['cases'] }}</strong></p>
            <p class="text-slate-500 text-xs mt-2">Refusés : chiffre, date ou lien modifiés, balise perdue, longueur absurde, script ajouté, nombre inventé.</p>
        </div>
        <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-4">
            <p class="text-white font-semibold mb-2">Usage</p>
            @if($w['usage']['total'])
                <p class="text-slate-300">{{ $w['usage']['total'] }} demandes, {{ $w['usage']['ok'] }} abouties, <strong class="text-white">{{ $w['usage']['applied'] }}</strong> appliquées ({{ $pct($w['usage']['applied_rate']) }}).</p>
                <p class="text-slate-500 text-xs mt-2">{{ collect($w['usage']['by_action'])->map(fn ($n, $a) => $a.' : '.$n)->implode(' · ') }}</p>
            @else
                <p class="text-slate-400">Aucune utilisation pour l'instant. Le taux de suggestions appliquées mesurera l'utilité réelle.</p>
            @endif
            <p class="text-slate-500 text-xs mt-2">La qualité d'une traduction réelle ne se mesure qu'avec une clé et un relecteur : elle n'est pas évaluée ici.</p>
        </div>
    </div>
</div>

{{-- Analytique prédictive pour les prestataires --}}
<div class="bg-green-900 border border-slate-800 rounded-xl p-5 mb-6">
    <h2 class="text-white font-semibold text-sm mb-1 flex items-center gap-2"><i class="fas fa-chart-area text-orange-400"></i> Analytique prédictive des prestataires (Silver et Gold)</h2>
    <p class="text-slate-500 text-xs mb-4">
        Prévision des vues de la fiche : on masque les {{ $analytics['holdout'] }} derniers jours de chaque prestataire ayant au moins 56 jours d'historique, on prévoit, puis on compare au réel.
        MAE = erreur moyenne en vues par jour ; MAPE = erreur moyenne en % ; WAPE = erreur totale rapportée au total réel (plus il est bas, mieux c'est).
    </p>
    @if($analytics['providers'] === 0)
        <p class="text-slate-400 text-sm">Aucun prestataire n'a encore 56 jours de mesures : le site compte désormais les vues des fiches chaque jour, l'évaluation apparaîtra avec le temps (ou en démonstration : <code>php artisan ai:seed-analytics</code>).</p>
    @else
        @if($analytics['simulated_share'] > 0)
            <p class="mb-4 text-xs rounded-lg px-3 py-2" style="background:#F3F8F5;border:1px solid #F7C396;color:#B85600"><i class="fas fa-flask mr-1"></i>L'historique est simulé à {{ round($analytics['simulated_share'] * 100) }} % : ce test vérifie le fonctionnement de la chaîne de prévision, il ne mesure pas la précision qu'on obtiendra sur de vrais visiteurs.</p>
        @endif
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead><tr class="text-left text-slate-500 text-xs uppercase tracking-wide border-b border-slate-800"><th class="py-2 pr-4">Modèle</th><th class="py-2 pr-4">MAE</th><th class="py-2 pr-4">MAPE</th><th class="py-2">WAPE</th></tr></thead>
            <tbody>
            @foreach(['auto' => 'Notre prévision (modèle choisi par rétro-test)', 'moyenne' => 'Repère : moyenne de la dernière semaine', 'semaine' => 'Repère : même jour de la semaine précédente'] as $k => $label)
                @php $mm = $analytics['metrics'][$k]; @endphp
                <tr class="border-b border-slate-800/60 text-slate-300"><td class="py-2.5 pr-4 {{ $k === 'auto' ? 'text-white font-medium' : '' }}">{{ $label }}</td><td class="py-2.5 pr-4">{{ str_replace('.', ',', $mm['mae']) }}</td><td class="py-2.5 pr-4">{{ str_replace('.', ',', $mm['mape']) }} %</td><td class="py-2.5">{{ $pct($mm['wape']) }}</td></tr>
            @endforeach
            </tbody>
        </table></div>
        <p class="text-slate-500 text-xs mt-3">{{ $analytics['providers'] }} prestataires évalués · intervalle à 80 % : {{ $pct($analytics['interval_coverage']) }} des jours réels à l'intérieur (au-dessus de 80 % : intervalles prudents) · modèles retenus : {{ collect($analytics['models'])->map(fn ($n, $m) => $m.' ×'.$n)->implode(', ') }}.</p>
    @endif
    <details class="mt-4"><summary class="cursor-pointer text-sm text-orange-400">Voir l'analyse d'un prestataire (vue complète)</summary>
        <div class="flex flex-wrap gap-2 mt-3">
            @foreach($analytics['providers_list'] as $pr)
                <a href="{{ route('admin.ai.provider', $pr->id) }}" class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 hover:text-white text-xs border border-slate-700">{{ $pr->name }}</a>
            @endforeach
        </div>
    </details>
</div>

<p class="mb-6"><a href="{{ route('admin.ai-moderation.index') }}" class="inline-flex items-center gap-2 text-sm text-orange-400 hover:text-orange-300"><i class="fas fa-shield-halved"></i> Ouvrir la file de modération assistée</a></p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold text-sm mb-3"><i class="fas fa-fire text-orange-400 mr-2"></i>Fiches les plus consultées</h2>
        @forelse($top as $t)
            <div class="flex justify-between text-sm py-1.5 border-b border-slate-800/60"><span class="text-slate-300">{{ $t['title'] }}</span><span class="text-slate-500">{{ $t['n'] }}</span></div>
        @empty
            <p class="text-slate-500 text-sm">Aucune consultation enregistrée pour l'instant.</p>
        @endforelse
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold text-sm mb-3"><i class="fas fa-layer-group text-orange-400 mr-2"></i>Composition du catalogue</h2>
        @foreach($stats['by_type'] as $label => $n)
            <div class="flex justify-between text-sm py-1.5 border-b border-slate-800/60"><span class="text-slate-300">{{ $label }}</span><span class="text-slate-500">{{ $n }}</span></div>
        @endforeach
    </div>
</div>
@endsection
