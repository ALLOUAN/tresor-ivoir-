<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title_fr }} — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <a href="{{ route('events.index') }}" class="text-amber-400 text-sm">← Retour à l'agenda</a>

        <div class="mt-4 bg-slate-900 border border-slate-800 rounded-xl p-6">
            <p class="text-amber-400 text-xs uppercase">{{ $event->category->name_fr ?? 'Événement' }}</p>
            <h1 class="text-3xl font-bold mt-2">{{ $event->title_fr }}</h1>
            <p class="text-slate-400 mt-2">{{ $event->starts_at?->format('d/m/Y H:i') }} @if($event->ends_at) - {{ $event->ends_at->format('d/m/Y H:i') }} @endif</p>
            <p class="text-slate-400">{{ $event->location_name ?: 'Lieu non précisé' }} · {{ $event->city ?: 'Côte d\'Ivoire' }}</p>
            <p class="mt-4 leading-relaxed text-slate-200">{{ $event->description_fr }}</p>
            @if($event->ticket_url)
                <a href="{{ $event->ticket_url }}" target="_blank" class="inline-flex mt-5 bg-amber-500 hover:bg-amber-600 text-black font-semibold px-4 py-2 rounded-lg text-sm">
                    Réserver / Billetterie
                </a>
            @endif
        </div>

        @if($related->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-3">Événements liés</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($related as $r)
                        <a href="{{ route('events.show', $r->slug) }}" class="bg-slate-900 border border-slate-800 rounded-xl p-4 hover:border-amber-500/40 transition">
                            <p class="text-amber-400 text-xs uppercase">{{ $r->category->name_fr ?? 'Événement' }}</p>
                            <p class="font-semibold mt-1">{{ $r->title_fr }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</body>
</html>
