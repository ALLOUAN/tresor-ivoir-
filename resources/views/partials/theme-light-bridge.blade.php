<style>
    html:not(.dark) {
        --ti-bg: #e9e5d9;
        --ti-surface-1: #f0ece1;
        --ti-surface-2: #ddd7c7;
        --ti-surface-3: #d2cbb7;
        --ti-text-1: #1c1915;
        --ti-text-2: #2d2a23;
        --ti-text-3: #44413a;
        --ti-text-4: #544f47;
        --ti-border-1: #d6cfba;
        --ti-border-2: #c2b89e;
    }
    html:not(.dark) body { background:var(--ti-bg) !important; color:var(--ti-text-1) !important; }
    html:not(.dark) .text-white { color:#1c1915 !important; }
    html:not(.dark) .text-gray-100,
    html:not(.dark) .text-slate-100 { color:#1c1915 !important; }
    html:not(.dark) .text-gray-200,
    html:not(.dark) .text-slate-200 { color:var(--ti-text-2) !important; }
    html:not(.dark) .text-gray-300,
    html:not(.dark) .text-slate-300 { color:var(--ti-text-3) !important; }
    html:not(.dark) .text-gray-400,
    html:not(.dark) .text-slate-400 { color:var(--ti-text-4) !important; }
    html:not(.dark) .text-gray-500,
    html:not(.dark) .text-slate-500 { color:#5c574e !important; }
    html:not(.dark) .text-gray-600,
    html:not(.dark) .text-slate-600 { color:#544f47 !important; }
    html:not(.dark) .text-zinc-100,
    html:not(.dark) .text-neutral-100,
    html:not(.dark) .text-stone-100 { color:#1c1915 !important; }
    html:not(.dark) .text-zinc-200,
    html:not(.dark) .text-neutral-200,
    html:not(.dark) .text-stone-200 { color:#2d2a23 !important; }
    html:not(.dark) .text-zinc-300,
    html:not(.dark) .text-neutral-300,
    html:not(.dark) .text-stone-300 { color:#44413a !important; }
    html:not(.dark) .text-zinc-400,
    html:not(.dark) .text-neutral-400,
    html:not(.dark) .text-stone-400 { color:#544f47 !important; }
    html:not(.dark) .text-zinc-500,
    html:not(.dark) .text-neutral-500,
    html:not(.dark) .text-stone-500 { color:#5c574e !important; }
    html:not(.dark) .text-zinc-600,
    html:not(.dark) .text-neutral-600,
    html:not(.dark) .text-stone-600,
    html:not(.dark) .text-gray-700,
    html:not(.dark) .text-slate-700 { color:#6a655a !important; }
    html:not(.dark) .text-gray-800,
    html:not(.dark) .text-slate-800,
    html:not(.dark) .text-zinc-800,
    html:not(.dark) .text-neutral-800,
    html:not(.dark) .text-stone-800 { color:#44413a !important; }
    html:not(.dark) .text-gray-900,
    html:not(.dark) .text-slate-900,
    html:not(.dark) .text-zinc-900,
    html:not(.dark) .text-neutral-900,
    html:not(.dark) .text-stone-900 { color:#1c1915 !important; }
    /* Accent colors: avoid low contrast on light background */
    html:not(.dark) .text-orange-200,
    html:not(.dark) .text-orange-200\/90,
    html:not(.dark) .text-orange-200\/85,
    html:not(.dark) .text-gold-200,
    html:not(.dark) .text-gold-200\/90,
    html:not(.dark) .text-gold-200\/85,
    html:not(.dark) .text-orange-200 { color:#a3450a !important; }
    html:not(.dark) .text-orange-300\/90,
    html:not(.dark) .text-gold-300\/90 { color:#a3450a !important; }
    html:not(.dark) .text-orange-300\/80,
    html:not(.dark) .text-gold-300\/80 { color:#a54a0b !important; }
    html:not(.dark) .text-orange-300\/75,
    html:not(.dark) .text-gold-300\/75 { color:#a54a0b !important; }
    html:not(.dark) .text-gold-300,
    html:not(.dark) .text-orange-300 { color:#a54a0b !important; }
    html:not(.dark) .text-orange-300,
    html:not(.dark) .text-gold-300,
    html:not(.dark) .text-orange-300 { color:#a54a0b !important; }
    html:not(.dark) .text-orange-400,
    html:not(.dark) .text-orange-400\/90,
    html:not(.dark) .text-gold-400,
    html:not(.dark) .text-gold-400\/90,
    html:not(.dark) .text-orange-400 { color:#9f4709 !important; }
    html:not(.dark) .text-gold-400\/80,
    html:not(.dark) .text-orange-400\/80 { color:#a54a0b !important; }
    html:not(.dark) .text-gold-400\/70,
    html:not(.dark) .text-orange-400\/70 { color:#a3450a !important; }
    html:not(.dark) .text-gold-400\/60,
    html:not(.dark) .text-orange-400\/60 { color:#a54a0b !important; }
    html:not(.dark) .text-gold-500\/50,
    html:not(.dark) .text-orange-500\/50 { color:#a54a0b !important; }
    html:not(.dark) .text-dark-400 { color:#5c574e !important; }
    html:not(.dark) .text-dark-500 { color:#665f52 !important; }
    html:not(.dark) .text-emerald-200,
    html:not(.dark) .text-emerald-300 { color:#065f46 !important; }
    html:not(.dark) .text-emerald-400,
    html:not(.dark) .text-emerald-400\/90 { color:#036b4e !important; }
    html:not(.dark) .text-red-200,
    html:not(.dark) .text-rose-200 { color:#991b1b !important; }
    html:not(.dark) .text-red-400,
    html:not(.dark) .text-rose-400 { color:#b91c1c !important; }
    html:not(.dark) .placeholder-gray-500::placeholder,
    html:not(.dark) .placeholder-slate-500::placeholder,
    html:not(.dark) .placeholder-gray-600::placeholder,
    html:not(.dark) .placeholder-slate-600::placeholder { color:#665f52 !important; }
    html:not(.dark) .hover\:text-white:hover { color:#1c1915 !important; }
    html:not(.dark) a.text-orange-400,
    html:not(.dark) a.text-gold-400,
    html:not(.dark) a.text-orange-400 { color:#a54a0b !important; }
    html:not(.dark) a.text-orange-400:hover,
    html:not(.dark) a.text-gold-400:hover,
    html:not(.dark) a.text-orange-400:hover { color:#a3450a !important; }
    html:not(.dark) .border-white\/10 { border-color:rgba(0,0,0,0.1) !important; }
    html:not(.dark) .border-white\/20 { border-color:rgba(0,0,0,0.14) !important; }
    html:not(.dark) .border-white\/8 { border-color:rgba(0,0,0,0.08) !important; }
    html:not(.dark) .border-white\/6 { border-color:rgba(0,0,0,0.06) !important; }
    html:not(.dark) .border-white\/5 { border-color:rgba(0,0,0,0.05) !important; }
    html:not(.dark) .bg-white\/5,
    html:not(.dark) .bg-white\/4 { background-color:rgba(0,0,0,0.04) !important; }
    html:not(.dark) .bg-white\/\[0\.03\] { background-color:rgba(0,0,0,0.03) !important; }
    html:not(.dark) .bg-white\/2 { background-color:rgba(0,0,0,0.02) !important; }
    html:not(.dark) .bg-white\/10 { background-color:rgba(0,0,0,0.06) !important; }
    html:not(.dark) .bg-green-950\/50 { background-color:rgba(233, 229, 217, 0.82) !important; }
    html:not(.dark) .bg-green-950\/55 { background-color:rgba(233, 229, 217, 0.86) !important; }
    html:not(.dark) .bg-green-950,
    html:not(.dark) .bg-dark-900 { background-color:var(--ti-bg) !important; }
    html:not(.dark) .bg-dark-900\/60 { background-color:rgba(233, 229, 217, 0.82) !important; }
    html:not(.dark) .bg-green-900 { background-color:var(--ti-surface-1) !important; }
    html:not(.dark) .bg-slate-800,
    html:not(.dark) .bg-dark-800 { background-color:var(--ti-surface-2) !important; }
    html:not(.dark) .bg-dark-800\/50 { background-color:rgba(0,0,0,0.035) !important; }
    html:not(.dark) .bg-dark-800\/70 { background-color:rgba(0,0,0,0.045) !important; }
    html:not(.dark) .bg-dark-800\/80 { background-color:rgba(0,0,0,0.05) !important; }
    html:not(.dark) .bg-dark-700 { background-color:var(--ti-surface-3) !important; }
    html:not(.dark) .bg-dark-700\/40 { background-color:rgba(0,0,0,0.05) !important; }
    html:not(.dark) .bg-dark-700\/50 { background-color:rgba(0,0,0,0.06) !important; }
    html:not(.dark) .hover\:bg-dark-700\/80:hover { background-color:rgba(0,0,0,0.08) !important; }
    html:not(.dark) .bg-dark-600 { background-color:#e9e5d9 !important; }
    html:not(.dark) .bg-slate-700 { background-color:var(--ti-surface-3) !important; }
    html:not(.dark) .border-slate-800 { border-color:var(--ti-border-1) !important; }
    html:not(.dark) .border-slate-700 { border-color:var(--ti-border-2) !important; }
    /* Filet de sécurité générique : capture TOUTES les variantes d'opacité
       (bg-slate-800/70, border-slate-700/50, hover:bg-slate-800/40, etc.)
       qui ne sont pas listées individuellement ci-dessus, pour éviter les
       fonds/bordures sombres oubliés sur fond clair. */
    html:not(.dark) [class*="bg-slate-800/"],
    html:not(.dark) [class*="bg-dark-800/"] { background-color:var(--ti-surface-2) !important; }
    html:not(.dark) [class*="bg-slate-700/"],
    html:not(.dark) [class*="bg-dark-700/"] { background-color:var(--ti-surface-3) !important; }
    html:not(.dark) [class*="bg-slate-900/"],
    html:not(.dark) [class*="bg-dark-900/"] { background-color:var(--ti-bg) !important; }
    html:not(.dark) [class*="bg-green-900/"] { background-color:var(--ti-surface-1) !important; }
    html:not(.dark) [class*="bg-green-950/"] { background-color:var(--ti-bg) !important; }
    html:not(.dark) [class*="border-slate-800/"],
    html:not(.dark) [class*="divide-slate-800/"] { border-color:var(--ti-border-1) !important; }
    html:not(.dark) [class*="border-slate-700/"] { border-color:var(--ti-border-2) !important; }
    html:not(.dark) [class*="border-green-700/"],
    html:not(.dark) [class*="border-green-800/"] { border-color:var(--ti-border-2) !important; }
    html:not(.dark) [class*="hover:bg-slate-800/"]:hover,
    html:not(.dark) [class*="hover:bg-dark-800/"]:hover { background-color:rgba(0,0,0,0.05) !important; }
    html:not(.dark) [class*="hover:bg-slate-700/"]:hover,
    html:not(.dark) [class*="hover:bg-dark-700/"]:hover { background-color:rgba(0,0,0,0.07) !important; }
    html:not(.dark) [class*="hover:bg-green-900/"]:hover,
    html:not(.dark) [class*="hover:bg-green-950/"]:hover { background-color:rgba(0,0,0,0.04) !important; }
    html:not(.dark) [class*="hover:border-slate-700/"]:hover,
    html:not(.dark) [class*="hover:border-green-700/"]:hover { border-color:var(--ti-border-2) !important; }
    html:not(.dark) [class*="text-slate-400/"],
    html:not(.dark) [class*="text-slate-500/"] { color:var(--ti-text-4) !important; }
    html:not(.dark) [class*="text-slate-600/"],
    html:not(.dark) [class*="text-slate-700/"] { color:#5c574e !important; }
    html:not(.dark) .border-gold-500\/20 { border-color:rgba(194, 94, 10,0.22) !important; }
    html:not(.dark) .border-gold-400\/50 { border-color:rgba(194, 94, 10,0.35) !important; }
    html:not(.dark) .border-gold-500\/25 { border-color:rgba(194, 94, 10,0.25) !important; }
    html:not(.dark) .border-gold-500\/30 { border-color:rgba(194, 94, 10,0.3) !important; }
    html:not(.dark) .hover\:border-gold-400\/40:hover { border-color:rgba(194, 94, 10,0.35) !important; }
    html:not(.dark) .hover\:border-orange-400\/45:hover { border-color:rgba(194, 94, 10,0.35) !important; }
    html:not(.dark) .hover\:border-gold-500\/20:hover { border-color:rgba(194, 94, 10,0.26) !important; }
    html:not(.dark) .hover\:border-gold-500\/40:hover { border-color:rgba(194, 94, 10,0.34) !important; }
    html:not(.dark) .hover\:border-gold-500\/25:hover { border-color:rgba(194, 94, 10,0.3) !important; }
    html:not(.dark) .group:hover .group-hover\:border-gold-500\/30 { border-color:rgba(194, 94, 10,0.3) !important; }
    html:not(.dark) .divide-slate-800 > * + * { border-color:var(--ti-border-1) !important; }
    html:not(.dark) [class*="bg-[#ffffff]"] { background-color:var(--ti-bg) !important; }
    html:not(.dark) [class*="bg-[#ffffff]"] { background-color:var(--ti-surface-1) !important; }
    html:not(.dark) [class*="bg-[#ffffff]"] { background-color:var(--ti-surface-2) !important; }
    html:not(.dark) [class*="bg-[#ffffff]"] { background-color:var(--ti-surface-1) !important; }
    html:not(.dark) .bg-white { background-color:var(--ti-bg) !important; }
    /* Component polish for public readability */
    html:not(.dark) input,
    html:not(.dark) select,
    html:not(.dark) textarea { color:var(--ti-text-1) !important; }
    html:not(.dark) .bg-emerald-500\/10 { background-color:rgba(16,185,129,0.12) !important; }
    html:not(.dark) .bg-red-500\/10,
    html:not(.dark) .bg-rose-500\/10 { background-color:rgba(239,68,68,0.10) !important; }
    html:not(.dark) .bg-green-500\/10 { background-color:rgba(34,153,84,0.12) !important; }
    html:not(.dark) .bg-orange-500\/10,
    html:not(.dark) .bg-gold-500\/10 { background-color:rgba(242, 121, 15,0.14) !important; }
    html:not(.dark) .bg-gold-500\/15 { background-color:rgba(242, 121, 15,0.16) !important; }
    html:not(.dark) .hover\:bg-gold-500\/5:hover { background-color:rgba(242, 121, 15,0.08) !important; }
    html:not(.dark) .hover\:bg-dark-800\/50:hover { background-color:rgba(0,0,0,0.04) !important; }
    /* Dark overlays become light overlays in light mode for readability */
    html:not(.dark) .from-green-950\/95 { --tw-gradient-from: rgba(255,255,255,0.95) !important; }
    html:not(.dark) .via-green-950\/30 { --tw-gradient-via: rgba(255,255,255,0.58) !important; }
    html:not(.dark) .to-transparent { --tw-gradient-to: rgba(255,255,255,0) !important; }
    html:not(.dark) .from-dark-700 { --tw-gradient-from: #ffffff !important; }
    html:not(.dark) .to-dark-600 { --tw-gradient-to: #ffffff !important; }
    html:not(.dark) .via-dark-800 { --tw-gradient-via: #ffffff !important; }
    html:not(.dark) .from-dark-900\/60 { --tw-gradient-from: rgba(255,255,255,0.82) !important; }
    html:not(.dark) .shadow-2xl,
    html:not(.dark) .shadow-xl { box-shadow:0 12px 28px rgba(0,0,0,0.08) !important; }
    /* Strong readability fix for article cards in light mode */
    html:not(.dark) .article-card {
        background:#e9e5d9 !important;
        border-color: rgba(0,0,0,0.1) !important;
        box-shadow: 0 12px 24px rgba(0,0,0,0.06);
    }
    html:not(.dark) .article-card .bg-linear-to-t.from-green-950\/95.via-green-950\/30.to-transparent,
    html:not(.dark) .article-card [class*="bg-linear-to-t from-green-950/95"] {
        background: linear-gradient(to top, rgba(255,255,255,0.97), rgba(255,255,255,0.82), rgba(255,255,255,0.02)) !important;
    }
    html:not(.dark) .article-card h2,
    html:not(.dark) .article-card h3,
    html:not(.dark) .article-card h4 {
        color: #1c1915 !important;
    }
    html:not(.dark) .article-card .text-gray-400,
    html:not(.dark) .article-card .text-gray-500,
    html:not(.dark) .article-card .text-gray-600 {
        color: #544f47 !important;
    }
    html:not(.dark) .article-card .text-orange-400,
    html:not(.dark) .article-card .text-gold-400,
    html:not(.dark) .article-card .text-gold-400\/70,
    html:not(.dark) .article-card .text-gold-400\/60 {
        color: #a54a0b !important;
    }
    /* Category cards section readability */
    html:not(.dark) .cat-card {
        background:#e9e5d9 !important;
        border-color: rgba(0,0,0,0.12) !important;
        box-shadow: 0 10px 24px rgba(0,0,0,0.06);
    }
    html:not(.dark) .cat-card:hover {
        border-color: rgba(194, 94, 10,0.35) !important;
        box-shadow: 0 14px 28px rgba(194, 94, 10,0.14);
    }
    html:not(.dark) .cat-card h3 { color:#1c1915 !important; }
    html:not(.dark) .cat-card p.text-gray-500 { color:#544f47 !important; }
    html:not(.dark) .cat-card span.text-gray-600 { color:#5c574e !important; }
    html:not(.dark) .cat-card .text-gold-400,
    html:not(.dark) .cat-card .text-gold-400\/70 { color:#a54a0b !important; }
    html:not(.dark) .cat-card .bg-white\/5 { background-color:rgba(0,0,0,0.04) !important; }
    html:not(.dark) .cat-card .group-hover\:bg-gold-500\/15 { background-color:rgba(242, 121, 15,0.14) !important; }
    html:not(.dark) .cat-card [style*="radial-gradient(circle, #f2790f"] { opacity:0.2 !important; }
    /* Inline gold grid overlays used on some hero sections */
    html:not(.dark) [style*="repeating-linear-gradient(45deg,#f2790f"] {
        opacity: 0.14 !important;
        background-image: repeating-linear-gradient(
            45deg,
            rgba(194, 94, 10,0.45) 0,
            rgba(194, 94, 10,0.45) 1px,
            transparent 0,
            transparent 50%
        ) !important;
    }
    /* Ensure gradient text remains readable in light mode */
    html:not(.dark) .text-transparent.bg-clip-text,
    html:not(.dark) .text-transparent[class*="bg-clip-text"] {
        color:#1c1915 !important;
        -webkit-text-fill-color:#1c1915 !important;
        background-image:none !important;
    }

    /* ——— Couverture complète des couleurs de texte, générée à partir d'un
       inventaire de toutes les classes text-* réellement utilisées dans le
       projet (resources/views), pour qu'aucun texte ne reste dans sa teinte
       "mode sombre" (donc invisible) une fois passé en mode clair. ——— */
    html:not(.dark) .text-orange-50,
    html:not(.dark) .text-orange-100,
    html:not(.dark) .text-orange-200,
    html:not(.dark) .text-orange-200\/40,
    html:not(.dark) .text-orange-200\/70,
    html:not(.dark) .text-orange-200\/80,
    html:not(.dark) .text-orange-200\/85,
    html:not(.dark) .text-orange-200\/90 { color:#a3450a !important; }
    html:not(.dark) .text-orange-300,
    html:not(.dark) .text-orange-300\/70,
    html:not(.dark) .text-orange-300\/75,
    html:not(.dark) .text-orange-300\/80,
    html:not(.dark) .text-orange-300\/90,
    html:not(.dark) .text-orange-600,
    html:not(.dark) .text-orange-600\/70,
    html:not(.dark) .text-orange-800\/80 { color:#a54a0b !important; }
    html:not(.dark) .text-orange-400,
    html:not(.dark) .text-orange-400\/50,
    html:not(.dark) .text-orange-400\/60,
    html:not(.dark) .text-orange-400\/70,
    html:not(.dark) .text-orange-400\/80,
    html:not(.dark) .text-orange-400\/90,
    html:not(.dark) .text-orange-500,
    html:not(.dark) .text-orange-500\/40,
    html:not(.dark) .text-orange-500\/50,
    html:not(.dark) .text-orange-500\/60,
    html:not(.dark) .text-orange-500\/70,
    html:not(.dark) .text-orange-500\/80 { color:#a54a0b !important; }
    html:not(.dark) .text-gold-100,
    html:not(.dark) .text-gold-200\/90 { color:#a3450a !important; }
    html:not(.dark) .text-gold-400\/60,
    html:not(.dark) .text-gold-400\/70,
    html:not(.dark) .text-gold-400\/80,
    html:not(.dark) .text-gold-400\/90,
    html:not(.dark) .text-gold-500\/20,
    html:not(.dark) .text-gold-500\/50,
    html:not(.dark) .text-gold-500\/60,
    html:not(.dark) .text-gold-500\/90 { color:#a54a0b !important; }
    /* Brand green: consolidated readable shades for every former blue/sky/cyan/indigo/violet/fuchsia/purple/teal/pink accent */
    html:not(.dark) .text-green-50,
    html:not(.dark) .text-green-100,
    html:not(.dark) .text-green-100\/80,
    html:not(.dark) .text-green-100\/90,
    html:not(.dark) .text-green-200,
    html:not(.dark) .text-green-200\/80,
    html:not(.dark) .text-green-200\/90 { color:#14532d !important; }
    html:not(.dark) .text-green-300,
    html:not(.dark) .text-green-300\/70,
    html:not(.dark) .text-green-300\/80,
    html:not(.dark) .text-green-300\/90,
    html:not(.dark) .text-green-600 { color:#166534 !important; }
    html:not(.dark) .text-green-400,
    html:not(.dark) .text-green-400\/60,
    html:not(.dark) .text-green-400\/70,
    html:not(.dark) .text-green-400\/80,
    html:not(.dark) .text-green-400\/90,
    html:not(.dark) .text-green-500,
    html:not(.dark) .text-green-500\/70 { color:#1a7038 !important; }
    html:not(.dark) .text-emerald-100,
    html:not(.dark) .text-emerald-200\/70,
    html:not(.dark) .text-emerald-200\/90,
    html:not(.dark) .text-emerald-300\/80,
    html:not(.dark) .text-emerald-300\/90 { color:#065f46 !important; }
    html:not(.dark) .text-emerald-400\/60,
    html:not(.dark) .text-emerald-400\/70,
    html:not(.dark) .text-emerald-400\/80,
    html:not(.dark) .text-emerald-400\/85,
    html:not(.dark) .text-emerald-400\/90,
    html:not(.dark) .text-emerald-400\/95,
    html:not(.dark) .text-emerald-500,
    html:not(.dark) .text-emerald-600,
    html:not(.dark) .text-emerald-600\/80 { color:#036b4e !important; }
    html:not(.dark) .text-red-100,
    html:not(.dark) .text-red-300 { color:#991b1b !important; }
    html:not(.dark) .text-red-400\/90,
    html:not(.dark) .text-red-400\/95,
    html:not(.dark) .text-red-500,
    html:not(.dark) .text-red-600 { color:#b91c1c !important; }
    html:not(.dark) .text-rose-100,
    html:not(.dark) .text-rose-100\/95,
    html:not(.dark) .text-rose-200\/70,
    html:not(.dark) .text-rose-200\/90,
    html:not(.dark) .text-rose-300,
    html:not(.dark) .text-rose-300\/80 { color:#991b1b !important; }
    html:not(.dark) .text-slate-950 { color:#1c1915 !important; }

    /* ——— Mêmes classes en variantes hover / group-hover ——— */
    html:not(.dark) .group:hover .group-hover\:text-orange-100,
    html:not(.dark) .group:hover .group-hover\:text-orange-300,
    html:not(.dark) .group:hover .group-hover\:text-gold-200,
    html:not(.dark) .group:hover .group-hover\:text-gold-300 { color:#a3450a !important; }
    html:not(.dark) .group:hover .group-hover\:text-orange-400,
    html:not(.dark) .group:hover .group-hover\:text-orange-400\/60,
    html:not(.dark) .group:hover .group-hover\:text-orange-400\/70,
    html:not(.dark) .group:hover .group-hover\:text-orange-400\/80,
    html:not(.dark) .group:hover .group-hover\:text-gold-400 { color:#a54a0b !important; }
    html:not(.dark) .group:hover .group-hover\:text-gray-400 { color:#544f47 !important; }
    html:not(.dark) .group:hover .group-hover\:text-slate-200 { color:#2d2a23 !important; }
    html:not(.dark) .group:hover .group-hover\:text-slate-300 { color:#44413a !important; }
    html:not(.dark) .group:hover .group-hover\:text-green-300 { color:#166534 !important; }
    html:not(.dark) .group:hover .group-hover\:text-white { color:#1c1915 !important; }
    html:not(.dark) .group:hover .group-hover\:text-white\/60 { color:#1c1915 !important; }
    html:not(.dark) .hover\:text-orange-200:hover,
    html:not(.dark) .hover\:text-orange-300:hover,
    html:not(.dark) .hover\:text-gold-200:hover,
    html:not(.dark) .hover\:text-gold-300:hover { color:#a3450a !important; }
    html:not(.dark) .hover\:text-orange-400:hover,
    html:not(.dark) .hover\:text-orange-400\/90:hover,
    html:not(.dark) .hover\:text-gold-400:hover { color:#a54a0b !important; }
    html:not(.dark) .hover\:text-green-200:hover { color:#14532d !important; }
    html:not(.dark) .hover\:text-green-300:hover { color:#166534 !important; }
    html:not(.dark) .hover\:text-emerald-300:hover { color:#065f46 !important; }
    html:not(.dark) .hover\:text-emerald-400:hover { color:#036b4e !important; }
    html:not(.dark) .hover\:text-gray-100:hover { color:#1c1915 !important; }
    html:not(.dark) .hover\:text-gray-300:hover { color:#44413a !important; }
    html:not(.dark) .hover\:text-gray-400:hover { color:#544f47 !important; }
    html:not(.dark) .hover\:text-red-300:hover { color:#991b1b !important; }
    html:not(.dark) .hover\:text-red-400:hover { color:#b91c1c !important; }
    html:not(.dark) .hover\:text-rose-200:hover { color:#991b1b !important; }
    html:not(.dark) .hover\:text-rose-300:hover { color:#991b1b !important; }
    html:not(.dark) .hover\:text-slate-200:hover { color:#2d2a23 !important; }
    html:not(.dark) .hover\:text-slate-300:hover { color:#44413a !important; }
</style>
