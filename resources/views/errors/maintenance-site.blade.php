<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — {{ $siteBrand['site_name'] ?? config('app.name') }}</title>
    @include('partials.theme-init')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'system-ui', 'sans-serif'],
                        display: ['Instrument Serif', 'Georgia', 'serif'],
                    },
                    animation: {
                        'blob': 'blob 18s ease-in-out infinite',
                        'blob-slow': 'blob 24s ease-in-out infinite reverse',
                        'float': 'float 6s ease-in-out infinite',
                        'shimmer': 'shimmer 3s ease-in-out infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.05)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.95)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-8px)' },
                        },
                        shimmer: {
                            '0%, 100%': { opacity: '0.4' },
                            '50%': { opacity: '1' },
                        },
                    },
                },
            },
        };
    </script>
    <style>
        .grain {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
        }
        html:not(.dark) [class*="border-white"] { border-color:rgba(0,0,0,.1) !important; }
        html:not(.dark) [class*="bg-white/"] { background-color:rgba(0,0,0,.04) !important; }
    </style>
    @include('partials.theme-light-bridge')
</head>
<body class="h-full min-h-screen bg-[#050508] text-zinc-100 font-sans selection:bg-amber-500/30 selection:text-amber-100">
    {{-- Fond : blobs + grille + grain --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-1/4 -left-1/4 h-[70vmin] w-[70vmin] rounded-full bg-violet-600/25 blur-[100px] animate-blob motion-reduce:animate-none"></div>
        <div class="absolute top-1/3 -right-1/4 h-[60vmin] w-[60vmin] rounded-full bg-amber-500/20 blur-[90px] animate-blob-slow motion-reduce:animate-none"></div>
        <div class="absolute bottom-0 left-1/3 h-[50vmin] w-[50vmin] rounded-full bg-cyan-500/15 blur-[80px] animate-blob motion-reduce:animate-none"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:64px_64px] [mask-image:radial-gradient(ellipse_80%_60%_at_50%_40%,black,transparent)]"></div>
        <div class="absolute inset-0 grain opacity-60"></div>
    </div>

    <main class="relative z-10 flex min-h-screen flex-col items-center justify-center px-5 py-16 sm:px-8">
        <div class="w-full max-w-lg">
            {{-- Carte glass --}}
            <div class="relative rounded-[1.75rem] border border-white/[0.08] bg-white/[0.03] p-8 shadow-2xl shadow-black/40 backdrop-blur-2xl sm:p-10">
                {{-- Liseré lumineux haut --}}
                <div class="pointer-events-none absolute inset-x-6 -top-px h-px bg-gradient-to-r from-transparent via-amber-400/50 to-transparent"></div>

                <div class="flex flex-col items-center text-center">
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.35em] text-amber-400/90">
                        {{ $siteBrand['site_name'] ?? config('app.name') }}
                    </p>

                    {{-- Icône / halo --}}
                    <div class="relative mb-8">
                        <div class="absolute inset-0 scale-150 rounded-full bg-gradient-to-tr from-amber-500/20 via-violet-500/15 to-transparent blur-xl animate-shimmer motion-reduce:animate-none"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl border border-white/10 bg-gradient-to-br from-white/[0.08] to-transparent shadow-inner animate-float motion-reduce:animate-none">
                            <svg class="h-9 w-9 text-amber-400/90" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.932 6.932 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.37.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.213-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>

                    <h1 class="font-display text-[2.125rem] leading-[1.15] text-white sm:text-4xl sm:leading-tight">
                        Site en maintenance
                    </h1>

                    <p class="mt-5 max-w-md text-[15px] leading-relaxed text-zinc-400 sm:text-base">
                        {{ $maintenanceMessage ?? 'Nous effectuons une mise à jour. Merci de revenir un peu plus tard.' }}
                    </p>

                    {{-- Barre d’état décorative --}}
                    <div class="mt-10 flex w-full max-w-xs flex-col gap-2">
                        <div class="flex items-center justify-between text-[11px] font-medium uppercase tracking-wider text-zinc-500">
                            <span>État</span>
                            <span class="text-amber-400/80">Mise à jour</span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-white/[0.06]">
                            <div class="h-full w-2/5 rounded-full bg-gradient-to-r from-amber-500 via-amber-400 to-amber-600 animate-shimmer motion-reduce:animate-none"></div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-8 text-center text-[12px] text-zinc-600">
                Merci de votre patience — nous revenons très bientôt.
            </p>
        </div>
    </main>
</body>
</html>
