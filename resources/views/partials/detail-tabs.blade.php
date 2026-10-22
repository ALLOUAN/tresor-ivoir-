{{--
    Navigation par ancres des fiches établissement. $offerLabel : libellé de la section propre
    au secteur (carte, activités, circuits, offres). Les liens sans section correspondante sont retirés.
--}}
<nav class="hx-tabs" id="dx-tabs" aria-label="{{ __('Sections de la fiche') }}">
    <a href="#apercu">{{ __('Aperçu') }}</a>
    <a href="#equipements">{{ __('Équipements') }}</a>
    <a href="#offre">{{ $offerLabel }}</a>
    <a href="#localisation">{{ __('Localisation') }}</a>
    <a href="#avis">{{ __('Avis') }}</a>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#dx-tabs a').forEach(function (a) {
        if (!document.getElementById(a.getAttribute('href').slice(1))) a.remove();
    });
});
</script>
