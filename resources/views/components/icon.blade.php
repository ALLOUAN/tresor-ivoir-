{{--
    Icône Solar « Bold Duotone » (480 Design, CC BY 4.0) en SVG intégré : <x-icon name="bed" />.
    Les tracés viennent de resources/icons/solar.json (sous-ensemble utilisé par le site).
    Rendu dans un <i> : les styles existants des icônes (taille via font-size, couleur, pastille) s'appliquent tels quels.
    Le second ton est le même currentColor à 50 % d'opacité.
--}}
@props(['name'])
@php
    $iconSet = once(fn () => json_decode((string) @file_get_contents(resource_path('icons/solar.json')), true) ?: []);
    $iconBody = $iconSet[$name] ?? null;
@endphp
@once
<style>
    .ico { display: inline-flex; align-items: center; justify-content: center; line-height: 1; font-style: normal; }
    .ico > svg { width: 1.15em; height: 1.15em; display: block; flex: none; }
</style>
@endonce
<i {{ $attributes->merge(['class' => 'ico', 'data-icon' => $name]) }} aria-hidden="true">@if($iconBody)<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">{!! $iconBody !!}</svg>@endif</i>
