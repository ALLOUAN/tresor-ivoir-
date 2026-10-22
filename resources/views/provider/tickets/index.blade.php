@extends('layouts.app')

@section('title', 'Contrôle des billets')
@section('page-title', 'Contrôle des billets')

@section('content')
<div class="max-w-xl space-y-5">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold mb-1"><i class="fas fa-qrcode text-orange-400 mr-1"></i> Scanner un billet</h2>
        <p class="text-slate-400 text-sm mb-4">Scannez le QR code du visiteur avec ce téléphone, ou saisissez le code imprimé sous le QR.</p>
        <div id="scan-box" class="hidden mb-3 rounded-xl overflow-hidden bg-black">
            <video id="scan-video" class="w-full max-h-80 object-cover" playsinline muted></video>
        </div>
        <button type="button" id="scan-start" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2.5 rounded-lg mb-3">
            <i class="fas fa-camera"></i> Ouvrir la caméra
        </button>
        <p id="scan-msg" class="text-xs text-slate-400 mb-3"></p>
        <form method="GET" action="{{ route('provider.tickets.index') }}" class="flex gap-2">
            <input name="code" required maxlength="200" placeholder="Code du billet (ex. 7KQ2M9XA4B)" autocomplete="off" autocapitalize="characters"
                   class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 uppercase">
            <button class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Vérifier</button>
        </form>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold mb-3">Visiteurs attendus aujourd'hui</h2>
        @forelse($today as $v)
            <div class="flex items-center justify-between py-2 border-b border-slate-800 last:border-0 text-sm">
                <span class="text-slate-200">{{ $v->full_name }} <span class="text-slate-500">· {{ $v->participants_count }} pers.</span></span>
                @if($v->checked_in_at)
                    <span class="text-emerald-300 text-xs"><i class="fas fa-check"></i> {{ $v->checked_in_at->format('H:i') }}</span>
                @else
                    <span class="text-slate-500 text-xs">En attente</span>
                @endif
            </div>
        @empty
            <p class="text-slate-400 text-sm">Aucune visite payée prévue aujourd'hui.</p>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
(function () {
    const btn = document.getElementById('scan-start'), box = document.getElementById('scan-box'), video = document.getElementById('scan-video'), msg = document.getElementById('scan-msg');
    btn.addEventListener('click', async () => {
        if (!('BarcodeDetector' in window)) {
            msg.textContent = "Ce navigateur ne lit pas les QR codes : utilisez l'appareil photo du téléphone (il ouvre le lien du billet) ou saisissez le code.";
            return;
        }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream; await video.play();
            box.classList.remove('hidden'); btn.classList.add('hidden');
            const detector = new BarcodeDetector({ formats: ['qr_code'] });
            const tick = async () => {
                const codes = await detector.detect(video).catch(() => []);
                if (codes.length) {
                    stream.getTracks().forEach(t => t.stop());
                    window.location = @json(route('provider.tickets.index')) + '?code=' + encodeURIComponent(codes[0].rawValue);
                    return;
                }
                requestAnimationFrame(tick);
            };
            tick();
        } catch (e) {
            msg.textContent = 'Caméra indisponible ou refusée : saisissez le code du billet.';
        }
    });
})();
</script>
@endpush
@endsection
