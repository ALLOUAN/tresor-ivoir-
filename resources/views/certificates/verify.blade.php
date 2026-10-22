<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ __('Certificat vérifié') }} — {{ $order->certificateNumber() }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; background: #f6f3ed; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; color: #1c1915; }
        .card { max-width: 460px; width: 100%; background: #fff; border: 1px solid #e8e1d3; border-radius: 18px; padding: 28px; box-shadow: 0 20px 50px rgb(29 26 22 / .1); }
        .ok { display: flex; align-items: center; gap: 10px; color: #31621c; font-weight: 700; font-size: 18px; }
        dl { margin: 18px 0 0; display: grid; grid-template-columns: auto 1fr; gap: 8px 14px; font-size: 14px; }
        dt { color: #8a7f6b; } dd { margin: 0; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <p class="ok"><i class="fas fa-circle-check"></i> {{ __('Certificat authentique') }}</p>
        <p style="color:#5c5548; font-size:14px; margin-top:6px;">{{ __('Cette œuvre a bien été vendue par son artiste sur Trésors d\'Ivoire.') }}</p>
        <dl>
            <dt>{{ __('N°') }}</dt><dd>{{ $order->certificateNumber() }}</dd>
            <dt>{{ __('Œuvre') }}</dt><dd>{{ $order->artwork?->title }}@if($order->edition_label) — {{ __('édition « :l », exemplaire :n/:t', ['l' => $order->edition_label, 'n' => $order->edition_number, 't' => $order->edition_size]) }}@endif</dd>
            <dt>{{ __('Artiste') }}</dt><dd>{{ $order->provider?->name }}</dd>
            <dt>{{ __('Technique') }}</dt><dd>{{ $order->artwork?->medium ?: '—' }}</dd>
            <dt>{{ __('Vendue le') }}</dt><dd>{{ ($order->paid_at ?? $order->created_at)->format('d/m/Y') }}</dd>
        </dl>
    </div>
</body>
</html>
