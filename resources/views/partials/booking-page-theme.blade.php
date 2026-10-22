{{-- Styles des pages de réservation détaillées (circuit, activité, formule) : préfixe tp-. --}}
    <style>
        .tp-gal { display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 10px; height: clamp(280px, 46vh, 480px); }
        .tp-gal button { position: relative; padding: 0; border: 0; overflow: hidden; border-radius: 20px; background: #f3eee4; cursor: zoom-in; }
        .tp-gal button:first-child { grid-row: span 2; border-radius: 28px; }
        .tp-gal img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
        .tp-gal button:hover img { transform: scale(1.04); }
        .tp-gal.is-1 { grid-template-columns: 1fr; } .tp-gal.is-1 button:first-child { grid-row: span 2; }
        .tp-gal.is-2 { grid-template-columns: 2fr 1fr; } .tp-gal.is-2 button:nth-child(2) { grid-row: span 2; }
        .tp-gal__more { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(29,26,22,.55); color: #fff; font-weight: 700; }
        .tp-gal__empty { height: 220px; border-radius: 28px; background: #fdf4ec; color: #ef7c2b; display: flex; align-items: center; justify-content: center; font-size: 42px; }
        .tp-sec { scroll-margin-top: 100px; }
        .tp-h2 { margin: 0 0 14px; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.4vw + 16px, 32px); font-weight: 700; color: #1d1a16; }
        .tp-days { position: relative; margin: 0; padding: 0; list-style: none; }
        .tp-day { position: relative; padding: 0 0 22px 52px; }
        .tp-day::before { content: ''; position: absolute; left: 19px; top: 40px; bottom: 0; width: 2px; background: #ece5d6; }
        .tp-day:last-child::before { display: none; }
        .tp-day__n { position: absolute; left: 0; top: 0; width: 40px; height: 40px; border-radius: 999px; background: #b9500a; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
        .tp-day__card { padding: 14px 16px; border-radius: 18px; background: #fff; border: 1px solid #ece5d6; }
        .tp-day__title { margin: 0; font-size: 16px; font-weight: 700; color: #1d1a16; }
        .tp-day__text { margin: 6px 0 0; font-size: 14.5px; line-height: 1.65; color: #4a443a; white-space: pre-line; }
        .tp-day__tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .tp-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: #f4efe4; color: #5c5548; font-size: 12px; font-weight: 600; }
        .tp-incl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .tp-box { padding: 16px 18px; border-radius: 20px; background: #fff; border: 1px solid #ece5d6; }
        .tp-box h3 { margin: 0 0 10px; font-size: 14px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .tp-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 7px; font-size: 14.5px; color: #3d372c; }
        .tp-list i { width: 18px; }
        .tp-deps { display: flex; flex-direction: column; gap: 8px; }
        .tp-dep { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 18px; background: #fff; border: 1px solid #ece5d6; }
        .tp-dep__date { width: 54px; text-align: center; line-height: 1.05; }
        .tp-dep__date b { display: block; font-size: 22px; font-weight: 800; color: #1d1a16; }
        .tp-dep__date small { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9a4308; }
        .tp-dep__info { min-width: 0; font-size: 13px; color: #5c5548; }
        .tp-dep__info strong { display: block; font-size: 14.5px; color: #1d1a16; }
        .tp-dep__price { text-align: right; font-weight: 800; white-space: nowrap; }
        .tp-dep.is-full { opacity: .55; }
        .tp-badge { display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f4efe4; color: #5c5548; }
        .tp-badge.is-ok { background: #e7f3ec; color: #1b6b3a; } .tp-badge.is-warn { background: #fff3d6; color: #7a5300; } .tp-badge.is-off { background: #fbe9e5; color: #a3341f; }
        .tp-policy { margin: 0; padding: 0; list-style: none; display: grid; gap: 6px; }
        .tp-policy li { display: flex; justify-content: space-between; gap: 10px; padding: 10px 14px; border-radius: 14px; background: #fff; border: 1px solid #ece5d6; font-size: 14px; }
        .tp-card { position: sticky; top: 90px; padding: 20px; border-radius: 28px; background: #fdfbf7; border: 1px solid rgba(0,0,0,.08); box-shadow: 0 20px 50px rgba(20,18,12,.1); }
        .tp-card__price { margin: 0; font-size: 13px; color: #6b6458; }
        .tp-card__price b { font-size: 26px; color: #1d1a16; font-weight: 800; }
        .tp-label { display: block; margin: 14px 0 6px; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #6b6458; }
        .tp-select, .tp-input { width: 100%; padding: 11px 14px; border-radius: 14px; border: 1px solid rgba(0,0,0,.12); background: #fff; font-size: 14px; color: #1d1a16; }
        .tp-select:focus, .tp-input:focus { outline: 2px solid #ef7c2b; border-color: #ef7c2b; }
        .tp-step { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 10px 8px 14px; border: 1px solid #ece5d6; border-radius: 14px; background: #fff; margin-top: 6px; }
        .tp-step span { font-size: 14px; font-weight: 600; } .tp-step small { display: block; font-size: 11.5px; color: #6b6458; font-weight: 500; }
        .tp-step__ctl { display: inline-flex; align-items: center; gap: 6px; }
        .tp-step__ctl button { width: 34px; height: 34px; border-radius: 999px; border: 1.5px solid #ece5d6; background: #fff; color: #b9500a; }
        .tp-step__ctl button:hover { border-color: #b9500a; } .tp-step__ctl button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 1px; }
        .tp-step__ctl output { min-width: 22px; text-align: center; font-weight: 800; }
        .tp-extra { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 9px 12px; margin-top: 6px; border: 1px solid #ece5d6; border-radius: 14px; background: #fff; font-size: 13.5px; cursor: pointer; }
        .tp-extra input { width: 18px; height: 18px; accent-color: #b9500a; }
        .tp-extra:has(input:checked) { border-color: #b9500a; background: #fdf4ec; }
        .tp-choice { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .tp-choice label { position: relative; display: flex; flex-direction: column; gap: 2px; padding: 10px 12px; border: 1.5px solid #ece5d6; border-radius: 14px; background: #fff; cursor: pointer; font-size: 13px; font-weight: 700; }
        .tp-choice label small { font-weight: 500; color: #6b6458; font-size: 11.5px; }
        .tp-choice input { position: absolute; opacity: 0; }
        .tp-choice label:has(input:checked) { border-color: #b9500a; background: #fdf4ec; box-shadow: 0 0 0 3px rgba(239,124,43,.15); }
        .tp-choice label:has(input:focus-visible) { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .tp-choice label:has(input:disabled) { opacity: .45; cursor: not-allowed; }
        .tp-sum { margin-top: 14px; padding: 12px 14px; border-radius: 16px; background: rgba(0,0,0,.03); border: 1px solid rgba(0,0,0,.07); font-size: 13px; }
        .tp-sum p { margin: 0 0 4px; display: flex; justify-content: space-between; gap: 8px; color: #5c5548; }
        .tp-sum .is-total { margin: 8px 0 0; padding-top: 8px; border-top: 1px dashed #e2dacb; font-size: 15px; font-weight: 800; color: #1d1a16; }
        .tp-sum .is-now { color: #b9500a; font-weight: 800; }
        .tp-submit { width: 100%; margin-top: 14px; padding: 14px; border: 0; border-radius: 18px; background: #b9500a; color: #fff; font-weight: 800; font-size: 15px; cursor: pointer; }
        .tp-submit:hover { background: #9a4308; } .tp-submit:disabled { opacity: .5; cursor: not-allowed; }
        .tp-note { margin: 10px 0 0; font-size: 12px; color: #6b6458; line-height: 1.5; }
        .tp-err { margin-top: 10px; padding: 10px 12px; border-radius: 12px; background: #fbe9e5; color: #a3341f; font-size: 13px; }
        .tp-lb { position: fixed; inset: 0; z-index: 60; display: none; align-items: center; justify-content: center; background: rgba(10,9,7,.95); padding: 16px; }
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
