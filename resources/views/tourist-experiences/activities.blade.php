<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Activités proposées') }} — {{ $experience->name }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
    </style>
    @include('partials.detail-theme')
    @include('partials.catalog-theme')
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    @include('partials.catalog-page', [
        'model' => $experience,
        'groups' => $activitiesByCategory,
        'sectorName' => __('Sites Touristiques'),
        'sectorIcon' => 'fa-mountain-sun',
        'title' => __('Activités proposées'),
        'intro' => __('Choisissez une activité pour poursuivre votre échange avec le prestataire.'),
        'showRoute' => 'tourist-experience.show',
        'bookAnchor' => '#tvp-booking-module',
        'bookLabel' => __('Réserver une visite'),
        'allLabel' => __('Toutes les activités'),
        'itemIcon' => 'fa-person-hiking',
        'itemUrl' => fn ($i) => $i->slug ? route('activity.show', $i->slug) : null,
        'itemCta' => __('Voir et réserver'),
        'unitLabel' => ':count activité|:count activités',
        'emptyText' => __('Aucune activité renseignée pour ce site pour le moment.'),
        'meta' => fn ($i) => array_values(array_filter([
            $i->duration_minutes ? ['fa-clock', $i->duration_minutes.' '.__('min')] : null,
            $i->max_participants ? ['fa-user', $i->max_participants.' '.__('pers. max')] : null,
        ])),
    ])

@include('partials.homepage-footer')
@include('partials.place-card-script')
@include('partials.image-protection')
</body>
</html>
