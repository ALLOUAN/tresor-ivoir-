{{--
    Bannière publicitaire de l'accueil (gérée dans Administration › Bannières publicitaires).
    Paramètre : $placement (after_annuaire | after_cultures). N'affiche rien si aucune bannière active.
    Charte « Le Joyau » : fond vert très clair, coin coupé, titre gras + maigre, accroche orange,
    bouton rond orange ; aplats, aucun dégradé, ombre au survol seulement.
--}}
@php $promo = \App\Models\HomePromoBanner::forPlacement($placement ?? ''); @endphp
@if($promo)
@once
<style>
    .tpb-wrap { max-width: 80rem; margin: 0 auto; padding: 8px 1rem 24px; }
    @media (min-width: 640px) { .tpb-wrap { padding: 8px 1.5rem 56px; } }
    .tpb { position: relative; display: grid; grid-template-columns: minmax(0, 300px) minmax(0, 1fr) auto; align-items: center; gap: 28px; min-height: 200px; padding: 20px 40px 20px 28px; border-radius: 24px; background: #EAF4EE; color: #0E3B2A; text-decoration: none;
        clip-path: polygon(0 0, calc(100% - 64px) 0, 100% 64px, 100% 100%, 0 100%); transition: background-color .2s ease; }
    .tpb:hover { background: #E1EFE7; }
    .tpb:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
    .tpb__media { display: flex; align-items: center; justify-content: center; height: 180px; }
    .tpb__media img { max-width: 100%; max-height: 210px; object-fit: contain; margin: -24px 0; transition: transform .3s ease; }
    .tpb:hover .tpb__media img { transform: translateY(-4px) scale(1.02); }
    .tpb__media { position: relative; }
    .tpb__slides { position: relative; display: block; width: 100%; height: 100%; }
    .tpb__slides img { position: absolute; inset: 0; width: 100%; height: 100%; max-height: none; margin: 0; object-fit: contain; opacity: 0; transition: opacity .6s ease, transform .3s ease; }
    .tpb__slides img.is-on { opacity: 1; }
    .tpb__dots { position: absolute; left: 50%; bottom: -14px; transform: translateX(-50%); display: flex; gap: 6px; }
    .tpb__dots i { width: 7px; height: 7px; border-radius: 999px; background: rgba(0,135,79,.25); transition: width .25s ease, background-color .25s ease; }
    .tpb__dots i.is-on { width: 20px; background: #EB6E00; }
    .tpb__icon { width: 132px; height: 132px; border-radius: 999px; display: flex; align-items: center; justify-content: center; background: #fff; box-shadow: 0 0 0 10px rgba(0,135,79,.08); color: #00874F; font-size: 52px; }
    .tpb__kicker { margin: 0; font-size: 14px; font-weight: 600; color: #24503D; }
    .tpb__title { margin: 6px 0 0; font-size: clamp(22px, 2.3vw, 32px); line-height: 1.12; text-transform: uppercase; letter-spacing: -.005em; color: #0E3B2A; }
    .tpb__title b { font-weight: 800; }
    .tpb__title span { font-weight: 500; }
    .tpb__accent { margin: 6px 0 0; font-size: clamp(20px, 2vw, 28px); font-weight: 800; line-height: 1.15; color: #EB6E00; }
    .tpb__cta { flex: none; width: 112px; height: 112px; border-radius: 999px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 10px; background: #EB6E00; color: #fff; text-align: center; font-size: 12px; font-weight: 800; line-height: 1.15; text-transform: uppercase; letter-spacing: .02em; transition: background-color .15s ease, transform .2s ease; }
    .tpb__cta i { font-size: 14px; transform: rotate(-45deg); transition: transform .2s ease; }
    .tpb:hover .tpb__cta { background: #B85600; transform: scale(1.04); box-shadow: 0 14px 26px -16px rgba(184,86,0,.7); }
    .tpb:hover .tpb__cta i { transform: translate(2px, -2px) rotate(-45deg); }
    @media (max-width: 900px) {
        .tpb { grid-template-columns: minmax(0, 200px) minmax(0, 1fr); padding: 20px 28px; }
        .tpb__cta { grid-column: 1 / -1; justify-self: start; flex-direction: row; width: auto; height: 52px; padding: 0 24px; border-radius: 999px; font-size: 13.5px; }
        .tpb__media { height: 150px; }
    }
    @media (max-width: 560px) {
        .tpb { grid-template-columns: minmax(0, 1fr); gap: 14px; padding: 22px 20px 20px; text-align: center; clip-path: polygon(0 0, calc(100% - 40px) 0, 100% 40px, 100% 100%, 0 100%); }
        .tpb__media { height: 140px; }
        .tpb__media img { max-height: 160px; margin: 0; }
        .tpb__icon { width: 104px; height: 104px; font-size: 40px; }
        .tpb__cta { justify-self: stretch; justify-content: center; }
    }
    @media (prefers-reduced-motion: reduce) { .tpb, .tpb__media img, .tpb__cta, .tpb__cta i { transition: none; } }
</style>
<script>
// Défilement des visuels des bannières : un fondu toutes les 3 s, arrêté si l'onglet est masqué
// ou si l'utilisateur a demandé moins d'animations.
document.addEventListener('DOMContentLoaded', function () {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.querySelectorAll('[data-tpb-slides]').forEach(function (box) {
        var imgs = box.querySelectorAll('img'), dots = box.parentNode.querySelectorAll('.tpb__dots i'), i = 0;
        if (imgs.length < 2) return;
        setInterval(function () {
            if (document.hidden) return;
            imgs[i].classList.remove('is-on'); if (dots[i]) dots[i].classList.remove('is-on');
            i = (i + 1) % imgs.length;
            imgs[i].classList.add('is-on'); if (dots[i]) dots[i].classList.add('is-on');
        }, 3000);
    });
});
</script>
@endonce
<div class="tpb-wrap">
    <a href="{{ $promo->link_url }}" class="tpb" @if($promo->open_new_tab) target="_blank" rel="noopener sponsored" @endif>
        <span class="tpb__media" aria-hidden="true">
            @php $promoImages = $promo->imageList(); @endphp
            @if(count($promoImages) > 1)
                {{-- Plusieurs visuels : fondu enchaîné toutes les 3 s (pause si l'utilisateur limite les animations). --}}
                <span class="tpb__slides" data-tpb-slides>
                    @foreach($promoImages as $i => $url)
                        <img src="{{ $url }}" alt="" class="{{ $i === 0 ? 'is-on' : '' }}" loading="lazy" decoding="async">
                    @endforeach
                </span>
                <span class="tpb__dots">@foreach($promoImages as $i => $url)<i class="{{ $i === 0 ? 'is-on' : '' }}"></i>@endforeach</span>
            @elseif($promoImages)
                <img src="{{ $promoImages[0] }}" alt="" loading="lazy" decoding="async">
            @else
                <span class="tpb__icon"><i class="fas {{ $promo->icon ?: 'fa-rectangle-ad' }}"></i></span>
            @endif
        </span>
        <span class="tpb__text">
            @if($promo->kicker)<p class="tpb__kicker">{{ $promo->kicker }}</p>@endif
            <p class="tpb__title"><b>{{ $promo->title_strong }}</b> @if($promo->title_rest)<span>{{ $promo->title_rest }}</span>@endif</p>
            @if($promo->accent)<p class="tpb__accent">{{ $promo->accent }}</p>@endif
        </span>
        <span class="tpb__cta"><i class="fas fa-arrow-right" aria-hidden="true"></i>{{ $promo->cta_label }}</span>
    </a>
</div>
@endif
