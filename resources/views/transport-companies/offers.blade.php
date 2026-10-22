<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Nos offres de transport') }} — {{ $company->name }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
    @include('partials.detail-theme')
    @include('partials.catalog-theme')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    @include('partials.catalog-page', [
        'model' => $company,
        'groups' => $offersByCategory,
        'sectorName' => __('Transports & Mobilité'),
        'sectorIcon' => 'fa-van-shuttle',
        'title' => __('Nos offres de transport'),
        'intro' => __('Choisissez une offre pour poursuivre votre échange avec l\'entreprise.'),
        'showRoute' => 'transport-company.show',
        'bookAnchor' => $company->provider_id ? '#reserver' : null,
        'bookLabel' => __('Réserver un transport'),
        'allLabel' => __('Toutes les offres'),
        'itemIcon' => 'fa-car',
        'itemUrl' => fn ($i) => $i->slug && $i->mode ? route('transport-offer.show', $i->slug) : null,
        'itemCta' => __('Voir et réserver'),
        'unitLabel' => ':count offre|:count offres',
        'emptyText' => __('Aucune offre renseignée pour cette entreprise pour le moment.'),
        'meta' => fn ($i) => array_values(array_filter([
            $i->max_passengers ? ['fa-user', trans_choice(':count passager max|:count passagers max', $i->max_passengers)] : null,
        ])),
    ])

@include('partials.homepage-footer')
@include('partials.place-card-script')
@include('partials.image-protection')
</body>
</html>
