{{--
    Paragraphe « lettrine » des articles : grande initiale rouge sur deux lignes et filet vertical rouge à gauche.
    Le rédacteur l'applique à un paragraphe avec le bouton « Lettrine » de l'éditeur (classe p.lettrine).
    Partagé par la page publique, l'aperçu rédacteur et la zone d'édition.
--}}
@once
<style>
    p.lettrine {
        --lettrine: #EB6E00;
        border-left: 3px solid var(--lettrine);
        padding-left: 1.15rem;
    }
    p.lettrine::first-letter {
        float: left;
        font-family: 'Montserrat', Impact, 'Arial Narrow', sans-serif;
        font-weight: 700;
        font-size: 3.5em;
        line-height: .8;
        text-transform: uppercase;
        color: var(--lettrine);
        margin: .1em .14em 0 0;
    }
    /* le premier paragraphe agrandi de l'article garde une lettrine à la même échelle que le texte */
    .prose-content > p.lettrine:first-of-type::first-letter { font-size: 3.1em; }
</style>
@endonce
