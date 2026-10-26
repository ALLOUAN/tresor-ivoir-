{{--
    « Les Astuces de Mian » : fenêtre de présentation, affichée automatiquement une seule fois par navigateur
    (première visite), après un court délai, et jamais sur les pages de la rubrique elle-même.
    N'apparaît que si au moins une BD est publiée. Mémorisation : localStorage + cookie (1 an).
--}}
@php
    $mianComics = request()->routeIs('comics.*') || request()->cookie('tiv_mian_seen')
        ? collect()
        // Cache en tableau simple : la configuration du cache refuse les objets (cache.serializable_classes).
        : collect(\Illuminate\Support\Facades\Cache::remember('mian_welcome_v2', 600, function () {
            if (! \Illuminate\Support\Facades\Schema::hasTable('comics')) {
                return [];
            }

            return \App\Models\Comic::published()->ordered()->take(3)->get()
                ->map(fn ($c) => ['title' => $c->title, 'url' => route('comics.show', $c), 'cover' => $c->coverUrl()])->all();
        }));
@endphp
@if($mianComics->isNotEmpty())
<div id="mian-welcome" class="mw" hidden role="dialog" aria-modal="true" aria-labelledby="mw-title" aria-describedby="mw-text">
    <div class="mw-backdrop" data-mw-close></div>
    <style>
        /* Fenêtre « Astuces de Mian » : panneau vert à motifs BD + éventail de couvertures, texte et actions à droite. Aplats. */
        .mw { position: fixed; inset: 0; z-index: 70; display: flex; align-items: center; justify-content: center; padding: 16px; font-family: 'Montserrat', sans-serif; }
        .mw[hidden] { display: none; }
        .mw-backdrop { position: absolute; inset: 0; background: rgba(14,59,42, .55); }
        .mw-card { position: relative; width: 100%; max-width: 780px; max-height: 92vh; overflow-y: auto; display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); border-radius: 28px; background: #fff; color: #0E3B2A; animation: mw-in .28s ease-out; }
        @keyframes mw-in { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: none; } }
        .mw-art { position: relative; overflow: hidden; min-height: 340px; background: #00874F; }
        .mw-art .ivm-g { opacity: .14 !important; }
        .mw-art .ivm-g > * { stroke: #F7C396 !important; }
        .mw-fan { position: absolute; inset: 0; z-index: 1; }
        .mw-fan a { position: absolute; top: 50%; left: 50%; width: 46%; aspect-ratio: 3 / 4; border-radius: 14px; overflow: hidden; background: #D8E9E0; border: 4px solid #fff; transition: transform .25s ease; }
        .mw-fan a img { width: 100%; height: 100%; object-fit: cover; }
        .mw-fan a:nth-child(1) { transform: translate(-50%, -54%) rotate(-2deg); z-index: 3; }
        .mw-fan a:nth-child(2) { transform: translate(-98%, -44%) rotate(-12deg); z-index: 2; }
        .mw-fan a:nth-child(3) { transform: translate(-2%, -44%) rotate(11deg); z-index: 1; }
        .mw-fan a:nth-child(1):hover { transform: translate(-50%, -58%) rotate(0); }
        .mw-fan a:only-child { width: 52%; }
        .mw-new { position: absolute; left: 16px; top: 16px; z-index: 2; display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .mw-body { display: flex; flex-direction: column; padding: 30px 30px 26px; }
        .mw-close { position: absolute; right: 14px; top: 14px; z-index: 3; width: 40px; height: 40px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; color: #0E3B2A; font-size: 20px; line-height: 1; cursor: pointer; }
        .mw-close:hover { border-color: #EB6E00; color: #B85600; }
        .mw-kick { margin: 0; font-size: 11.5px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #EB6E00; }
        .mw-title { margin: 8px 40px 0 0; font-family: 'Montserrat', serif; font-size: clamp(26px, 3.2vw, 34px); font-weight: 700; line-height: 1.08; color: #0E3B2A; }
        .mw-title em { font-style: normal; color: #EB6E00; }
        .mw-text { margin: 12px 0 0; font-size: 15.5px; line-height: 1.6; color: #24503D; }
        .mw-cats { display: flex; flex-wrap: wrap; gap: 6px; margin: 16px 0 0; padding: 0; list-style: none; }
        .mw-cats li { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; font-size: 12.5px; font-weight: 700; }
        .mw-cats li:nth-child(1) { background: rgba(0,135,79,.12); color: #00874F; }
        .mw-cats li:nth-child(2) { background: rgba(235,110,0,.14); color: #B85600; }
        .mw-cats li:nth-child(3) { background: rgba(0,135,79,.12); color: #00874F; }
        .mw-cats .ico { width: 14px; height: 14px; }
        .mw-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: auto; padding-top: 22px; }
        .mw-go { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 50px; padding: 0 24px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15px; font-weight: 800; text-decoration: none; }
        .mw-go:hover { background: #B85600; }
        .mw-go .ico { width: 18px; height: 18px; }
        .mw-later { height: 50px; padding: 0 18px; border: 0; border-radius: 999px; background: transparent; color: #557466; font: inherit; font-size: 14.5px; font-weight: 700; cursor: pointer; }
        .mw-later:hover { background: #F3F8F5; color: #0E3B2A; }
        .mw-note { margin: 10px 0 0; font-size: 12px; color: #557466; }
        .mw button:focus-visible, .mw a:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        @media (max-width: 640px) {
            .mw { padding: 0; align-items: flex-end; }
            .mw-card { grid-template-columns: minmax(0, 1fr); max-height: 94vh; border-radius: 24px 24px 0 0; animation-name: mw-up; }
            @keyframes mw-up { from { transform: translateY(40px); opacity: 0; } to { transform: none; opacity: 1; } }
            .mw-art { min-height: 0; height: 190px; }
            .mw-fan a { width: 30%; }
            .mw-fan a:only-child { width: 34%; }
            .mw-fan a:nth-child(2) { transform: translate(-125%, -44%) rotate(-12deg); }
            .mw-fan a:nth-child(3) { transform: translate(25%, -44%) rotate(11deg); }
            .mw-close { background: rgba(255,255,255,.95); border-color: transparent; }
            .mw-body { padding: 18px 18px 20px; }
            .mw-title { font-size: 24px; margin-right: 0; }
            .mw-text { font-size: 14.5px; }
            .mw-actions { padding-top: 16px; }
            .mw-go { flex: 1; height: 48px; }
            .mw-later { flex: none; height: 48px; }
        }
        @media (prefers-reduced-motion: reduce) { .mw-card, .mw-fan a { animation: none; transition: none; } }
    </style>
    <div class="mw-card">
        <button type="button" class="mw-close" data-mw-close aria-label="{{ __('Fermer') }}">×</button>
        <div class="mw-art">
            @include('partials.ivorian-motifs', ['culture' => 'bd', 'band' => false])
            <span class="mw-new">{{ __('Nouveau') }}</span>
            <div class="mw-fan">
                @foreach($mianComics as $mc)
                    <a href="{{ $mc['url'] }}" data-mw-go tabindex="-1" aria-hidden="true">@if($mc['cover'])<img src="{{ $mc['cover'] }}" alt="" loading="lazy" decoding="async">@endif</a>
                @endforeach
            </div>
        </div>
        <div class="mw-body">
            <p class="mw-kick">{{ __('Bandes dessinées') }}</p>
            <h2 class="mw-title" id="mw-title">{{ __('Les Astuces') }} <em>{{ __('de Mian') }}</em></h2>
            <p class="mw-text" id="mw-text">{{ __('Suivez Mian dans de courtes histoires en images pour découvrir la Côte d\'Ivoire autrement : bons plans, traditions et sourires garantis.') }}</p>
            <ul class="mw-cats">
                <li><x-icon name="book" />{{ __('Éducatif') }}</li>
                <li><x-icon name="star-shine" />{{ __('Ludique') }}</li>
                <li><x-icon name="smile-circle" />{{ __('Divertissant') }}</li>
            </ul>
            <div class="mw-actions">
                <a href="{{ route('comics.index') }}" class="mw-go" data-mw-go><x-icon name="book" />{{ __('Découvrir les BD') }}</a>
                <button type="button" class="mw-later" data-mw-close>{{ __('Plus tard') }}</button>
            </div>
            <p class="mw-note">{{ __('Retrouvez-les à tout moment dans le menu Médiathèque.') }}</p>
        </div>
    </div>
</div>
<script>
(function () {
    var box = document.getElementById('mian-welcome');
    if (!box) return;
    var KEY = 'tiv_mian_seen';
    function seen() { try { return localStorage.getItem(KEY) === '1'; } catch (e) { return false; } }
    function remember() {
        try { localStorage.setItem(KEY, '1'); } catch (e) {}
        document.cookie = KEY + '=1; max-age=31536000; path=/; SameSite=Lax';
    }
    if (seen()) { remember(); return; }

    var lastFocus = null;
    function close() {
        box.hidden = true;
        document.body.style.overflow = '';
        document.removeEventListener('keydown', onKey);
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    function onKey(e) {
        if (e.key === 'Escape') { close(); return; }
        if (e.key !== 'Tab') return;
        var f = box.querySelectorAll('a[href]:not([tabindex="-1"]), button');
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
        else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
    }
    function open() {
        // Jamais par-dessus une autre fenêtre ouverte (contact, menu mobile…) ni pendant une saisie.
        var contact = document.getElementById('contact-modal');
        if ((contact && !contact.classList.contains('hidden')) || /input|textarea|select/i.test(document.activeElement && document.activeElement.tagName || '')) {
            setTimeout(open, 4000);
            return;
        }
        remember();
        lastFocus = document.activeElement;
        box.hidden = false;
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', onKey);
        var go = box.querySelector('.mw-go'); if (go) go.focus({ preventScroll: true });
    }
    box.querySelectorAll('[data-mw-close]').forEach(function (b) { b.addEventListener('click', close); });
    // Court délai : on laisse d'abord le visiteur voir la page qu'il est venu consulter.
    setTimeout(open, 3500);
})();
</script>
@endif
