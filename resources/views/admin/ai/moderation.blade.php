@extends('layouts.app')

@section('title', 'Modération assistée')
@section('page-title', 'Modération assistée par IA')

@php
    $tab = fn ($label, $key, $val, $current) => '<a href="'.e(request()->fullUrlWithQuery([$key => $val, 'page' => null])).'" class="px-3 py-1.5 rounded-lg text-xs font-medium '.($current === $val ? 'bg-orange-500 text-white' : 'bg-slate-800 text-slate-400 hover:text-white').'">'.e($label).'</a>';
    $badge = fn ($v) => match ($v) {'likely' => ['Problème probable', '#c0362c', '#fdeceb'], 'review' => ['À vérifier', '#9a6a00', '#fdf3d6'], default => ['Sain', '#1b6b3a', '#eef6f0']};
    $t = $eval['text']; $im = $eval['images']; $dec = $eval['decisions']; $real = $eval['real'];
    $pct = fn ($v) => $v === null ? '—' : number_format($v * 100, 0, ',', ' ').' %';
@endphp

@section('content')
@if(session('status'))
    <p class="mb-4 text-sm rounded-lg px-4 py-3" style="background:#eef6f0;border:1px solid #d9ecdf;color:#14532d"><i class="fas fa-circle-check mr-2"></i>{{ session('status') }}</p>
@endif

<div class="mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
    <p class="text-slate-500 text-sm max-w-3xl">
        L'IA analyse les articles, avis, fiches, événements et photos, puis classe ce qui mérite votre attention (score de 0 à 100 et raisons).
        <strong class="text-slate-300">Elle ne publie, ne refuse et ne supprime rien</strong> : vous tranchez, en commençant par les cas les plus risqués.
        Chaque contenu enregistré est analysé automatiquement.
    </p>
    <form method="POST" action="{{ route('admin.ai-moderation.scan') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('span').textContent='Analyse en cours…'">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-rotate text-xs"></i> <span>Analyser tous les contenus</span>
        </button>
    </form>
</div>

