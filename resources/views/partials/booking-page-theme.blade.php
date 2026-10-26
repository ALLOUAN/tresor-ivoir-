{{-- Styles des pages de réservation détaillées (circuit, activité, formule) : préfixe tp-. --}}
    <style>
        .tp-gal { display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 10px; height: clamp(280px, 46vh, 480px); }
        .tp-gal button { position: relative; padding: 0; border: 0; overflow: hidden; border-radius: 20px; background: #F3F8F5; cursor: zoom-in; }
        .tp-gal button:first-child { grid-row: span 2; border-radius: 28px; }
        .tp-gal img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
        .tp-gal button:hover img { transform: scale(1.04); }
        .tp-gal.is-1 { grid-template-columns: 1fr; } .tp-gal.is-1 button:first-child { grid-row: span 2; }
        .tp-gal.is-2 { grid-template-columns: 2fr 1fr; } .tp-gal.is-2 button:nth-child(2) { grid-row: span 2; }
        .tp-gal__more { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(14,59,42,.55); color: #fff; font-weight: 700; }
        .tp-gal__empty { height: 220px; border-radius: 28px; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 42px; }
        .tp-sec { scroll-margin-top: 100px; }
        .tp-h2 { margin: 0 0 14px; font-family: 'Montserrat', serif; font-size: clamp(24px, 1.4vw + 16px, 32px); font-weight: 700; color: #0E3B2A; }
        .tp-days { position: relative; margin: 0; padding: 0; list-style: none; }
        .tp-day { position: relative; padding: 0 0 22px 52px; }
        .tp-day::before { content: ''; position: absolute; left: 19px; top: 40px; bottom: 0; width: 2px; background: #D8E9E0; }
        .tp-day:last-child::before { display: none; }
        .tp-day__n { position: absolute; left: 0; top: 0; width: 40px; height: 40px; border-radius: 999px; background: #EB6E00; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
        .tp-day__card { padding: 14px 16px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; }
        .tp-day__title { margin: 0; font-size: 16px; font-weight: 700; color: #0E3B2A; }
        .tp-day__text { margin: 6px 0 0; font-size: 14.5px; line-height: 1.65; color: #24503D; white-space: pre-line; }
        .tp-day__tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .tp-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: #F3F8F5; color: #24503D; font-size: 12px; font-weight: 600; }
        .tp-incl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .tp-box { padding: 16px 18px; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; }
        .tp-box h3 { margin: 0 0 10px; font-size: 14px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .tp-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 7px; font-size: 14.5px; color: #24503D; }
        .tp-list i { width: 18px; }
        .tp-deps { display: flex; flex-direction: column; gap: 8px; }
        .tp-dep { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; }
        .tp-dep__date { width: 54px; text-align: center; line-height: 1.05; }
        .tp-dep__date b { display: block; font-size: 22px; font-weight: 800; color: #0E3B2A; }
        .tp-dep__date small { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #B85600; }
        .tp-dep__info { min-width: 0; font-size: 13px; color: #24503D; }
        .tp-dep__info strong { display: block; font-size: 14.5px; color: #0E3B2A; }
        .tp-dep__price { text-align: right; font-weight: 800; white-space: nowrap; }
        .tp-dep.is-full { opacity: .55; }
        .tp-badge { display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #F3F8F5; color: #24503D; }
        .tp-badge.is-ok { background: #F3F8F5; color: #00874F; } .tp-badge.is-warn { background: #F3F8F5; color: #B85600; } .tp-badge.is-off { background: #fbe9e5; color: #a3341f; }
        .tp-policy { margin: 0; padding: 0; list-style: none; display: grid; gap: 6px; }
        .tp-policy li { display: flex; justify-content: space-between; gap: 10px; padding: 10px 14px; border-radius: 14px; background: #fff; border: 1px solid #D8E9E0; font-size: 14px; }
        .tp-card { position: sticky; top: 90px; padding: 20px; border-radius: 28px; background: #FFFFFF; border: 1px solid rgba(14,59,42,.08); box-shadow: 0 20px 50px rgba(14,59,42,.1); }
        .tp-card__price { margin: 0; font-size: 13px; color: #557466; }
        .tp-card__price b { font-size: 26px; color: #0E3B2A; font-weight: 800; }
        .tp-label { display: block; margin: 14px 0 6px; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #557466; }
        .tp-select, .tp-input { width: 100%; padding: 11px 14px; border-radius: 14px; border: 1px solid rgba(14,59,42,.12); background: #fff; font-size: 14px; color: #0E3B2A; }
        .tp-select:focus, .tp-input:focus { outline: 2px solid #EB6E00; border-color: #EB6E00; }
        .tp-step { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 10px 8px 14px; border: 1px solid #D8E9E0; border-radius: 14px; background: #fff; margin-top: 6px; }
        .tp-step span { font-size: 14px; font-weight: 600; } .tp-step small { display: block; font-size: 11.5px; color: #557466; font-weight: 500; }
        .tp-step__ctl { display: inline-flex; align-items: center; gap: 6px; }
        .tp-step__ctl button { width: 34px; height: 34px; border-radius: 999px; border: 1.5px solid #D8E9E0; background: #fff; color: #EB6E00; }
        .tp-step__ctl button:hover { border-color: #EB6E00; } .tp-step__ctl button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 1px; }
        .tp-step__ctl output { min-width: 22px; text-align: center; font-weight: 800; }
        .tp-extra { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 9px 12px; margin-top: 6px; border: 1px solid #D8E9E0; border-radius: 14px; background: #fff; font-size: 13.5px; cursor: pointer; }
        .tp-extra input { width: 18px; height: 18px; accent-color: #EB6E00; }
        .tp-extra:has(input:checked) { border-color: #EB6E00; background: #F3F8F5; }
        .tp-choice { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .tp-choice label { position: relative; display: flex; flex-direction: column; gap: 2px; padding: 10px 12px; border: 1.5px solid #D8E9E0; border-radius: 14px; background: #fff; cursor: pointer; font-size: 13px; font-weight: 700; }
        .tp-choice label small { font-weight: 500; color: #557466; font-size: 11.5px; }
        .tp-choice input { position: absolute; opacity: 0; }
        .tp-choice label:has(input:checked) { border-color: #EB6E00; background: #F3F8F5; box-shadow: 0 0 0 3px rgba(235,110,0,.15); }
        .tp-choice label:has(input:focus-visible) { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .tp-choice label:has(input:disabled) { opacity: .45; cursor: not-allowed; }
        .tp-sum { margin-top: 14px; padding: 12px 14px; border-radius: 16px; background: rgba(14,59,42,.03); border: 1px solid rgba(14,59,42,.07); font-size: 13px; }
        .tp-sum p { margin: 0 0 4px; display: flex; justify-content: space-between; gap: 8px; color: #24503D; }
        .tp-sum .is-total { margin: 8px 0 0; padding-top: 8px; border-top: 1px dashed #D8E9E0; font-size: 15px; font-weight: 800; color: #0E3B2A; }
        .tp-sum .is-now { color: #EB6E00; font-weight: 800; }
        .tp-submit { width: 100%; margin-top: 14px; padding: 14px; border: 0; border-radius: 18px; background: #EB6E00; color: #fff; font-weight: 800; font-size: 15px; cursor: pointer; }
        .tp-submit:hover { background: #B85600; } .tp-submit:disabled { opacity: .5; cursor: not-allowed; }
        .tp-note { margin: 10px 0 0; font-size: 12px; color: #557466; line-height: 1.5; }
        .tp-err { margin-top: 10px; padding: 10px 12px; border-radius: 12px; background: #fbe9e5; color: #a3341f; font-size: 13px; }
        .tp-lb { position: fixed; inset: 0; z-index: 60; display: none; align-items: center; justify-content: center; background: rgba(14,59,42,.95); padding: 16px; }
        .tp-lb.is-open { display: flex; }
        .tp-lb img { max-width: 100%; max-height: 88vh; border-radius: 14px; }
        .tp-lb button { position: absolute; width: 44px; height: 44px; border-radius: 999px; border: 0; background: rgba(255,255,255,.12); color: #fff; }
        @media (max-width: 1023px) { .tp-card { position: static; } }
        @media (max-width: 639px) {
            .tp-gal { grid-template-columns: 1fr 1fr; grid-template-rows: 220px 110px; height: auto; }
            .tp-gal button:first-child { grid-column: span 2; grid-row: auto; }
            .tp-gal button:nth-child(n+4) { display: none; }
            .tp-incl { grid-template-columns: 1fr; }
        }
    </style>
