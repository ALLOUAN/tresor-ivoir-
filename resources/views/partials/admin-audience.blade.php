{{-- ── Audience du site (App\Services\SiteAudience) : en ligne, aujourd'hui, 7 jours, mois, année ── --}}
<section class="aud mb-6" aria-labelledby="aud-title" data-aud-url="{{ route('admin.dashboard.audience') }}">
    <div class="aud-head">
        <div>
            <h2 id="aud-title" class="aud-title">{{ __('Audience du site') }}</h2>
            <p class="aud-sub">{{ __('Visiteurs uniques, sans cookie (robots et back-office exclus). Actualisé toutes les 30 secondes.') }}</p>
        </div>
        <span class="aud-live"><span class="aud-live__dot" aria-hidden="true"></span>{{ __('En direct') }}</span>
    </div>

    <div class="aud-kpis">
        <div class="aud-kpi is-online">
            <span class="aud-kpi__label"><span class="aud-pulse" aria-hidden="true"></span>{{ __('En ligne maintenant') }}</span>
            <strong class="aud-kpi__value" data-aud="online">{{ number_format($audience['online'], 0, ',', ' ') }}</strong>
            <small>{{ __('actifs ces :n dernières minutes', ['n' => \App\Services\SiteAudience::ONLINE_MINUTES]) }}</small>
        </div>
        <div class="aud-kpi">
            <span class="aud-kpi__label">{{ __('Aujourd\'hui') }}</span>
            <strong class="aud-kpi__value" data-aud="today">{{ number_format($audience['today'], 0, ',', ' ') }}</strong>
            <small>{{ __('visiteurs') }}</small>
        </div>
        <div class="aud-kpi">
            <span class="aud-kpi__label">{{ __('7 derniers jours') }}</span>
            <strong class="aud-kpi__value" data-aud="week">{{ number_format($audience['week'], 0, ',', ' ') }}</strong>
            <small>{{ __('visites') }}</small>
        </div>
        <div class="aud-kpi">
            <span class="aud-kpi__label">{{ __('Ce mois-ci') }}</span>
            <strong class="aud-kpi__value" data-aud="month">{{ number_format($audience['month'], 0, ',', ' ') }}</strong>
            <small>{{ __('visites') }}</small>
        </div>
        <div class="aud-kpi">
            <span class="aud-kpi__label">{{ __('Cette année') }}</span>
            <strong class="aud-kpi__value" data-aud="year">{{ number_format($audience['year'], 0, ',', ' ') }}</strong>
            <small>{{ __('visites') }}</small>
        </div>
    </div>

    <div class="aud-chart">
        <div class="aud-chart__head">
            <p class="aud-chart__caption" data-aud-caption>{{ __('Nouveaux visiteurs par heure, aujourd\'hui') }}</p>
            <div class="aud-tabs" role="tablist" aria-label="{{ __('Période') }}">
                <button type="button" role="tab" class="aud-tab is-on" aria-selected="true" data-aud-range="day" data-caption="{{ __('Nouveaux visiteurs par heure, aujourd\'hui') }}">{{ __('Aujourd\'hui') }}</button>
                <button type="button" role="tab" class="aud-tab" aria-selected="false" data-aud-range="week" data-caption="{{ __('Visiteurs par jour, 7 derniers jours') }}">{{ __('Semaine') }}</button>
                <button type="button" role="tab" class="aud-tab" aria-selected="false" data-aud-range="month" data-caption="{{ __('Visiteurs par jour, 30 derniers jours') }}">{{ __('Mois') }}</button>
                <button type="button" role="tab" class="aud-tab" aria-selected="false" data-aud-range="year" data-caption="{{ __('Visites par mois, 12 derniers mois') }}">{{ __('Année') }}</button>
            </div>
        </div>
        <div class="aud-chart__canvas"><canvas id="chartAudience" aria-label="{{ __('Graphique de fréquentation') }}" role="img"></canvas></div>
    </div>
</section>
<style>
    .aud { padding: 20px; border-radius: 16px; background: #ffffff; border: 1px solid #D8E9E0; color: #0E3B2A; }
    .aud-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
    .aud-title { margin: 0; font-size: 15px; font-weight: 700; color: #0E3B2A; }
    .aud-sub { margin: 3px 0 0; font-size: 12px; color: #557466; }
    .aud-live { display: inline-flex; align-items: center; gap: 7px; flex-shrink: 0; height: 28px; padding: 0 11px; border-radius: 999px; background: #F3F8F5; color: #00874F; font-size: 12px; font-weight: 700; }
    .aud-live__dot { width: 7px; height: 7px; border-radius: 999px; background: #00874F; }
    .aud-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
    .aud-kpi { display: flex; flex-direction: column; gap: 4px; padding: 14px 16px; border-radius: 14px; background: #F3F8F5; border: 1px solid #D8E9E0; }
    .aud-kpi.is-online { background: #00874F; border-color: #00874F; color: #ffffff; }
    .aud-kpi__label { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #24503D; }
    .aud-kpi.is-online .aud-kpi__label { color: rgb(255 255 255 / .8); }
    .aud-kpi__value { font-size: 28px; font-weight: 800; line-height: 1.1; color: #0E3B2A; letter-spacing: -.02em; }
    .aud-kpi.is-online .aud-kpi__value { color: #ffffff; }
    .aud-kpi small { font-size: 11.5px; color: #557466; }
    .aud-kpi.is-online small { color: rgb(255 255 255 / .65); }
    .aud-pulse { position: relative; width: 9px; height: 9px; border-radius: 999px; background: #00874F; }
    .aud-pulse::after { content: ''; position: absolute; inset: -4px; border-radius: 999px; border: 2px solid #00874F; opacity: 0; animation: aud-ping 1.8s ease-out infinite; }
    @keyframes aud-ping { 0% { transform: scale(.6); opacity: .8; } 100% { transform: scale(1.6); opacity: 0; } }
    .aud-chart { margin-top: 16px; padding: 14px 14px 10px; border-radius: 14px; border: 1px solid #D8E9E0; }
    .aud-chart__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; }
    .aud-chart__caption { margin: 0; font-size: 12.5px; font-weight: 600; color: #24503D; }
    .aud-tabs { display: inline-flex; gap: 4px; padding: 4px; border-radius: 999px; background: #F3F8F5; }
    .aud-tab { height: 30px; padding: 0 14px; border: 0; border-radius: 999px; background: transparent; color: #24503D; font-size: 12.5px; font-weight: 600; cursor: pointer; }
    .aud-tab:hover { color: #B85600; }
    .aud-tab.is-on { background: #EB6E00; color: #ffffff; }
    .aud-tab:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
    .aud-chart__canvas { position: relative; height: 260px; }
    @media (max-width: 1100px) { .aud-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } .aud-kpi.is-online { grid-column: span 3; } }
    @media (max-width: 640px) { .aud-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .aud-kpi.is-online { grid-column: span 2; } .aud-head { flex-direction: column; } .aud-chart__canvas { height: 220px; } .aud-tabs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; } .aud-tab { padding: 0 2px; font-size: 11px; letter-spacing: -.01em; white-space: nowrap; } .aud-tabs { gap: 2px; padding: 3px; } }
    @media (prefers-reduced-motion: reduce) { .aud-pulse::after { animation: none; } }
</style>