{{-- Indicateurs --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
    @foreach([
        ['Contenus analysés', $real['analysed'], 'fa-magnifying-glass'],
        ['Passés sans alerte', $pct($real['auto_ok_share']), 'fa-check'],
        ['À vérifier', $real['review'], 'fa-eye'],
        ['Problème probable', $real['likely'], 'fa-triangle-exclamation'],
        ['En attente de décision', $real['pending'], 'fa-hourglass-half'],
    ] as [$label, $val, $icon])
        <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
            <p class="text-slate-500 text-xs flex items-center gap-2"><i class="fas {{ $icon }} text-orange-400"></i>{{ $label }}</p>
            <p class="text-white text-xl font-bold mt-1">{{ $val }}</p>
        </div>
    @endforeach
</div>

{{-- Évaluation --}}
<details class="bg-green-900 border border-slate-800 rounded-xl mb-6">
    <summary class="cursor-pointer select-none px-5 py-4 text-white text-sm font-semibold"><i class="fas fa-chart-simple text-orange-400 mr-2"></i>Fiabilité de la détection (tests automatiques et décisions de l'administrateur)</summary>
    <div class="px-5 pb-5 grid grid-cols-1 lg:grid-cols-3 gap-4 text-sm">
        <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-4">
            <p class="text-white font-semibold mb-2">Textes — jeu de test synthétique</p>
            <p class="text-slate-400">{{ $t['cases'] }} textes étiquetés à l'avance</p>
            <p class="text-slate-300 mt-2">Précision <strong class="text-white">{{ $pct($t['precision']) }}</strong> · Rappel <strong class="text-white">{{ $pct($t['recall']) }}</strong> · F1 <strong class="text-white">{{ number_format($t['f1'], 2, ',', '') }}</strong></p>
            <p class="text-slate-500 text-xs mt-2">Fausses alertes : {{ $t['fp'] }} · Textes à signaler non détectés : {{ $t['fn'] }}.
                @foreach($t['misses'] as $m) <br>— {{ $m['title'] }} ({{ $m['why'] }}, score {{ $m['score'] }}) @endforeach</p>
        </div>
        <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-4">
            <p class="text-white font-semibold mb-2">Photos — images générées pour le test</p>
            <p class="text-slate-400">{{ $im['cases'] }} images : nette, floue, sombre, surexposée, petite, panoramique, doublon</p>
            <p class="text-slate-300 mt-2">Anomalies détectées <strong class="text-white">{{ $im['detected'] }} / {{ $im['detected'] + $im['missed'] }}</strong> · Fausses alertes <strong class="text-white">{{ $im['false_alarms'] }} / {{ $im['clean_cases'] }}</strong></p>
            <p class="text-slate-500 text-xs mt-2">La conformité du contenu (nudité, violence…) n'est contrôlée que si un modèle de vision est configuré (AI_VISION_DRIVER).</p>
        </div>
        <div class="rounded-lg bg-slate-800/60 border border-slate-700/80 p-4">
            <p class="text-white font-semibold mb-2">Accord avec l'administrateur</p>
            @if($dec['decided'])
                <p class="text-slate-300">Sur {{ $dec['decided'] }} dossiers traités : <strong class="text-white">{{ $dec['confirmed'] }}</strong> problèmes confirmés, <strong class="text-white">{{ $dec['dismissed'] }}</strong> fausses alertes.</p>
                <p class="text-slate-300 mt-2">Alertes confirmées : <strong class="text-white">{{ $pct($dec['agreement']) }}</strong>@if($dec['likely_agreement'] !== null) (« problème probable » : {{ $pct($dec['likely_agreement']) }})@endif</p>
            @else
                <p class="text-slate-400">Aucune décision enregistrée pour l'instant. Chaque clic sur « Problème confirmé » ou « Fausse alerte » alimente cette mesure.</p>
            @endif
        </div>
    </div>
</details>

{{-- Filtres --}}
<div class="flex flex-wrap items-center gap-2 mb-4">
    <span class="text-slate-500 text-xs mr-1">Niveau :</span>
    {!! $tab('À traiter', 'verdict', 'flagged', $verdict) !!} {!! $tab('Problème probable', 'verdict', 'likely', $verdict) !!} {!! $tab('À vérifier', 'verdict', 'review', $verdict) !!} {!! $tab('Sains', 'verdict', 'ok', $verdict) !!} {!! $tab('Tous', 'verdict', 'all', $verdict) !!}
    <span class="text-slate-500 text-xs ml-3 mr-1">Statut :</span>
    {!! $tab('En attente', 'status', 'pending', $status) !!} {!! $tab('Confirmés', 'status', 'confirmed', $status) !!} {!! $tab('Fausses alertes', 'status', 'dismissed', $status) !!} {!! $tab('Tous', 'status', 'all', $status) !!}
    <span class="text-slate-500 text-xs ml-3 mr-1">Nature :</span>
    {!! $tab('Tout', 'kind', 'all', $kind) !!} {!! $tab('Textes', 'kind', 'text', $kind) !!} {!! $tab('Photos', 'kind', 'image', $kind) !!}
</div>

{{-- File --}}
<div class="space-y-3">
    @forelse($reports as $r)
        @php [$bl, $bc, $bb] = $badge($r->verdict); $typeLabel = ['article' => 'Article', 'article_cover' => 'Couverture', 'review' => 'Avis', 'provider' => 'Prestataire', 'event' => 'Événement', 'media' => 'Photo', 'accommodation_media' => 'Photo hébergement'][$r->subject_type] ?? $r->subject_type; @endphp
        <article class="bg-green-900 border border-slate-800 rounded-xl p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-xs font-bold px-2 py-0.5 rounded" style="background:{{ $bb }};color:{{ $bc }}">{{ $bl }} · {{ $r->score }}/100</span>
                        <span class="text-xs text-slate-500">{{ $typeLabel }} #{{ $r->subject_id }}</span>
                        @if($r->status === 'confirmed')<span class="text-xs text-red-400">problème confirmé</span>@elseif($r->status === 'dismissed')<span class="text-xs text-emerald-400">fausse alerte</span>@endif
                    </div>
                    <p class="text-white text-sm font-semibold">
                        @if($r->url)<a href="{{ $r->url }}" target="_blank" rel="noopener" class="hover:text-orange-400">{{ $r->label }} <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-500"></i></a>@else {{ $r->label }} @endif
                    </p>
                    <ul class="mt-2 space-y-1">
                        @foreach($r->reasons as $reason)
                            <li class="text-sm text-slate-300"><i class="fas fa-circle-exclamation text-orange-400 text-xs mr-1.5"></i>{{ $reason['label'] }}@if($reason['detail']) <span class="text-slate-500">— {{ $reason['detail'] }}</span>@endif</li>
                        @endforeach
                    </ul>
                    @php $tg = $r->suggestions['tagging'] ?? null; @endphp
                    @if($tg && ($tg['category'] || $tg['tags'] || $tg['interests']))
                        <p class="mt-2 text-xs text-slate-500">
                            <i class="fas fa-tags mr-1"></i>Suggestions :
                            @if($tg['category']) rubrique « {{ $tg['category']['name'] }} » ({{ round($tg['category']['confidence'] * 100) }} %){{ !empty($tg['category']['differs']) ? ' — différente de l\'actuelle' : '' }} @endif
                            @if($tg['tags']) · étiquettes : {{ collect($tg['tags'])->pluck('name')->implode(', ') }} @endif
                        </p>
                    @endif
                    @if(!empty($r->suggestions['duplicates']))
                        <p class="mt-1 text-xs text-slate-500"><i class="fas fa-clone mr-1"></i>Proche de : {{ collect($r->suggestions['duplicates'])->pluck('label')->implode(' ; ') }}</p>
                    @endif
                    @if($r->kind === 'image' && ($r->suggestions['content_check'] ?? '') === 'not_done')
                        <p class="mt-1 text-xs text-slate-600">Conformité du contenu de la photo : non vérifiée (aucun modèle de vision configuré).</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    @foreach([['confirmed', 'Problème confirmé', 'fa-flag'], ['dismissed', 'Fausse alerte', 'fa-check']] as [$val, $lab, $ic])
                        @if($r->status !== $val)
                        <form method="POST" action="{{ route('admin.ai-moderation.decide', $r->id) }}">@csrf @method('PATCH')
                            <input type="hidden" name="decision" value="{{ $val }}">
                            <button class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg {{ $val === 'confirmed' ? 'bg-red-500/15 text-red-400 hover:bg-red-500/25' : 'bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25' }}"><i class="fas {{ $ic }} text-[10px]"></i>{{ $lab }}</button>
                        </form>
                        @endif
                    @endforeach
                    @if($r->status !== 'pending')
                        <form method="POST" action="{{ route('admin.ai-moderation.decide', $r->id) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="pending">
                            <button class="text-xs text-slate-500 hover:text-white px-2 py-1.5">Remettre en attente</button></form>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <p class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center text-slate-500 text-sm">Aucun contenu à afficher avec ces filtres. @if(! $real['analysed']) Lancez « Analyser tous les contenus ». @endif</p>
    @endforelse
</div>
<div class="mt-5">{{ $reports->links() }}</div>
@endsection
