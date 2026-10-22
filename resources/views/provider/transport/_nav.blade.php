@if(session('success'))<div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ session('error') }}</div>@endif
@if($errors->any())<div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>@endif
<nav class="flex flex-wrap gap-2 mb-5" aria-label="Transport">
    @foreach([
        ['provider.transport-bookings.index', 'fa-calendar-check', 'Planning'],
        ['provider.transport.index', 'fa-tags', 'Offres et tarifs'],
        ['provider.transport-fleet.index', 'fa-car', 'Flotte et chauffeurs'],
        ['provider.transport-trips.index', 'fa-bus', 'Lignes'],
        ['provider.tickets.index', 'fa-qrcode', 'Scanner'],
    ] as [$r, $i, $l])
        <a href="{{ route($r) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm border {{ request()->routeIs($r) ? 'bg-orange-500 border-orange-500 text-black font-semibold' : 'border-slate-700 text-slate-300 hover:text-white' }}"><i class="fas {{ $i }} text-xs"></i>{{ $l }}</a>
    @endforeach
</nav>
