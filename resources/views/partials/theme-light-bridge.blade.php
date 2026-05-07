<style>
    html:not(.dark) {
        --ti-bg: #f8f5ee;
        --ti-surface-1: #ffffff;
        --ti-surface-2: #f4f0e8;
        --ti-surface-3: #e8e3da;
        --ti-text-1: #1c1915;
        --ti-text-2: #2d2a23;
        --ti-text-3: #44413a;
        --ti-text-4: #6b6860;
        --ti-border-1: #e8e3da;
        --ti-border-2: #d6d0c5;
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
    html:not(.dark) .text-slate-500 { color:#7c796f !important; }
    html:not(.dark) .text-gray-600,
    html:not(.dark) .text-slate-600 { color:#6b6860 !important; }
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
    html:not(.dark) .text-stone-400 { color:#6b6860 !important; }
    html:not(.dark) .text-zinc-500,
    html:not(.dark) .text-neutral-500,
    html:not(.dark) .text-stone-500 { color:#7c796f !important; }
    html:not(.dark) .text-zinc-600,
    html:not(.dark) .text-neutral-600,
    html:not(.dark) .text-stone-600,
    html:not(.dark) .text-gray-700,
    html:not(.dark) .text-slate-700 { color:#8a867c !important; }
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
    html:not(.dark) .text-amber-200,
    html:not(.dark) .text-amber-200\/90,
    html:not(.dark) .text-amber-200\/85,
    html:not(.dark) .text-gold-200,
    html:not(.dark) .text-gold-200\/90,
    html:not(.dark) .text-gold-200\/85,
    html:not(.dark) .text-yellow-200 { color:#92400e !important; }
    html:not(.dark) .text-amber-300\/90,
    html:not(.dark) .text-gold-300\/90 { color:#92400e !important; }
    html:not(.dark) .text-amber-300\/80,
    html:not(.dark) .text-gold-300\/80 { color:#b45309 !important; }
    html:not(.dark) .text-amber-300\/75,
    html:not(.dark) .text-gold-300\/75 { color:#b45309 !important; }
    html:not(.dark) .text-gold-300,
    html:not(.dark) .text-amber-300 { color:#b45309 !important; }
    html:not(.dark) .text-amber-300,
    html:not(.dark) .text-gold-300,
    html:not(.dark) .text-yellow-300 { color:#b45309 !important; }
    html:not(.dark) .text-amber-400,
    html:not(.dark) .text-amber-400\/90,
    html:not(.dark) .text-gold-400,
    html:not(.dark) .text-gold-400\/90,
    html:not(.dark) .text-yellow-400 { color:#d97706 !important; }
    html:not(.dark) .text-gold-400\/80,
    html:not(.dark) .text-amber-400\/80 { color:#b45309 !important; }
    html:not(.dark) .text-gold-400\/70,
    html:not(.dark) .text-amber-400\/70 { color:#92400e !important; }
    html:not(.dark) .text-gold-400\/60,
    html:not(.dark) .text-amber-400\/60 { color:#b45309 !important; }
    html:not(.dark) .text-gold-500\/50,
    html:not(.dark) .text-amber-500\/50 { color:#b45309 !important; }
    html:not(.dark) .text-dark-400 { color:#7c796f !important; }
    html:not(.dark) .text-dark-500 { color:#9e9b90 !important; }
    html:not(.dark) .text-emerald-200,
    html:not(.dark) .text-emerald-300 { color:#065f46 !important; }
    html:not(.dark) .text-emerald-400,
    html:not(.dark) .text-emerald-400\/90 { color:#047857 !important; }
    html:not(.dark) .text-red-200,
    html:not(.dark) .text-rose-200 { color:#991b1b !important; }
    html:not(.dark) .text-red-400,
    html:not(.dark) .text-rose-400 { color:#b91c1c !important; }
    html:not(.dark) .text-blue-200,
    html:not(.dark) .text-sky-200 { color:#1e3a8a !important; }
    html:not(.dark) .text-blue-400,
    html:not(.dark) .text-sky-400 { color:#1d4ed8 !important; }
    html:not(.dark) .placeholder-gray-500::placeholder,
    html:not(.dark) .placeholder-slate-500::placeholder,
    html:not(.dark) .placeholder-gray-600::placeholder,
    html:not(.dark) .placeholder-slate-600::placeholder { color:#9e9b90 !important; }
    html:not(.dark) .hover\:text-white:hover { color:#1c1915 !important; }
    html:not(.dark) a.text-amber-400,
    html:not(.dark) a.text-gold-400,
    html:not(.dark) a.text-yellow-400 { color:#b45309 !important; }
    html:not(.dark) a.text-amber-400:hover,
    html:not(.dark) a.text-gold-400:hover,
    html:not(.dark) a.text-yellow-400:hover { color:#92400e !important; }
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
    html:not(.dark) .bg-black\/50 { background-color:rgba(255,255,255,0.82) !important; }
    html:not(.dark) .bg-black\/55 { background-color:rgba(255,255,255,0.86) !important; }
    html:not(.dark) .bg-slate-950,
    html:not(.dark) .bg-dark-900 { background-color:var(--ti-bg) !important; }
    html:not(.dark) .bg-dark-900\/60 { background-color:rgba(255,255,255,0.82) !important; }
    html:not(.dark) .bg-slate-900 { background-color:var(--ti-surface-1) !important; }
    html:not(.dark) .bg-slate-800,
    html:not(.dark) .bg-dark-800 { background-color:var(--ti-surface-2) !important; }
    html:not(.dark) .bg-dark-800\/50 { background-color:rgba(244,240,232,0.64) !important; }
    html:not(.dark) .bg-dark-800\/70 { background-color:rgba(244,240,232,0.74) !important; }
    html:not(.dark) .bg-dark-800\/80 { background-color:rgba(247,243,235,0.9) !important; }
    html:not(.dark) .bg-dark-700 { background-color:var(--ti-surface-3) !important; }
    html:not(.dark) .bg-dark-700\/40 { background-color:rgba(232,227,218,0.58) !important; }
    html:not(.dark) .bg-dark-700\/50 { background-color:rgba(232,227,218,0.7) !important; }
    html:not(.dark) .hover\:bg-dark-700\/80:hover { background-color:rgba(232,227,218,0.86) !important; }
    html:not(.dark) .bg-dark-600 { background-color:#dcd5ca !important; }
    html:not(.dark) .bg-slate-700 { background-color:var(--ti-surface-3) !important; }
    html:not(.dark) .bg-zinc-950,
    html:not(.dark) .bg-neutral-950,
    html:not(.dark) .bg-stone-950 { background-color:#ffffff !important; }
    html:not(.dark) .bg-zinc-900,
    html:not(.dark) .bg-neutral-900,
    html:not(.dark) .bg-stone-900 { background-color:#f4f0e8 !important; }
    html:not(.dark) .border-slate-800 { border-color:var(--ti-border-1) !important; }
    html:not(.dark) .border-slate-700 { border-color:var(--ti-border-2) !important; }
    html:not(.dark) .border-gold-500\/20 { border-color:rgba(180,83,9,0.22) !important; }
    html:not(.dark) .border-gold-400\/50 { border-color:rgba(180,83,9,0.35) !important; }
    html:not(.dark) .border-gold-500\/25 { border-color:rgba(180,83,9,0.25) !important; }
    html:not(.dark) .border-gold-500\/30 { border-color:rgba(180,83,9,0.3) !important; }
    html:not(.dark) .hover\:border-gold-400\/40:hover { border-color:rgba(180,83,9,0.35) !important; }
    html:not(.dark) .hover\:border-amber-400\/45:hover { border-color:rgba(180,83,9,0.35) !important; }
    html:not(.dark) .hover\:border-gold-500\/20:hover { border-color:rgba(180,83,9,0.26) !important; }
    html:not(.dark) .hover\:border-gold-500\/40:hover { border-color:rgba(180,83,9,0.34) !important; }
    html:not(.dark) .hover\:border-gold-500\/25:hover { border-color:rgba(180,83,9,0.3) !important; }
    html:not(.dark) .group:hover .group-hover\:border-gold-500\/30 { border-color:rgba(180,83,9,0.3) !important; }
    html:not(.dark) .divide-slate-800 > * + * { border-color:var(--ti-border-1) !important; }
    html:not(.dark) [class*="bg-[#0d0d0b]"] { background-color:var(--ti-bg) !important; }
    html:not(.dark) [class*="bg-[#141410]"] { background-color:var(--ti-surface-1) !important; }
    html:not(.dark) [class*="bg-[#1c1c16]"] { background-color:var(--ti-surface-2) !important; }
    html:not(.dark) [class*="bg-[#080706]"] { background-color:var(--ti-surface-1) !important; }
    /* Component polish for public readability */
    html:not(.dark) input,
    html:not(.dark) select,
    html:not(.dark) textarea { color:var(--ti-text-1) !important; }
    html:not(.dark) .bg-emerald-500\/10 { background-color:rgba(16,185,129,0.12) !important; }
    html:not(.dark) .bg-red-500\/10,
    html:not(.dark) .bg-rose-500\/10 { background-color:rgba(239,68,68,0.10) !important; }
    html:not(.dark) .bg-blue-500\/10,
    html:not(.dark) .bg-sky-500\/10 { background-color:rgba(59,130,246,0.10) !important; }
    html:not(.dark) .bg-amber-500\/10,
    html:not(.dark) .bg-gold-500\/10 { background-color:rgba(245,158,11,0.14) !important; }
    html:not(.dark) .bg-gold-500\/15 { background-color:rgba(245,158,11,0.16) !important; }
    html:not(.dark) .hover\:bg-gold-500\/5:hover { background-color:rgba(245,158,11,0.08) !important; }
    html:not(.dark) .hover\:bg-dark-800\/50:hover { background-color:rgba(244,240,232,0.66) !important; }
    /* Dark overlays become light overlays in light mode for readability */
    html:not(.dark) .from-black\/95 { --tw-gradient-from: rgba(255,255,255,0.95) !important; }
    html:not(.dark) .via-black\/30 { --tw-gradient-via: rgba(255,255,255,0.58) !important; }
    html:not(.dark) .to-transparent { --tw-gradient-to: rgba(255,255,255,0) !important; }
    html:not(.dark) .from-dark-700 { --tw-gradient-from: #e8e3da !important; }
    html:not(.dark) .to-dark-600 { --tw-gradient-to: #dcd5ca !important; }
    html:not(.dark) .via-dark-800 { --tw-gradient-via: #f4f0e8 !important; }
    html:not(.dark) .from-dark-900\/60 { --tw-gradient-from: rgba(255,255,255,0.82) !important; }
    html:not(.dark) .shadow-2xl,
    html:not(.dark) .shadow-xl { box-shadow:0 12px 28px rgba(0,0,0,0.08) !important; }
    /* Strong readability fix for article cards in light mode */
    html:not(.dark) .article-card {
        background: #ffffff !important;
        border-color: rgba(0,0,0,0.1) !important;
        box-shadow: 0 12px 24px rgba(0,0,0,0.06);
    }
    html:not(.dark) .article-card .bg-linear-to-t.from-black\/95.via-black\/30.to-transparent,
    html:not(.dark) .article-card [class*="bg-linear-to-t from-black/95"] {
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
        color: #6b6860 !important;
    }
    html:not(.dark) .article-card .text-amber-400,
    html:not(.dark) .article-card .text-gold-400,
    html:not(.dark) .article-card .text-gold-400\/70,
    html:not(.dark) .article-card .text-gold-400\/60 {
        color: #b45309 !important;
    }
    /* Category cards section readability */
    html:not(.dark) .cat-card {
        background: linear-gradient(145deg, #ffffff, #f7f3eb) !important;
        border-color: rgba(0,0,0,0.12) !important;
        box-shadow: 0 10px 24px rgba(0,0,0,0.06);
    }
    html:not(.dark) .cat-card:hover {
        border-color: rgba(180,83,9,0.35) !important;
        box-shadow: 0 14px 28px rgba(180,83,9,0.14);
    }
    html:not(.dark) .cat-card h3 { color:#1c1915 !important; }
    html:not(.dark) .cat-card p.text-gray-500 { color:#6b6860 !important; }
    html:not(.dark) .cat-card span.text-gray-600 { color:#7c796f !important; }
    html:not(.dark) .cat-card .text-gold-400,
    html:not(.dark) .cat-card .text-gold-400\/70 { color:#b45309 !important; }
    html:not(.dark) .cat-card .bg-white\/5 { background-color:rgba(0,0,0,0.04) !important; }
    html:not(.dark) .cat-card .group-hover\:bg-gold-500\/15 { background-color:rgba(245,158,11,0.14) !important; }
    html:not(.dark) .cat-card [style*="radial-gradient(circle, #e8a020"] { opacity:0.2 !important; }
    /* Inline gold grid overlays used on some hero sections */
    html:not(.dark) [style*="repeating-linear-gradient(45deg,#e8a020"] {
        opacity: 0.14 !important;
        background-image: repeating-linear-gradient(
            45deg,
            rgba(180,83,9,0.45) 0,
            rgba(180,83,9,0.45) 1px,
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
</style>
