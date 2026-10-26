<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Bon cadeau') }} — {{ $provider->name }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => __('Offrez un bon cadeau chez :name', ['name' => $provider->name]), 'description' => __('Un cadeau qui fait plaisir : un bon à utiliser chez :name, envoyé par e-mail en quelques secondes.', ['name' => $provider->name]), 'image' => $provider->cover_url ?: $provider->logo_url])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .gc-card { border: 1px solid rgba(14,59,42,0.07); background: #FFFFFF; box-shadow: 0 10px 28px rgba(14,59,42,0.06); }
        .gc-amount input:checked + span { background: #00874F; color: #fff; border-color: #00874F; }
        .gc-preview { background: #00874F; color: #fff; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
        <a href="{{ route('providers.show', $provider->slug) }}" class="text-sm text-[#557466] hover:text-[#0E3B2A]">← {{ $provider->name }}</a>
        <h1 class="font-serif text-3xl sm:text-4xl font-bold mt-2">{{ __('Offrez un moment chez :name', ['name' => $provider->name]) }}</h1>
        <p class="text-[#557466] mt-2 max-w-2xl">{{ __('Le bénéficiaire reçoit son bon par e-mail, avec un code à présenter sur place. Valable :n mois.', ['n' => \App\Models\GiftCard::VALIDITY_MONTHS]) }}</p>

        @if(session('success'))
            <div class="mt-5 rounded-xl bg-[#00874F]/10 text-[#00874F] font-semibold px-4 py-3"><i class="fas fa-circle-check mr-1"></i> {{ session('success') }}</div>
        @endif
        @if(session('error') || $errors->any())
            <div class="mt-5 rounded-xl bg-red-50 text-red-700 px-4 py-3 text-sm">{{ session('error') ?? $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-6">
            @auth
            <form method="POST" action="{{ route('gift-cards.store', $provider->slug) }}" class="lg:col-span-3 gc-card rounded-2xl p-5 space-y-4" id="gc-form">
                @csrf
                <div>
                    <p class="text-sm font-semibold mb-2">{{ __('Montant') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($amounts as $a)
                            <label class="gc-amount cursor-pointer">
                                <input type="radio" name="amount_choice" value="{{ $a }}" class="sr-only" @checked((int) old('amount_xof', 25000) === $a)>
                                <span class="inline-block px-4 py-2.5 rounded-xl border border-black/10 bg-white font-semibold text-sm">{{ number_format($a, 0, ',', ' ') }} FCFA</span>
                            </label>
                        @endforeach
                    </div>
                    <label class="block text-xs text-[#557466] mt-3">{{ __('Ou un montant libre (FCFA)') }}
                        <input type="number" name="amount_xof" min="5000" step="500" required value="{{ old('amount_xof', 25000) }}" class="mt-1 w-full sm:w-48 bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <input name="recipient_name" required maxlength="255" value="{{ old('recipient_name') }}" placeholder="{{ __('Nom du bénéficiaire') }}" class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                    <input type="email" name="recipient_email" required maxlength="255" value="{{ old('recipient_email') }}" placeholder="{{ __('E-mail du bénéficiaire') }}" class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">
                </div>
                <textarea name="message" rows="3" maxlength="500" placeholder="{{ __('Votre message (facultatif) — ex. Joyeux anniversaire !') }}" class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm">{{ old('message') }}</textarea>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" name="payment_method" value="cinetpay" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#EB6E00] text-white font-semibold"><i class="fas fa-lock"></i> {{ __('Payer et envoyer') }}</button>
                    <button type="submit" name="payment_method" value="wallet" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-black/10 bg-white font-semibold">{{ __('Avec mon solde') }}</button>
                </div>
            </form>
            @else
            <div class="lg:col-span-3 gc-card rounded-2xl p-6 text-center">
                <p class="font-semibold">{{ __('Connectez-vous pour offrir un bon cadeau') }}</p>
                <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="inline-flex items-center gap-2 mt-3 px-5 py-2.5 rounded-xl bg-[#EB6E00] text-white font-semibold text-sm">{{ __('Se connecter') }}</a>
            </div>
            @endauth

            <div class="lg:col-span-2">
                <div class="gc-preview rounded-2xl p-6 shadow-lg">
                    <p class="text-xs uppercase tracking-widest text-[#EB6E00] font-bold"><i class="fas fa-gift mr-1"></i> {{ __('Bon cadeau') }}</p>
                    <p class="font-serif text-2xl font-bold mt-2">{{ $provider->name }}</p>
                    <p class="text-4xl font-extrabold mt-4" id="gc-preview-amount">{{ number_format((int) old('amount_xof', 25000), 0, ',', ' ') }} FCFA</p>
                    <p class="text-white/70 text-sm mt-4 font-mono tracking-widest">XXXX-XXXX-XXXX</p>
                </div>
            </div>
        </div>
    </main>

    @include('partials.homepage-footer')
<script>
(function () {
    const form = document.getElementById('gc-form');
    if (!form) return;
    const input = form.querySelector('[name="amount_xof"]'), preview = document.getElementById('gc-preview-amount');
    const show = () => { preview.textContent = (parseInt(input.value, 10) || 0).toLocaleString('fr-FR') + ' FCFA'; };
    form.querySelectorAll('[name="amount_choice"]').forEach(r => r.addEventListener('change', () => { input.value = r.value; show(); }));
    input.addEventListener('input', show);
})();
</script>
</body>
</html>
