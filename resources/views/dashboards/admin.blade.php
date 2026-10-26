@extends('layouts.app')

@section('title', 'Administration')
@section('page-title', 'Tableau de bord — Administration')

@section('content')
{{--
    Tableau de bord admin — lecture en « pyramide » : 1. ce qui attend une action, 2. chiffres clés,
    3. audience et tendances, 4. détail (activité, inscrits, répartitions, modération).
    Présentation seule : mêmes données ($stats, $audience, séries) et mêmes graphiques (#chartRevenue,
    #chartSignups, #chartProviderStatus, #chartSubscriptionPlans). Styles propres (.ad-*), aplats, ombre au survol.
--}}
@php
    $adUser = auth()->user();
    $adHour = (int) now()->format('G');
    $adHello = $adHour < 12 ? 'Bonjour' : ($adHour < 18 ? 'Bon après-midi' : 'Bonsoir');

    $revVals = $revenue_by_month['values'];
    $prevMonth = $revVals[count($revVals) - 2] ?? 0;
    $curMonth = $revVals[count($revVals) - 1] ?? 0;
    $revenueDelta = $prevMonth > 0 ? round(($curMonth - $prevMonth) / $prevMonth * 100, 1) : null;

    $signupVals = $signups_by_day['values'] ?? [];
    $signups14 = array_sum($signupVals);
    $signups7 = array_sum(array_slice($signupVals, -7));
    $signupsPrev7 = array_sum(array_slice($signupVals, 0, max(0, count($signupVals) - 7)));
    $signupsDelta = $signupsPrev7 > 0 ? round(($signups7 - $signupsPrev7) / $signupsPrev7 * 100) : null;

    $adTodo = [
        ['n' => $stats['pending_providers'], 'label' => 'Prestataires à valider', 'hint' => 'Comptes et pièces à vérifier', 'icon' => 'fa-user-check', 'url' => route('admin.provider-verifications.index')],
        ['n' => $stats['pending_reviews'], 'label' => 'Avis à modérer', 'hint' => 'Publiés après validation', 'icon' => 'fa-star-half-stroke', 'url' => route('admin.reviews.index')],
        ['n' => $stats['articles_review'], 'label' => 'Articles en révision', 'hint' => 'En attente de relecture', 'icon' => 'fa-pen-nib', 'url' => route('admin.articles.index')],
    ];
    $adTodoTotal = collect($adTodo)->sum('n');

    // Mini-courbe de tendance (SVG) pour les chiffres clés : trait 2 px, point final, aplat à 10 %.
    $adSpark = function (array $vals, int $w = 132, int $h = 34): ?array {
        $vals = array_values(array_map('floatval', $vals));
        $n = count($vals);
        if ($n < 2 || max($vals) <= 0) {
            return null;
        }
        $max = max($vals);
        $pts = [];
        foreach ($vals as $i => $v) {
            $pts[] = [round($i * ($w - 4) / ($n - 1) + 2, 1), round($h - 3 - ($v / $max) * ($h - 8), 1)];
        }
        $line = implode(' ', array_map(fn ($p) => $p[0].','.$p[1], $pts));
        $area = 'M'.$pts[0][0].','.($h - 1).' L'.str_replace(' ', ' L', $line).' L'.end($pts)[0].','.($h - 1).' Z';

        return ['w' => $w, 'h' => $h, 'line' => $line, 'area' => $area, 'end' => end($pts)];
    };
    $revenueSpark = $adSpark($revVals);
    $signupSpark = $adSpark($signupVals);
    $planLabels = ['bronze' => 'Bronze', 'silver' => 'Argent', 'gold' => 'Or'];

    $adKpis = [
        ['label' => 'Utilisateurs', 'value' => number_format($stats['total_users'], 0, ',', ' '), 'sub' => '+'.$stats['users_today'].' aujourd\'hui', 'icon' => 'fa-users', 'tone' => 'green', 'url' => route('admin.users.index'), 'spark' => $signupSpark, 'spark_label' => 'Inscriptions sur 14 jours'],
        ['label' => 'Prestataires actifs', 'value' => number_format($stats['active_providers'], 0, ',', ' '), 'sub' => $stats['pending_providers'].' en attente', 'icon' => 'fa-store', 'tone' => 'green', 'url' => route('admin.providers.index')],
        ['label' => 'Abonnements actifs', 'value' => number_format($stats['active_subscriptions'], 0, ',', ' '), 'sub' => 'Forfaits en cours', 'icon' => 'fa-gem', 'tone' => 'gold', 'url' => route('admin.subscriptions.index')],
        ['label' => 'Articles publiés', 'value' => number_format($stats['published_articles'], 0, ',', ' '), 'sub' => $stats['articles_review'].' en révision', 'icon' => 'fa-newspaper', 'tone' => 'orange', 'url' => route('admin.articles.index')],
        ['label' => 'Événements à venir', 'value' => number_format($stats['upcoming_events'], 0, ',', ' '), 'sub' => 'Publiés à l\'agenda', 'icon' => 'fa-calendar-days', 'tone' => 'orange', 'url' => null],
    ];

    $roleLabels = ['admin' => 'Admin', 'editor' => 'Éditeur', 'provider' => 'Prestataire', 'visitor' => 'Client'];
    $statusMeta = [
        'active'    => ['label' => 'Actifs',     'color' => '#00874F'],
        'pending'   => ['label' => 'En attente', 'color' => '#EB6E00'],
        'suspended' => ['label' => 'Suspendus',  'color' => '#EB6E00'],
        'inactive'  => ['label' => 'Inactifs',   'color' => '#8AA99B'],
    ];
@endphp

{{-- Fond de page : motifs ivoiriens très discrets, fixes derrière le contenu (décoratif). --}}
<div class="ad-bg" aria-hidden="true">@include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])</div>
<div class="ad">

    {{-- ── 1. Accueil : salutation, date, raccourcis ── --}}
    <header class="ad-hero">
        @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
        <div class="ad-hero__in">
            <div class="ad-hero__txt">
                <p class="ad-hero__date">{{ ucfirst(now()->translatedFormat('l j F Y')) }}</p>
                <h1 class="ad-hero__title">{{ $adHello }}, {{ $adUser?->first_name ?: 'Admin' }}</h1>
                <p class="ad-hero__sub">
                    @if($adTodoTotal > 0)
                        {{ $adTodoTotal }} {{ $adTodoTotal > 1 ? 'éléments attendent' : 'élément attend' }} votre validation aujourd'hui.
                    @else
                        Tout est à jour : aucune validation en attente.
                    @endif
                </p>
            </div>
            <nav class="ad-hero__actions" aria-label="Raccourcis">
                <a href="{{ route('admin.provider-verifications.index') }}" class="ad-btn is-primary"><i class="fas fa-user-check" aria-hidden="true"></i>Valider des prestataires</a>
                <a href="{{ route('admin.newsletter.index') }}" class="ad-btn"><i class="fas fa-envelope-open-text" aria-hidden="true"></i>Newsletter</a>
                <a href="{{ route('admin.users.index') }}" class="ad-btn"><i class="fas fa-user-plus" aria-hidden="true"></i>Utilisateurs</a>
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="ad-btn"><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>Voir le site</a>
            </nav>
        </div>
    </header>

    {{-- ── 2. À traiter : seules les files non vides sont mises en avant (couleur réservée à l'action) ── --}}
    <section class="ad-todo" aria-label="À traiter">
        @foreach($adTodo as $t)
            <a href="{{ $t['url'] }}" class="ad-todo__item {{ $t['n'] > 0 ? 'is-hot' : 'is-ok' }}">
                <span class="ad-todo__ico"><i class="fas {{ $t['n'] > 0 ? $t['icon'] : 'fa-check' }}" aria-hidden="true"></i></span>
                <span class="ad-todo__txt">
                    <strong>{{ $t['label'] }}</strong>
                    <small>{{ $t['n'] > 0 ? $t['hint'] : 'Rien en attente' }}</small>
                </span>
                <span class="ad-todo__n">{{ $t['n'] }}</span>
                <i class="fas fa-chevron-right ad-todo__go" aria-hidden="true"></i>
            </a>
        @endforeach
    </section>

    {{-- ── 3. Chiffres clés : revenu en tête, puis la plateforme ── --}}
    <section class="ad-kpis" aria-label="Chiffres clés">
        <article class="ad-kpi is-revenue">
            <div class="ad-kpi__top">
                <span class="ad-kpi__label">Revenu du mois</span>
                <span class="ad-kpi__ico"><i class="fas fa-coins" aria-hidden="true"></i></span>
            </div>
            <p class="ad-kpi__value">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} <small>FCFA</small></p>
            @if($revenueSpark)
                <svg class="ad-spark is-orange" viewBox="0 0 {{ $revenueSpark['w'] }} {{ $revenueSpark['h'] }}" preserveAspectRatio="none" role="img" aria-label="Revenu sur 12 mois">
                    <path d="{{ $revenueSpark['area'] }}" class="ad-spark__area"/>
                    <polyline points="{{ $revenueSpark['line'] }}" class="ad-spark__line"/>
                    <circle cx="{{ $revenueSpark['end'][0] }}" cy="{{ $revenueSpark['end'][1] }}" r="3" class="ad-spark__dot"/>
                </svg>
            @endif
            <p class="ad-kpi__sub">
                @if($revenueDelta !== null)
                    <span class="ad-delta {{ $revenueDelta >= 0 ? 'is-up' : 'is-down' }}"><i class="fas {{ $revenueDelta >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}" aria-hidden="true"></i>{{ $revenueDelta >= 0 ? '+' : '' }}{{ $revenueDelta }} %</span> vs mois dernier
                @else
                    Paiements complétés ce mois-ci
                @endif
            </p>
            <a href="{{ route('admin.payments.index') }}" class="ad-kpi__link">Voir les paiements <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </article>
        @foreach($adKpis as $k)
            @php $tag = $k['url'] ? 'a' : 'div'; @endphp
            <{{ $tag }} @if($k['url']) href="{{ $k['url'] }}" @endif class="ad-kpi is-{{ $k['tone'] }}">
                <div class="ad-kpi__top">
                    <span class="ad-kpi__label">{{ $k['label'] }}</span>
                    <span class="ad-kpi__ico"><i class="fas {{ $k['icon'] }}" aria-hidden="true"></i></span>
                </div>
                <p class="ad-kpi__value">{{ $k['value'] }}</p>
                @if(! empty($k['spark']))
                    <svg class="ad-spark is-{{ $k['tone'] }}" viewBox="0 0 {{ $k['spark']['w'] }} {{ $k['spark']['h'] }}" preserveAspectRatio="none" role="img" aria-label="{{ $k['spark_label'] }}">
                        <path d="{{ $k['spark']['area'] }}" class="ad-spark__area"/>
                        <polyline points="{{ $k['spark']['line'] }}" class="ad-spark__line"/>
                        <circle cx="{{ $k['spark']['end'][0] }}" cy="{{ $k['spark']['end'][1] }}" r="3" class="ad-spark__dot"/>
                    </svg>
                @endif
                <p class="ad-kpi__sub">{{ $k['sub'] }}</p>
            </{{ $tag }}>
        @endforeach
    </section>

    {{-- ── 4. Audience du site (en ligne, aujourd'hui, semaine, mois, année) ── --}}
    @include('partials.admin-audience')

    {{-- ── 5. Tendances ── --}}
    <section class="ad-grid ad-grid--2-1">
        <article class="ad-card">
            <header class="ad-card__head">
                <div>
                    <h2 class="ad-card__title">Revenu — 12 derniers mois</h2>
                    <p class="ad-card__sub">Paiements complétés, par mois</p>
                </div>
                @if($revenueDelta !== null)
                    <span class="ad-delta {{ $revenueDelta >= 0 ? 'is-up' : 'is-down' }}"><i class="fas {{ $revenueDelta >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}" aria-hidden="true"></i>{{ $revenueDelta >= 0 ? '+' : '' }}{{ $revenueDelta }} %</span>
                @endif
            </header>
            <div class="ad-chart" style="height: 272px">
                <canvas id="chartRevenue" role="img" aria-label="Revenu par mois sur 12 mois"></canvas>
                @if(array_sum($revVals) <= 0)
                    <p class="ad-chart__empty"><i class="fas fa-coins" aria-hidden="true"></i><strong>Aucun paiement complété sur 12 mois</strong><span>La courbe se remplira dès les premiers paiements.</span></p>
                @endif
            </div>
            <details class="ad-data">
                <summary>Voir les données</summary>
                <table><thead><tr><th>Mois</th><th>Revenu</th></tr></thead><tbody>
                    @foreach($revenue_by_month['labels'] as $i => $m)<tr><td>{{ $m }}</td><td>{{ number_format($revVals[$i] ?? 0, 0, ',', ' ') }} FCFA</td></tr>@endforeach
                </tbody></table>
            </details>
        </article>
        <article class="ad-card">
            <header class="ad-card__head">
                <div>
                    <h2 class="ad-card__title">Nouvelles inscriptions</h2>
                    <p class="ad-card__sub">{{ $signups14 }} sur 14 jours · {{ $signups7 }} cette semaine</p>
                </div>
                @if($signupsDelta !== null)
                    <span class="ad-delta {{ $signupsDelta >= 0 ? 'is-up' : 'is-down' }}">{{ $signupsDelta >= 0 ? '+' : '' }}{{ $signupsDelta }} %</span>
                @endif
            </header>
            <div class="ad-chart" style="height: 272px"><canvas id="chartSignups" role="img" aria-label="Inscriptions par jour sur 14 jours"></canvas></div>
            <details class="ad-data">
                <summary>Voir les données</summary>
                <table><thead><tr><th>Jour</th><th>Inscriptions</th></tr></thead><tbody>
                    @foreach($signups_by_day['labels'] as $i => $d)<tr><td>{{ $d }}</td><td>{{ $signupVals[$i] ?? 0 }}</td></tr>@endforeach
                </tbody></table>
            </details>
        </article>
    </section>

    {{-- ── 6. Détail ── --}}
    <section class="ad-grid ad-grid--2-1">
        <div class="ad-stack">
            {{-- Activité des prestataires --}}
            <article class="ad-card is-flush">
                <header class="ad-card__head is-bar">
                    <h2 class="ad-card__title"><i class="fas fa-store" aria-hidden="true"></i>Activité récente des prestataires</h2>
                    <a href="{{ route('admin.providers.index') }}" class="ad-more">Tous les prestataires <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </header>
                <div class="ad-feed">
                    @php
                        $activityTones = ['orange' => 'is-orange', 'emerald' => 'is-green', 'sky' => 'is-blue', 'violet' => 'is-violet', 'rose' => 'is-rose'];
                    @endphp
                    @forelse($recent_provider_activity as $activity)
                        <a href="{{ $activity['url'] }}" class="ad-feed__row">
                            <span class="ad-feed__ico {{ $activityTones[$activity['color']] ?? '' }}"><i class="fas {{ $activity['icon'] }}" aria-hidden="true"></i></span>
                            <span class="ad-feed__txt">
                                <strong>{{ $activity['label'] }}</strong>
                                <small>{{ $activity['title'] }}@if($activity['provider']) · {{ $activity['provider'] }}@endif</small>
                            </span>
                            <time class="ad-feed__time">{{ $activity['date']->diffForHumans() }}</time>
                        </a>
                    @empty
                        <p class="ad-empty"><i class="fas fa-inbox" aria-hidden="true"></i>Aucune activité récente de la part des prestataires.</p>
                    @endforelse
                </div>
            </article>

            {{-- Derniers inscrits --}}
            <article class="ad-card is-flush">
                <header class="ad-card__head is-bar">
                    <h2 class="ad-card__title"><i class="fas fa-user-plus" aria-hidden="true"></i>Derniers utilisateurs inscrits</h2>
                    <a href="{{ route('admin.users.index') }}" class="ad-more">Voir tout <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </header>
                <div class="ad-table-wrap">
                    <table class="ad-table">
                        <thead>
                            <tr><th>Utilisateur</th><th class="is-hide-sm">Rôle</th><th class="is-hide-md">Inscription</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recent_users as $user)
                                <tr>
                                    <td>
                                        <span class="ad-user">
                                            <span class="ad-avatar">{{ strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1)) }}</span>
                                            <span class="ad-user__txt"><strong>{{ $user->first_name }} {{ $user->last_name }}</strong><small>{{ $user->email }}</small></span>
                                        </span>
                                    </td>
                                    <td class="is-hide-sm"><span class="ad-role is-{{ $user->role }}">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span></td>
                                    <td class="is-hide-md ad-muted">{{ $user->created_at->diffForHumans() }}</td>
                                    <td><span class="ad-status {{ $user->is_active ? 'is-on' : '' }}">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="ad-empty">Aucun utilisateur.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <div class="ad-stack">
            {{-- Prestataires par statut --}}
            <article class="ad-card">
                @php $providersTotal = $providers_by_status->sum(); @endphp
                <header class="ad-card__head">
                    <div>
                        <h2 class="ad-card__title">Prestataires par statut</h2>
                        <p class="ad-card__sub">{{ $providersTotal }} au total</p>
                    </div>
                </header>
                @if($providersTotal > 0)
                    {{-- Barre empilée en HTML : segments séparés par 2 px de surface, proportions exactes. --}}
                    <div class="ad-stackbar" role="img" aria-label="Répartition des prestataires par statut">
                        @foreach($providers_by_status as $status => $count)
                            @php $meta = $statusMeta[$status] ?? ['label' => ucfirst($status), 'color' => '#8AA99B']; @endphp
                            <span class="ad-stackbar__seg" style="flex-grow: {{ $count }}; background: {{ $meta['color'] }}" title="{{ $meta['label'] }} : {{ $count }}"></span>
                        @endforeach
                    </div>
                    <ul class="ad-legend">
                        @foreach($providers_by_status as $status => $count)
                            @php $meta = $statusMeta[$status] ?? ['label' => ucfirst($status), 'color' => '#8AA99B']; @endphp
                            <li><span class="ad-legend__dot" style="background: {{ $meta['color'] }}"></span><span>{{ $meta['label'] }}</span><strong>{{ $count }}</strong><small>{{ round($count / $providersTotal * 100) }} %</small></li>
                        @endforeach
                    </ul>
                @else
                    <p class="ad-empty is-small">Aucune donnée.</p>
                @endif
            </article>

            {{-- Abonnements par forfait --}}
            <article class="ad-card">
                <header class="ad-card__head">
                    <h2 class="ad-card__title">Abonnements actifs par forfait</h2>
                    <a href="{{ route('admin.subscriptions.index') }}" class="ad-more">Gérer <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </header>
                @if($subscriptions_by_plan->sum() > 0)
                    {{-- Liste à barres : une seule couleur (catégories sans ordre de valeur), valeur au bout de la barre. --}}
                    @php $planMax = max(1, $subscriptions_by_plan->max()); $planTotal = $subscriptions_by_plan->sum(); @endphp
                    <ul class="ad-barlist">
                        @foreach($subscriptions_by_plan->sortDesc() as $code => $count)
                            <li>
                                <span class="ad-barlist__label">{{ $planLabels[strtolower((string) $code)] ?? ucfirst((string) $code) }}</span>
                                <span class="ad-barlist__track"><span class="ad-barlist__bar" style="width: {{ max(4, round($count / $planMax * 100)) }}%"></span></span>
                                <span class="ad-barlist__value">{{ $count }} <small>{{ round($count / $planTotal * 100) }} %</small></span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="ad-empty is-small">Aucun abonnement actif.</p>
                @endif
            </article>

            {{-- Avis en attente --}}
            <article class="ad-card is-flush">
                <header class="ad-card__head is-bar">
                    <h2 class="ad-card__title"><i class="fas fa-star-half-stroke" aria-hidden="true"></i>Avis à modérer
                        @if($stats['pending_reviews'] > 0)<span class="ad-count">{{ $stats['pending_reviews'] }}</span>@endif
                    </h2>
                    <a href="{{ route('admin.reviews.index') }}" class="ad-more">Modérer <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </header>
                @forelse($pending_reviews as $review)
                    <a href="{{ route('admin.reviews.index') }}" class="ad-review">
                        <span class="ad-review__head">
                            <strong>{{ $review->user->first_name ?? 'Anonyme' }}</strong>
                            <span class="ad-muted">→</span>
                            <span class="ad-review__to">{{ $review->provider->business_name ?? '—' }}</span>
                        </span>
                        <span class="ad-review__stars" aria-label="{{ $review->rating }} sur 5">
                            @for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $review->rating ? 'is-on' : '' }}" aria-hidden="true"></i>@endfor
                            <small>{{ $review->created_at->diffForHumans() }}</small>
                        </span>
                        @if($review->comment)<span class="ad-review__text">{{ $review->comment }}</span>@endif
                    </a>
                @empty
                    <p class="ad-empty"><i class="fas fa-circle-check is-ok" aria-hidden="true"></i>Aucun avis en attente. Tout est à jour !</p>
                @endforelse
            </article>
        </div>
    </section>
