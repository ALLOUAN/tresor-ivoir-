<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — {{ $siteBrand['site_name'] ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col items-center justify-center px-6">
    <div class="max-w-md text-center">
        <p class="text-amber-400 text-sm font-semibold uppercase tracking-wider mb-2">{{ $siteBrand['site_name'] ?? config('app.name') }}</p>
        <h1 class="text-2xl font-bold text-white mb-3">Site en maintenance</h1>
        <p class="text-slate-400 text-sm leading-relaxed">
            Nous effectuons une mise à jour. Merci de revenir un peu plus tard.
        </p>
    </div>
</body>
</html>
