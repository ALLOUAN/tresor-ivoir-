<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Notre carte') }} — {{ $restaurant->name }} — {{ $siteBrand['site_name'] }}</title>
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

    @include('partials.restaurant-menu-page', [
        'restaurant' => $restaurant,
        'groups' => $menuByCategory,
        // Commande en ligne : bouton + et quantités sur chaque plat.
        'orderable' => $restaurant->provider_id && $restaurant->accepts_orders,
    ])

@php $cartDishes = $restaurant->provider_id && $restaurant->accepts_orders ? \App\Models\MenuItem::query()->available()->where('provider_id', $restaurant->provider_id)->with('category')->get() : collect(); @endphp
@include('partials.restaurant-cart', ['restaurant' => $restaurant, 'dishes' => $cartDishes])
@include('partials.homepage-footer')
@include('partials.place-card-script')
@include('partials.image-protection')
</body>
</html>
