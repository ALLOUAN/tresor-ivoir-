<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $artwork->title }} — Art &amp; Créations — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .field-input { width: 100%; background: #f7f7f5; border: 1.5px solid rgba(20,18,12,0.10); border-radius: 10px; padding: 10px 12px; font-size: 14px; color: #1c1915; }
        .field-input:focus { outline: none; border-color: #f2790f; box-shadow: 0 0 0 3px rgba(242,121,15,0.14); }
        .btn-primary { background: #f2790f; color: #fff; font-weight: 700; }
        .btn-primary:hover { background: #d4630a; }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-28 pb-16">

        @if(session('success'))
            <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                <i class="fas fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <i class="fas fa-circle-exclamation mr-1"></i> {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <div class="rounded-2xl overflow-hidden bg-[#14130f] aspect-square">
                    @if(!empty($artwork->images[0]))
                        <img id="artwork-main-image" src="{{ $artwork->images[0] }}" class="w-full h-full object-cover" alt="{{ $artwork->title }}">
                    @endif
                </div>
                @if(count($artwork->images ?? []) > 1)
                    <div class="grid grid-cols-4 gap-2 mt-2" id="artwork-thumbs">
                        @foreach($artwork->images as $i => $img)
                            <button type="button" data-thumb-src="{{ $img }}"
                                    class="artwork-thumb rounded-lg overflow-hidden aspect-square border-2 transition {{ $i === 0 ? 'border-orange-500' : 'border-transparent' }}">
                                <img src="{{ $img }}" class="w-full h-full object-cover" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <p class="text-[11px] uppercase tracking-wide text-orange-600 font-semibold mb-1">{{ $artwork->category?->name_fr }}</p>
                <h1 class="font-serif text-2xl sm:text-3xl font-bold">{{ $artwork->title }}</h1>
                @if($artwork->provider)
                <a href="{{ route('providers.show', $artwork->provider->slug) }}" class="inline-flex items-center gap-2 text-[#8a7f6b] text-sm mt-1 hover:text-orange-600 transition group">
                    @if($artwork->provider->cover_url || $artwork->provider->logo_url)
                        <img src="{{ $artwork->provider->logo_url ?: $artwork->provider->cover_url }}" alt="" class="w-6 h-6 rounded-full object-cover border border-black/10">
                    @endif
                    Par <span class="font-medium group-hover:underline">{{ $artwork->provider->name }}</span>
                </a>
                @endif

                <p class="text-2xl font-bold mt-4">{{ number_format((int) $artwork->price_xof, 0, ',', ' ') }} XOF</p>

                @if($artwork->description)
                    <p class="text-[#4a4436] text-sm mt-4 leading-relaxed whitespace-pre-line">{{ $artwork->description }}</p>
                @endif

                <div class="grid grid-cols-2 gap-3 mt-5 text-sm">
                    @if($artwork->medium)
                        <div><p class="text-[#8a7f6b] text-xs">Technique</p><p class="font-medium">{{ $artwork->medium }}</p></div>
                    @endif
                    @if($artwork->dimensions)
                        <div><p class="text-[#8a7f6b] text-xs">Dimensions</p><p class="font-medium">{{ $artwork->dimensions }}</p></div>
                    @endif
                    @if($artwork->year_created)
                        <div><p class="text-[#8a7f6b] text-xs">Année</p><p class="font-medium">{{ $artwork->year_created }}</p></div>
                    @endif
                </div>

                <div class="mt-6">
                    @if($artwork->isAvailable())
                        <button type="button" id="btn-acheter"
                                data-uuid="{{ $artwork->uuid }}"
                                data-title="{{ $artwork->title }}"
                                data-price-label="{{ number_format((int) $artwork->price_xof, 0, ',', ' ') }} XOF"
                                data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
                                data-user-name="{{ auth()->check() ? trim(auth()->user()->first_name.' '.auth()->user()->last_name) : '' }}"
                                data-user-email="{{ auth()->user()->email ?? '' }}"
                                data-user-phone="{{ auth()->user()->phone ?? '' }}"
                                class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl transition">
                            <i class="fas fa-cart-plus"></i> Acheter cette œuvre
                        </button>
                    @else
                        <span class="inline-flex items-center gap-2 bg-slate-200 text-slate-500 px-6 py-3 rounded-xl font-semibold">
                            <i class="fas fa-circle-xmark"></i> Œuvre indisponible
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal d'achat --}}
    <div id="modal-achat" class="hidden fixed inset-0 z-[60] p-4 bg-black/50 backdrop-blur-sm items-center justify-center">
        <div id="modal-backdrop" class="absolute inset-0"></div>
        <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="sticky top-0 flex items-center justify-between gap-4 px-5 py-4 border-b border-black/10 bg-white">
                <h2 id="modal-title" class="font-serif text-lg font-semibold">Finaliser votre achat</h2>
                <button id="modal-close" class="w-8 h-8 flex items-center justify-center rounded-full text-[#8a7f6b] hover:bg-black/5"><i class="fas fa-times"></i></button>
            </div>

            <div class="mx-5 mt-4 flex items-center justify-between rounded-xl border border-black/10 bg-[#f7f4ec] px-4 py-3">
                <p id="modal-artwork-title" class="text-sm font-semibold"></p>
                <span id="modal-artwork-price" class="rounded-full bg-[#f2790f] text-white text-xs font-bold px-3 py-1"></span>
            </div>

            <div id="step-register">
                <form id="form-register" class="space-y-3 px-5 pt-4 pb-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="field-label text-xs text-[#6b6355]">Prénom *</label><input type="text" name="first_name" required class="field-input"></div>
                        <div><label class="field-label text-xs text-[#6b6355]">Nom *</label><input type="text" name="last_name" required class="field-input"></div>
                    </div>
                    <div><label class="field-label text-xs text-[#6b6355]">Email *</label><input type="email" name="email" required class="field-input"></div>
                    <div><label class="field-label text-xs text-[#6b6355]">Téléphone *</label><input type="tel" name="phone" required class="field-input"></div>
                    <div><label class="field-label text-xs text-[#6b6355]">Mot de passe *</label><input type="password" name="password" required class="field-input"></div>
                    <div id="register-error" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"></div>
                    <button type="submit" id="btn-submit-register" class="btn-primary w-full py-3 rounded-xl">
                        <span id="btn-register-label">Créer mon compte et continuer</span>
                    </button>
                </form>
            </div>

            <div id="step-summary" class="hidden px-5 pb-5 pt-4 space-y-4">
                <div class="rounded-xl border border-black/10 px-4 py-3 text-sm">
                    <p id="summary-name" class="font-semibold"></p>
                    <p id="summary-email" class="text-[#8a7f6b] text-xs"></p>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-black/10 bg-[#f7f4ec] px-4 py-3">
                    <span class="text-sm font-semibold">Total à payer</span>
                    <span id="summary-total" class="text-lg font-bold text-[#f2790f]"></span>
                </div>
                <div id="pay-error" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"></div>
                <button type="button" id="btn-pay-cinetpay" class="btn-primary w-full py-3 rounded-xl">
                    <span id="btn-pay-label">Payer via CinetPay</span>
                </button>
            </div>
        </div>
    </div>

    @include('partials.homepage-footer')

    <script>
    (function () {
        // Galerie : clic sur une miniature -> affichage dans le grand cadre
        const mainImage = document.getElementById('artwork-main-image');
        const thumbs = document.querySelectorAll('.artwork-thumb');
        if (mainImage && thumbs.length) {
            thumbs.forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    mainImage.src = thumb.dataset.thumbSrc;
                    thumbs.forEach((t) => t.classList.toggle('border-orange-500', t === thumb));
                    thumbs.forEach((t) => t.classList.toggle('border-transparent', t !== thumb));
                });
            });
        }
    })();

    (function () {
        const modal = document.getElementById('modal-achat');
        const btnAcheter = document.getElementById('btn-acheter');
        const btnClose = document.getElementById('modal-close');
        const backdrop = document.getElementById('modal-backdrop');
        const stepReg = document.getElementById('step-register');
        const stepSum = document.getElementById('step-summary');
        const formReg = document.getElementById('form-register');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        let registerUrl = null;
        let payUrl = null;

        function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

        function showStep(step) {
            stepReg.classList.toggle('hidden', step !== 'register');
            stepSum.classList.toggle('hidden', step !== 'summary');
        }

        function fillSummary(name, email, priceLabel) {
            document.getElementById('summary-name').textContent = name;
            document.getElementById('summary-email').textContent = email;
            document.getElementById('summary-total').textContent = priceLabel;
        }

        if (btnAcheter) {
            btnAcheter.addEventListener('click', () => {
                openModal();
                const d = btnAcheter.dataset;
                document.getElementById('modal-artwork-title').textContent = d.title;
                document.getElementById('modal-artwork-price').textContent = d.priceLabel;

                registerUrl = `/art-creations/achat/${d.uuid}/creer-et-payer`;
                payUrl = `/art-creations/achat/${d.uuid}/payer`;

                if (d.authenticated === 'true') {
                    fillSummary(d.userName, d.userEmail, d.priceLabel);
                    showStep('summary');
                } else {
                    showStep('register');
                }
            });
        }

        [btnClose, backdrop].forEach((el) => el?.addEventListener('click', closeModal));

        formReg?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errEl = document.getElementById('register-error');
            const btnLbl = document.getElementById('btn-register-label');
            const btnBtn = document.getElementById('btn-submit-register');
            errEl.classList.add('hidden');
            btnBtn.disabled = true;
            btnLbl.textContent = 'Création en cours…';

            const body = Object.fromEntries(new FormData(formReg));

            try {
                const res = await fetch(registerUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(body),
                });
                const data = await res.json();

                if (data.success && data.payment_url) {
                    window.location.href = data.payment_url;
                    return;
                }

                const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Une erreur est survenue.');
                errEl.textContent = msg;
                errEl.classList.remove('hidden');
            } catch (err) {
                errEl.textContent = 'Erreur réseau. Veuillez réessayer.';
                errEl.classList.remove('hidden');
            }

            btnBtn.disabled = false;
            btnLbl.textContent = 'Créer mon compte et continuer';
        });

        document.getElementById('btn-pay-cinetpay')?.addEventListener('click', async () => {
            const errEl = document.getElementById('pay-error');
            const btnLbl = document.getElementById('btn-pay-label');
            const btnBtn = document.getElementById('btn-pay-cinetpay');
            errEl.classList.add('hidden');
            btnBtn.disabled = true;
            btnLbl.textContent = 'Redirection en cours…';

            try {
                const res = await fetch(payUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({}),
                });
                const data = await res.json();

                if (data.success && data.payment_url) {
                    window.location.href = data.payment_url;
                    return;
                }

                errEl.textContent = data.message || 'Une erreur est survenue.';
                errEl.classList.remove('hidden');
            } catch (err) {
                errEl.textContent = 'Erreur réseau. Veuillez réessayer.';
                errEl.classList.remove('hidden');
            }

            btnBtn.disabled = false;
            btnLbl.textContent = 'Payer via CinetPay';
        });
    })();
    </script>
</body>
</html>
