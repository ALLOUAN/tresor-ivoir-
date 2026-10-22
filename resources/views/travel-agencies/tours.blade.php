<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Nos circuits') }} — {{ $agency->name }} — {{ $siteBrand['site_name'] }}</title>
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
        'model' => $agency,
        'groups' => $toursByCategory,
        'sectorName' => __('Agences de Voyages & Tours'),
        'sectorIcon' => 'fa-plane-departure',
        'title' => __('Nos circuits'),
        'intro' => __('Choisissez un circuit pour poursuivre votre échange avec l\'agence.'),
        'showRoute' => 'travel-agency.show',
        'bookAnchor' => $agency->provider_id ? '#reserver' : null,
        'bookLabel' => __('Réserver un circuit'),
        'allLabel' => __('Tous les circuits'),
        'itemIcon' => 'fa-route',
        'itemUrl' => fn ($i) => $i->slug ? route('travel-agency.tour', [$agency->slug, $i->slug]) : null,
        'unitLabel' => ':count circuit|:count circuits',
        'emptyText' => __('Aucun circuit renseigné pour cette agence pour le moment.'),
        'meta' => fn ($i) => array_values(array_filter([
            $i->duration_days ? ['fa-calendar-days', trans_choice(':count jour|:count jours', $i->duration_days)] : null,
            $i->max_participants ? ['fa-user', $i->max_participants.' '.__('pers. max')] : null,
        ])),
    ])

@include('partials.homepage-footer')
@include('partials.place-card-script')
@include('partials.image-protection')
</body>
</html>