</div>

<style>
    .ad { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 20px; color: #0E3B2A; font-family: 'Montserrat', sans-serif; }
    .ad-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
    .ad-bg > .ivm .ivm-g { opacity: .05 !important; }
    .ad-bg > .ivm .ivm-g > :nth-child(odd) { stroke: #B85600 !important; }
    .ad-bg > .ivm .ivm-g > :nth-child(even) { stroke: #00874F !important; }
    @media (min-width: 1024px) { .ad-bg { left: 18rem; } }
    .ad a { text-decoration: none; }

    /* Accueil */
    .ad-hero { position: relative; overflow: hidden; border-radius: 22px; background: #00874F; color: #ffffff; }
    .ad-hero > .ivm .ivm-g { opacity: .05 !important; }
    .ad-hero > .ivm .ivm-g > * { stroke: #D8E9E0 !important; }
    .ad-hero > .ivm .ivm-g > circle[fill] { fill: #EB6E00 !important; stroke: #EB6E00 !important; }
    .ad-hero__in { position: relative; z-index: 1; display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; padding: 26px 28px; }
    .ad-hero__date { margin: 0; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #F7C396; }
    .ad-hero__title { margin: 6px 0 0; font-family: 'Montserrat', serif; font-size: clamp(24px, 2.4vw, 32px); font-weight: 700; line-height: 1.15; color: #ffffff; }
    .ad-hero__sub { margin: 6px 0 0; font-size: 14px; color: rgb(255 255 255 / .78); }
    .ad-hero__actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .ad-btn { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 15px; border-radius: 999px; border: 1px solid rgb(255 255 255 / .22); background: rgb(255 255 255 / .08); color: #ffffff !important; font-size: 13px; font-weight: 600; transition: background .15s ease, border-color .15s ease; }
    .ad-btn:hover { background: rgb(255 255 255 / .16); border-color: rgb(255 255 255 / .4); }
    .ad-btn.is-primary { background: #EB6E00; border-color: #EB6E00; }
    .ad-btn.is-primary:hover { background: #B85600; border-color: #B85600; }
    .ad-btn i { font-size: 12px; }

    /* À traiter */
    .ad-todo { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .ad-todo__item { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 16px; border: 1px solid #D8E9E0; background: #ffffff; color: #0E3B2A !important; transition: border-color .15s ease, box-shadow .15s ease; }
    .ad-todo__item:hover { border-color: #EB6E00; box-shadow: 0 16px 34px -26px rgb(14 59 42 / .5); }
    .ad-todo__ico { width: 40px; height: 40px; flex: none; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }
    .ad-todo__item.is-hot .ad-todo__ico { background: #F3F8F5; color: #EB6E00; }
    .ad-todo__item.is-ok .ad-todo__ico { background: #F3F8F5; color: #00874F; }
    .ad-todo__txt { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.25; }
    .ad-todo__txt strong { font-size: 14px; font-weight: 700; }
    .ad-todo__txt small { font-size: 12px; color: #557466; }
    .ad-todo__n { min-width: 32px; height: 32px; padding: 0 9px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800; }
    .ad-todo__item.is-hot .ad-todo__n { background: #EB6E00; color: #ffffff; }
    .ad-todo__item.is-ok .ad-todo__n { background: #F3F8F5; color: #557466; }
    .ad-todo__go { font-size: 11px; color: #8AA99B; }

    /* Chiffres clés */
    .ad-kpis { display: grid; grid-template-columns: 1.35fr repeat(5, minmax(0, 1fr)); gap: 12px; }
    .ad-kpi { display: flex; flex-direction: column; gap: 6px; padding: 16px; border-radius: 18px; background: #ffffff; border: 1px solid #D8E9E0; color: #0E3B2A !important; transition: border-color .15s ease, box-shadow .15s ease; }
    a.ad-kpi:hover { border-color: #8AA99B; box-shadow: 0 16px 34px -26px rgb(14 59 42 / .5); }
    .ad-kpi__top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .ad-kpi__label { font-size: 12.5px; font-weight: 600; color: #24503D; }
    .ad-kpi__ico { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; }
    .ad-kpi.is-green .ad-kpi__ico { background: #F3F8F5; color: #00874F; }
    .ad-kpi.is-gold .ad-kpi__ico { background: #F3F8F5; color: #B85600; }
    .ad-kpi.is-orange .ad-kpi__ico { background: #F3F8F5; color: #EB6E00; }
    .ad-kpi__value { margin: 0; font-size: 28px; font-weight: 800; line-height: 1.1; letter-spacing: -.02em; color: #0E3B2A; }
    .ad-kpi__value small { font-size: 14px; font-weight: 700; color: #EB6E00; }
    .ad-kpi__sub { margin: 0; font-size: 12px; color: #557466; }
    .ad-kpi.is-revenue { background: #F3F8F5; border-color: #D8E9E0; }
    .ad-kpi.is-revenue .ad-kpi__ico { background: #EB6E00; color: #ffffff; }
    .ad-kpi__link { margin-top: auto; padding-top: 4px; display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #B85600 !important; }
    .ad-kpi__link i { font-size: 10px; }
    .ad-delta { display: inline-flex; align-items: center; gap: 5px; height: 24px; padding: 0 9px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .ad-delta.is-up { background: #F3F8F5; color: #00874F; }
    .ad-delta.is-down { background: #fdeceb; color: #b42318; }
    .ad-delta i { font-size: 10px; }

    /* Cartes et grilles */
    .ad-grid { display: grid; gap: 20px; align-items: start; }
    .ad-grid--2-1 { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    .ad-stack { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
    .ad-card { padding: 18px 20px; border-radius: 18px; background: #ffffff; border: 1px solid #D8E9E0; min-width: 0; }
    .ad-card.is-flush { padding: 0; overflow: hidden; }
    .ad-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
    .ad-card__head.is-bar { margin: 0; padding: 15px 20px; border-bottom: 1px solid #F3F8F5; align-items: center; }
    .ad-card__title { margin: 0; display: inline-flex; align-items: center; gap: 9px; font-size: 14.5px; font-weight: 700; color: #0E3B2A; }
    .ad-card__title > i { width: 28px; height: 28px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; background: #F3F8F5; color: #EB6E00; font-size: 12px; }
    .ad-card__sub { margin: 3px 0 0; font-size: 12px; color: #557466; }
    .ad-more { flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #B85600 !important; }
    .ad-more:hover { color: #EB6E00 !important; text-decoration: underline; text-underline-offset: 3px; }
    .ad-more i { font-size: 10px; }
    .ad-chart { position: relative; }
    .ad-count { min-width: 22px; height: 22px; padding: 0 7px; border-radius: 999px; background: #EB6E00; color: #ffffff; font-size: 11.5px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }

    /* Fil d'activité */
    .ad-feed { max-height: 420px; overflow-y: auto; scrollbar-width: thin; }
    .ad-feed__row { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: #0E3B2A !important; border-bottom: 1px solid #F3F8F5; transition: background .15s ease; }
    .ad-feed__row:last-child { border-bottom: 0; }
    .ad-feed__row:hover { background: #FFFFFF; }
    .ad-feed__ico { width: 34px; height: 34px; flex: none; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #F3F8F5; color: #24503D; font-size: 12px; }
    .ad-feed__ico.is-orange { background: #F3F8F5; color: #EB6E00; }
    .ad-feed__ico.is-green { background: #F3F8F5; color: #00874F; }
    .ad-feed__ico.is-blue { background: #e7f1f8; color: #24678f; }
    .ad-feed__ico.is-violet { background: #F3F8F5; color: #6a4a9b; }
    .ad-feed__ico.is-rose { background: #fdeceb; color: #b42318; }
    .ad-feed__txt { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.3; }
    .ad-feed__txt strong { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ad-feed__txt small { font-size: 12px; color: #557466; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ad-feed__time { flex-shrink: 0; font-size: 12px; color: #557466; }

    /* Tableau */
    .ad-table-wrap { overflow-x: auto; }
    .ad-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ad-table th { padding: 10px 20px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #557466 !important; background: #FFFFFF !important; border-bottom: 1px solid #F3F8F5; }
    .ad-table thead tr { background: #FFFFFF !important; }
    html:not(.dark) body.adm main .ad .ad-table thead th { background-color: #FFFFFF !important; color: #557466 !important; border-color: #F3F8F5 !important; }
    .ad-table td { padding: 11px 20px; border-bottom: 1px solid #F3F8F5; vertical-align: middle; }
    .ad-table tr:last-child td { border-bottom: 0; }
    .ad-table tbody tr:hover { background: #FFFFFF; }
    .ad-user { display: flex; align-items: center; gap: 11px; min-width: 0; }
    .ad-avatar { width: 34px; height: 34px; flex: none; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: #F3F8F5; color: #24503D; font-size: 12px; font-weight: 700; }
    .ad-user__txt { display: flex; flex-direction: column; min-width: 0; line-height: 1.3; }
    .ad-user__txt strong { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px; }
    .ad-user__txt small { font-size: 12px; color: #557466; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px; }
    .ad-role { display: inline-flex; height: 24px; padding: 0 9px; align-items: center; border-radius: 999px; font-size: 12px; font-weight: 600; background: #F3F8F5; color: #24503D; }
    .ad-role.is-visitor { background: #F3F8F5; color: #00874F; }
    .ad-role.is-provider { background: #F3F8F5; color: #B85600; }
    .ad-role.is-admin { background: #fdeceb; color: #8a241c; }
    .ad-role.is-editor { background: #e7f1f8; color: #1d4f6e; }
    .ad-status { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; color: #557466; }
    .ad-status::before { content: ''; width: 8px; height: 8px; border-radius: 999px; background: #8AA99B; }
    .ad-status.is-on { color: #00874F; }
    .ad-status.is-on::before { background: #00874F; }
    .ad-muted { color: #557466; }

    /* Répartitions */
    .ad-legend { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 12.5px; }
    .ad-legend li { display: flex; align-items: center; gap: 8px; color: #24503D; }
    .ad-legend li strong { margin-left: auto; color: #0E3B2A; }
    .ad-legend__dot { width: 9px; height: 9px; border-radius: 999px; flex: none; }
    .ad-legend li small { min-width: 34px; text-align: right; color: #557466; font-size: 11.5px; }

    /* ── Graphiques modernes : mini-courbes, barre empilée, liste à barres, info-bulle, vue tableau ── */
    .ad-spark { display: block; width: 100%; height: 34px; margin: 2px 0 2px; overflow: visible; }
    .ad-spark__line { fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; vector-effect: non-scaling-stroke; }
    .ad-spark__area { stroke: none; }
    .ad-spark__dot { stroke: #ffffff; stroke-width: 2; vector-effect: non-scaling-stroke; }
    .ad-spark.is-orange .ad-spark__line { stroke: #EB6E00; } .ad-spark.is-orange .ad-spark__area { fill: rgba(235,110,0, .10); } .ad-spark.is-orange .ad-spark__dot { fill: #EB6E00; }
    .ad-spark.is-green .ad-spark__line { stroke: #00874F; } .ad-spark.is-green .ad-spark__area { fill: rgba(0,135,79, .10); } .ad-spark.is-green .ad-spark__dot { fill: #00874F; }
    .ad-kpi.is-revenue .ad-spark__dot { stroke: #F3F8F5; }

    .ad-chart__empty { position: absolute; inset: 0 0 26px 40px; margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; text-align: center; pointer-events: none; }
    .ad-chart__empty i { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #F3F8F5; color: #EB6E00; font-size: 15px; margin-bottom: 4px; }
    .ad-chart__empty strong { font-size: 13.5px; color: #0E3B2A; }
    .ad-chart__empty span { font-size: 12px; color: #557466; }

    .ad-tip { position: absolute; z-index: 5; min-width: 110px; padding: 9px 11px; border-radius: 12px; background: #ffffff; border: 1px solid #D8E9E0; box-shadow: 0 14px 30px -18px rgb(14 59 42 / .45); pointer-events: none; opacity: 0; transition: opacity .12s ease, left .08s ease, top .08s ease; white-space: nowrap; }
    .ad-tip__title { margin: 0 0 4px; font-size: 11.5px; color: #557466; }
    .ad-tip__row { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 13.5px; color: #0E3B2A; }
    .ad-tip__row strong { font-weight: 700; font-variant-numeric: tabular-nums; }
    .ad-tip__key { width: 12px; height: 3px; border-radius: 2px; flex: none; }

    .ad-stackbar { display: flex; gap: 2px; height: 14px; border-radius: 999px; overflow: hidden; background: #ffffff; }
    .ad-stackbar__seg { min-width: 6px; flex-basis: 0; }

    .ad-barlist { display: flex; flex-direction: column; gap: 12px; margin: 4px 0 0; padding: 0; list-style: none; }
    .ad-barlist li { display: grid; grid-template-columns: 64px minmax(0, 1fr) auto; align-items: center; gap: 12px; font-size: 13px; }
    .ad-barlist__label { font-weight: 600; color: #0E3B2A; }
    .ad-barlist__track { height: 12px; border-radius: 999px; background: #F3F8F5; overflow: hidden; }
    .ad-barlist__bar { display: block; height: 100%; border-radius: 999px; background: #EB6E00; transition: width .6s ease; }
    .ad-barlist__value { font-weight: 700; color: #0E3B2A; font-variant-numeric: tabular-nums; text-align: right; }
    .ad-barlist__value small { font-weight: 500; font-size: 11.5px; color: #557466; margin-left: 4px; }

    .ad-data { margin-top: 10px; border-top: 1px solid #F3F8F5; padding-top: 8px; }
    .ad-data > summary { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; list-style: none; font-size: 12.5px; font-weight: 700; color: #B85600; }
    .ad-data > summary::-webkit-details-marker { display: none; }
    .ad-data > summary::before { content: '▸'; font-size: 10px; transition: transform .15s ease; }
    .ad-data[open] > summary::before { transform: rotate(90deg); }
    .ad-data table { width: 100%; margin-top: 8px; border-collapse: collapse; font-size: 12.5px; font-variant-numeric: tabular-nums; }
    .ad-data th, .ad-data td { padding: 6px 4px; text-align: left; border-bottom: 1px solid #F3F8F5; }
    html:not(.dark) body.adm main .ad .ad-data thead th { background: transparent !important; color: #557466 !important; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
    .ad-data td:last-child, .ad-data th:last-child { text-align: right; }

    /* Avis */
    .ad-review { display: flex; flex-direction: column; gap: 4px; padding: 13px 20px; border-bottom: 1px solid #F3F8F5; color: #0E3B2A !important; }
    .ad-review:last-child { border-bottom: 0; }
    .ad-review:hover { background: #FFFFFF; }
    .ad-review__head { display: flex; align-items: center; gap: 6px; font-size: 13.5px; min-width: 0; }
    .ad-review__to { color: #B85600; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ad-review__stars { display: inline-flex; align-items: center; gap: 2px; font-size: 11px; color: #8AA99B; }
    .ad-review__stars i.is-on { color: #EB6E00; }
    .ad-review__stars small { margin-left: 6px; font-size: 11.5px; color: #557466; }
    .ad-review__text { font-size: 12.5px; color: #24503D; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .ad-empty { margin: 0; padding: 28px 20px; display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; font-size: 13px; color: #557466; }
    .ad-empty i { font-size: 22px; color: #8AA99B; }
    .ad-empty i.is-ok { color: #00874F; }
    .ad-empty.is-small { padding: 10px 0; align-items: flex-start; }

    /* L'audience (partials.admin-audience) suit la même charte que les cartes. */
    .ad .aud { margin-bottom: 0 !important; border-radius: 18px; border-color: #D8E9E0; }

    @media (max-width: 1280px) { .ad-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } .ad-kpi.is-revenue { grid-column: span 3; } }
    @media (max-width: 1100px) { .ad-grid--2-1 { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 900px) { .ad-todo { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 640px) {
        .ad-hero__in { padding: 14px 14px 16px; gap: 12px; }
        .ad-hero__date { font-size: 10.5px; }
        .ad-hero__title { font-size: 20px; margin-top: 2px; }
        .ad-hero__sub { font-size: 12.5px; margin-top: 4px; }
        .ad-hero__actions { gap: 6px; }
        .ad-hero__actions { width: 100%; }
        .ad-btn { flex: 1 1 calc(50% - 3px); justify-content: center; height: 34px; padding: 0 10px; font-size: 12px; white-space: nowrap; }
        .ad-btn.is-primary { flex-basis: 100%; }
        .ad-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ad-kpi.is-revenue { grid-column: span 2; }
        .ad-kpi__value { font-size: 24px; }
        .ad-card { padding: 16px; }
        .ad-card__head.is-bar { padding: 13px 16px; }
        .ad-feed__row, .ad-review { padding-left: 16px; padding-right: 16px; }
        .ad-table th, .ad-table td { padding-left: 14px; padding-right: 14px; }
        .ad-table .is-hide-sm { display: none; }
        .ad-feed__time { display: none; }
    }
    @media (max-width: 900px) { .ad-table .is-hide-md { display: none; } }
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
{{--
    Graphiques du tableau de bord (Chart.js), charte du site, aplats uniquement :
      - marques fines (traits 2 px, colonnes ≤ 24 px arrondies en haut, carrées sur la ligne de base),
        aplat de surface à 10 % sous les courbes, grille horizontale fine et discrète, pas de grille verticale ;
      - une seule échelle par graphique ; étiquettes choisies (valeur finale, valeur maximale), jamais partout ;
      - info-bulle HTML (valeur en gras, libellé discret, repère de couleur en trait) + ligne verticale qui suit
        le pointeur sur les courbes ; chaque graphique a sa vue « tableau » dans la page.
    chartPalette() / baseChartDefaults() servent aussi au graphique d'audience (partials.admin-audience).
--}}
const AD = {
    ink: '#0E3B2A', muted: '#557466', grid: '#D8E9E0', surface: '#ffffff', line: '#D8E9E0',
    orange: '#EB6E00', orangeHover: '#B85600', orangeWash: 'rgba(235,110,0, 0.10)',
    forest: '#00874F', forestHover: '#00874F',
};
Chart.defaults.font.family = "'Montserrat', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif";
Chart.defaults.color = AD.muted;

const adNumber = new Intl.NumberFormat('fr-FR');
const adCompact = new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 });

function chartPalette() {
    return { surface: AD.surface, grid: AD.grid, tick: AD.muted, tickStrong: AD.ink, tooltipTitle: AD.ink, tooltipBody: AD.muted, tooltipBorder: AD.line };
}

/* Info-bulle HTML : titre discret, puis une ligne par série (trait de couleur, valeur en gras). */
function adExternalTooltip(context) {
    const { chart, tooltip } = context;
    const host = chart.canvas.parentNode;
    let el = host.querySelector('.ad-tip');
    if (!el) {
        el = document.createElement('div');
        el.className = 'ad-tip';
        el.setAttribute('role', 'status');
        host.appendChild(el);
    }
    if (tooltip.opacity === 0) { el.style.opacity = '0'; return; }

    el.replaceChildren();
    const title = document.createElement('p');
    title.className = 'ad-tip__title';
    title.textContent = (tooltip.title || []).join(' ');
    el.appendChild(title);
    tooltip.dataPoints.forEach((dp, i) => {
        const row = document.createElement('p');
        row.className = 'ad-tip__row';
        const key = document.createElement('span');
        key.className = 'ad-tip__key';
        const ds = dp.dataset;
        key.style.background = ds.borderColor && ds.borderWidth ? ds.borderColor : (Array.isArray(ds.backgroundColor) ? ds.backgroundColor[dp.dataIndex] : ds.backgroundColor);
        const val = document.createElement('strong');
        val.textContent = (tooltip.body[i]?.lines || []).join(' ');
        row.append(key, val);
        el.appendChild(row);
    });

    const x = tooltip.caretX, y = tooltip.caretY;
    const w = el.offsetWidth || 120;
    const left = Math.min(Math.max(x - w / 2, 0), chart.width - w);
    el.style.left = left + 'px';
    el.style.top = Math.max(y - el.offsetHeight - 14, 0) + 'px';
    el.style.opacity = '1';
}

/* Ligne verticale qui suit le pointeur (courbes uniquement). */
const adCrosshair = {
    id: 'adCrosshair',
    afterDatasetsDraw(chart) {
        if (chart.config.type !== 'line') return;
        const active = chart.tooltip?.getActiveElements?.() || [];
        if (!active.length) return;
        const x = active[0].element.x, { top, bottom } = chart.chartArea, c = chart.ctx;
        c.save(); c.beginPath(); c.moveTo(x, top); c.lineTo(x, bottom);
        c.lineWidth = 1; c.strokeStyle = 'rgba(14,59,42, 0.18)'; c.stroke(); c.restore();
    },
};

/* Étiquette sélective : valeur finale (courbe) ou valeur maximale (colonnes), en couleur de texte. */
const adSelectiveLabel = {
    id: 'adSelectiveLabel',
    afterDatasetsDraw(chart, args, opts) {
        if (!opts || !opts.mode) return;
        const ds = chart.data.datasets[0];
        const meta = chart.getDatasetMeta(0);
        if (!ds || !meta?.data?.length) return;
        const values = ds.data.map(Number);
        const max = Math.max(...values);
        if (!(max > 0)) return;
        const idx = opts.mode === 'last' ? values.length - 1 : values.indexOf(max);
        const point = meta.data[idx];
        if (!point || !(values[idx] > 0)) return;
        const c = chart.ctx;
        c.save();
        c.font = "700 12px 'Montserrat', sans-serif";
        c.fillStyle = AD.ink;
        c.textAlign = opts.mode === 'last' ? 'right' : 'center';
        c.textBaseline = 'bottom';
        c.fillText(opts.format ? opts.format(values[idx]) : adNumber.format(values[idx]), point.x + (opts.mode === 'last' ? 4 : 0), point.y - 8);
        c.restore();
    },
};
Chart.register(adCrosshair, adSelectiveLabel);

function baseChartDefaults(p) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 600, easing: 'easeOutQuart' },
        interaction: { mode: 'index', intersect: false },
        layout: { padding: { top: 24, right: 8 } },
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false, external: adExternalTooltip },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: p.tick, font: { size: 11 }, maxRotation: 0, autoSkip: true, autoSkipPadding: 14 },
                border: { display: false },
            },
            y: {
                beginAtZero: true,
                grid: { color: p.grid, lineWidth: 1, drawTicks: false },
                ticks: { color: p.tick, font: { size: 11 }, padding: 8, maxTicksLimit: 5, callback: (v) => adCompact.format(v) },
                border: { display: false },
            },
        },
    };
}

const revenueLabels = @json($revenue_by_month['labels']);
const revenueValues = @json($revenue_by_month['values']);
const signupsLabels = @json($signups_by_day['labels']);
const signupsValues = @json($signups_by_day['values']);
const dashboardCharts = {};

function buildRevenueChart() {
    const canvas = document.getElementById('chartRevenue');
    if (!canvas) return;
    dashboardCharts.revenue?.destroy();
    const p = chartPalette();
    const base = baseChartDefaults(p);
    const fcfa = (v) => adNumber.format(v) + ' FCFA';

    dashboardCharts.revenue = new Chart(canvas, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                data: revenueValues,
                borderColor: AD.orange,
                backgroundColor: AD.orangeWash,
                borderWidth: 2,
                borderCapStyle: 'round',
                borderJoinStyle: 'round',
                fill: true,
                tension: 0.35,
                pointRadius: (ctx) => ctx.dataIndex === revenueValues.length - 1 ? 4 : 0,
                pointHoverRadius: 5,
                pointBackgroundColor: AD.orange,
                pointBorderColor: AD.surface,
                pointBorderWidth: 2,
                pointHitRadius: 14,
            }],
        },
        options: {
            ...base,
            plugins: {
                ...base.plugins,
                tooltip: { ...base.plugins.tooltip, callbacks: { label: (c) => fcfa(c.parsed.y) } },
                adSelectiveLabel: { mode: 'last', format: (v) => adCompact.format(v) + ' FCFA' },
            },
        },
    });
}

function buildSignupsChart() {
    const canvas = document.getElementById('chartSignups');
    if (!canvas) return;
    dashboardCharts.signups?.destroy();
    const p = chartPalette();
    const base = baseChartDefaults(p);

    dashboardCharts.signups = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: signupsLabels,
            datasets: [{
                data: signupsValues,
                backgroundColor: AD.forest,
                hoverBackgroundColor: AD.forestHover,
                borderRadius: { topLeft: 4, topRight: 4 },
                borderSkipped: 'bottom',
                maxBarThickness: 24,
                categoryPercentage: 0.8,
                barPercentage: 0.9,
            }],
        },
        options: {
            ...base,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                ...base.plugins,
                tooltip: { ...base.plugins.tooltip, callbacks: { label: (c) => adNumber.format(c.parsed.y) + (c.parsed.y > 1 ? ' inscriptions' : ' inscription') } },
                adSelectiveLabel: { mode: 'max' },
            },
            scales: { ...base.scales, y: { ...base.scales.y, ticks: { ...base.scales.y.ticks, precision: 0 } } },
        },
    });
}

function buildAllDashboardCharts() {
    buildRevenueChart();
    buildSignupsChart();
}

buildAllDashboardCharts();
</script>
@endpush


@push('scripts')
<script>
/* Audience du site : graphique par période (aujourd'hui / semaine / mois / année) et chiffres actualisés
   toutes les 30 s depuis admin.dashboard.audience (sans recharger la page). */
(function () {
    var section = document.querySelector('.aud');
    var canvas = document.getElementById('chartAudience');
    if (!section || !canvas || typeof Chart === 'undefined') return;
    var data = @json($audience);
    var range = 'day';
    var chart = null;
    var fmt = new Intl.NumberFormat('fr-FR');

    function draw() {
        var s = data.series[range];
        var p = chartPalette();
        var base = baseChartDefaults(p);
        var isLine = range === 'month';
        if (chart) chart.destroy();
        chart = new Chart(canvas, {
            type: isLine ? 'line' : 'bar',
            data: {
                labels: s.labels,
                datasets: [{
                    data: s.values,
                    backgroundColor: isLine ? 'rgba(235,110,0,0.10)' : '#EB6E00',
                    hoverBackgroundColor: '#B85600',
                    borderColor: '#EB6E00',
                    borderWidth: isLine ? 2 : 0,
                    borderRadius: isLine ? 0 : { topLeft: 4, topRight: 4 },
                    borderSkipped: 'bottom',
                    maxBarThickness: 24,
                    categoryPercentage: 0.8,
                    barPercentage: 0.9,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#EB6E00',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointHitRadius: 14,
                    fill: isLine,
                    tension: 0.35,
                }],
            },
            options: Object.assign({}, base, {
                plugins: Object.assign({}, base.plugins, {
                    tooltip: Object.assign({}, base.plugins.tooltip, {
                        callbacks: { label: function (c) { return fmt.format(c.parsed.y) + (range === 'year' ? ' visites' : ' visiteurs'); } },
                    }),
                    adSelectiveLabel: { mode: 'max' },
                }),
                scales: Object.assign({}, base.scales, {
                    y: Object.assign({}, base.scales.y, { ticks: Object.assign({}, base.scales.y.ticks, { precision: 0 }) }),
                    x: Object.assign({}, base.scales.x, { ticks: Object.assign({}, base.scales.x.ticks, { autoSkip: true, maxRotation: 0 }) }),
                }),
            }),
        });
    }

    section.querySelectorAll('[data-aud-range]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            range = tab.dataset.audRange;
            section.querySelectorAll('[data-aud-range]').forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('is-on', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            section.querySelector('[data-aud-caption]').textContent = tab.dataset.caption;
            draw();
        });
    });

    function refresh() {
        if (document.hidden) return;
        fetch(section.dataset.audUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (fresh) {
                if (!fresh) return;
                data = fresh;
                ['online', 'today', 'week', 'month', 'year'].forEach(function (k) {
                    var el = section.querySelector('[data-aud="' + k + '"]');
                    if (el) el.textContent = fmt.format(fresh[k]);
                });
                draw();
            })
            .catch(function () {});
    }

    draw();
    setInterval(refresh, 30000);
})();
</script>
@endpush
