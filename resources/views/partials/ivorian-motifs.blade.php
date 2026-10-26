{{--
    Fond culturel ivoirien (réutilisable), à placer dans un conteneur en position: relative.
    Paramètres : $culture (clé d'aire de App\Support\IvorianCulture : senoufo, baoule, akan, lagunaire, krou-cote,
    krou, dan, gouro, mande, lobi, national), $band (bool, défaut true : lisière tissée en bas).
    Tracés au trait, en aplat très clair (pas de dégradé), décoratifs (aria-hidden).
--}}
@php
    $miBand = $band ?? true;
    // Filigrane du nom du site en lettres creuses (désactivable : 'mark' => false).
    $miMark = $mark ?? true;
    $miKey = $culture ?? 'senoufo';
    if ($miKey === 'national') $miKey = 'senoufo';
    if ($miKey === 'krou-cote') $miKey = 'lagunaire';
    // Identifiant unique par inclusion : avec plusieurs motifs sur une page, un id partagé ferait réutiliser
    // le premier motif (et ses couleurs) partout.
    $miId = 'ivm-'.$miKey.'-'.substr(md5(uniqid('', true)), 0, 6);
@endphp
@once
<style>
    /* Opacité globale réduite : s'applique en plus des réglages propres à chaque page. */
    .ivm { position: absolute; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; opacity: .55; container-type: size; }
    .ivm svg { display: block; width: 100%; height: 100%; }
    /* Nom en lettres creuses, d'après le logo de la charte : « TRÉSORS » vert, « D'IVOIRE » orange, Montserrat 800 espacé.
       Posé en haut du bloc et coupé par le bord (les cartes qui chevauchent le bas des bandeaux le cachaient). Sur fond sombre (.is-dark), « TRÉSORS » passe en blanc. */
    .ivm-mark { position: absolute; left: 0; right: 0; top: 0; transform: translateY(-26%); margin: 0; text-align: center; white-space: nowrap; overflow: hidden;
        font-family: 'Montserrat', sans-serif; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; line-height: 1;
        font-size: min(6.8cqw, 34cqh); -webkit-text-fill-color: transparent; opacity: .32; user-select: none; }
    .ivm-mark span { -webkit-text-stroke: 1.5px #00874F; }
    .ivm-mark span + span { -webkit-text-stroke-color: #EB6E00; }
    .ivm-mark.is-dark { opacity: .26; }
    .ivm-mark.is-dark span:first-child { -webkit-text-stroke-color: #FFFFFF; }
    @supports not (font-size: 1cqw) { .ivm-mark { font-size: 6.4vw; } }
    /* Bandes basses (en-tête, frises) : pas de filigrane. */
    @container (max-height: 160px) { .ivm-mark { display: none; } }
    /* Couleurs des motifs (palette du site, aplats) : orange terre cuite, vert forêt, ocre, brun ; points pleins assortis. */
    .ivm-g > :nth-child(4n+1) { stroke: #EB6E00; }
    .ivm-g > :nth-child(4n+2) { stroke: #00874F; }
    .ivm-g > :nth-child(4n+3) { stroke: #EB6E00; }
    .ivm-g > :nth-child(4n+4) { stroke: #B85600; }
    .ivm-g > circle[fill] { fill: #EB6E00; stroke: #EB6E00; }
    .ivm-band { position: absolute; left: 0; right: 0; bottom: 0; height: 16px; z-index: 1; pointer-events: none; }
    .ivm-band svg { display: block; width: 100%; height: 100%; }
    @media (max-width: 640px) {
        .ivm-band { height: 12px; }
        /* Mobile : motifs réduits (~60 %) pour que des figures entières tiennent dans les bandes, et plus marqués */
        .ivm svg { width: 166.67%; height: 166.67%; transform: scale(.6); transform-origin: 0 0; }
        .ivm-g { opacity: .62; stroke-width: 2.8px; }
    }
</style>
<script>
// Filigrane : sur un fond sombre (premier fond opaque trouvé en remontant), « TRÉSORS » passe en blanc.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ivm-mark').forEach(function (m) {
        for (var el = m.parentElement; el; el = el.parentElement) {
            var c = getComputedStyle(el).backgroundColor.match(/[\d.]+/g);
            if (!c || (c.length > 3 && +c[3] < 0.5)) continue;
            if ((0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]) / 255 < 0.5) m.classList.add('is-dark');
            break;
        }
    });
});
</script>
@endonce
<div class="ivm" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="{{ $miId }}" width="260" height="260" patternUnits="userSpaceOnUse">
                <g class="ivm-g" fill="none" stroke="#B85600" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" opacity=".42">
                @switch($miKey)
                    @case('baoule')
                        {{-- Masque Goli (disque à cornes) --}}
                        <circle cx="60" cy="70" r="26"/>
                        <path d="M40 50 q-8 -26 6 -36 M80 50 q8 -26 -6 -36"/>
                        <path d="M48 64 h8 M64 64 h8 M54 82 h12"/>
                        <path d="M60 96 v14 M50 110 h20"/>
                        {{-- Poids à peser l'or (spirale) --}}
                        <path d="M190 60 m0 0 a6 6 0 1 1 6 6 a12 12 0 1 1 -12 -12 a18 18 0 1 1 18 18"/>
                        <rect x="168" y="38" width="44" height="44" rx="6"/>
                        {{-- Siège --}}
                        <path d="M30 180 q30 -14 60 0 M38 182 v24 M82 182 v24 M30 206 h60 M50 186 v18 M70 186 v18"/>
                        {{-- Damier du kita --}}
                        <path d="M150 170 h80 v40 h-80 z M170 170 v40 M190 170 v40 M210 170 v40 M150 190 h80"/>
                        <path d="M155 175 l10 10 M195 195 l10 10 M175 195 l10 10 M215 175 l10 10"/>
                        <path d="M10 130 h240" stroke-dasharray="2 10"/>
                        @break
                    @case('akan')
                        {{-- Sankofa (cœur à spirales) --}}
                        <path d="M60 90 q-34 -22 -20 -44 q12 -16 28 -2 q8 -12 20 -6 q16 10 -4 30 q-10 10 -24 22 z"/>
                        <path d="M48 50 q-6 10 4 14 M76 50 q8 8 -2 14"/>
                        {{-- Dwennimmen (cornes de bélier) --}}
                        <path d="M170 40 q-24 0 -24 22 q0 14 14 14 q10 0 10 -10 M190 40 q24 0 24 22 q0 14 -14 14 q-10 0 -10 -10"/>
                        <path d="M170 104 q-24 0 -24 -22 M190 104 q24 0 24 -22"/>
                        <path d="M170 40 h20 M170 104 h20"/>
                        {{-- Siège royal akan --}}
                        <path d="M28 196 q32 -18 64 0 M44 196 v-0 M40 198 q0 10 8 14 h24 q8 -4 8 -14 M48 212 v14 M72 212 v14 M32 226 h56"/>
                        {{-- Nkyinkyim (zigzag) --}}
                        <path d="M140 170 l14 14 l14 -14 l14 14 l14 -14 l14 14 l14 -14"/>
                        <path d="M140 200 l14 14 l14 -14 l14 14 l14 -14 l14 14 l14 -14"/>
                        <circle cx="236" cy="236" r="6"/><circle cx="236" cy="236" r="2" fill="#B85600"/>
                        @break
                    @case('lagunaire')
                        {{-- Pirogue et pagayeur --}}
                        <path d="M20 90 q60 26 120 0 q-60 10 -120 0 z"/>
                        <circle cx="80" cy="56" r="5"/><path d="M80 62 v18 M80 68 l16 -8 M96 60 l12 34"/>
                        {{-- Vagues --}}
                        <path d="M10 124 q15 -12 30 0 t30 0 t30 0 t30 0 t30 0 t30 0 t30 0 t30 0"/>
                        <path d="M10 140 q15 -12 30 0 t30 0 t30 0 t30 0 t30 0 t30 0 t30 0 t30 0"/>
                        {{-- Poisson --}}
                        <path d="M170 50 q22 -16 46 0 q-24 16 -46 0 z M216 50 l12 -9 v18 z"/>
                        {{-- Filet --}}
                        <path d="M30 170 l40 40 l40 -40 M50 170 l40 40 M90 170 l-40 40 M30 170 h80 M70 210 v20"/>
                        {{-- Cocotier --}}
                        <path d="M190 236 q6 -40 0 -70 M190 166 q-24 -6 -34 8 M190 166 q24 -6 34 8 M190 166 q-14 -18 -32 -16 M190 166 q14 -18 32 -16"/>
                        {{-- Coquillage --}}
                        <path d="M236 110 a10 10 0 1 1 -10 -10 a6 6 0 1 1 6 6"/>
                        @break
                    @case('krou')
                        {{-- Palmier à huile --}}
                        <path d="M60 120 q-4 -40 0 -76 M60 44 q-26 -8 -40 6 M60 44 q26 -8 40 6 M60 44 q-18 -20 -36 -16 M60 44 q18 -20 36 -16 M60 44 v-16"/>
                        {{-- Tambour --}}
                        <path d="M170 40 h44 l-8 50 h-28 z M178 90 l-6 20 h40 l-6 -20 M170 40 q22 -10 44 0"/>
                        <path d="M176 56 l32 0 M180 72 h24"/>
                        {{-- Cabosse --}}
                        <path d="M60 170 q24 0 28 30 q-4 30 -28 30 q-24 0 -28 -30 q4 -30 28 -30 z M60 170 v60 M46 176 q-6 24 0 48 M74 176 q6 24 0 48"/>
                        {{-- Feuille --}}
                        <path d="M150 210 q40 -50 90 -40 q-30 44 -90 40 z M150 210 l70 -32"/>
                        <path d="M10 140 h240" stroke-dasharray="2 10"/>
                        @break
                    @case('dan')
                        {{-- Masque dan (visage ovale, yeux fendus) --}}
                        <path d="M60 30 q26 0 28 40 q0 34 -28 46 q-28 -12 -28 -46 q2 -40 28 -40 z"/>
                        <path d="M44 62 q8 -4 14 0 M62 62 q8 -4 14 0 M60 70 v14 M50 94 q10 6 20 0"/>
                        {{-- Masque wè (protubérances) --}}
                        <circle cx="190" cy="70" r="28"/>
                        <circle cx="178" cy="64" r="6"/><circle cx="202" cy="64" r="6"/>
                        <path d="M184 86 h12 M162 50 l-12 -12 M218 50 l12 -12 M190 42 v-14"/>
                        {{-- Échassier --}}
                        <circle cx="60" cy="150" r="7"/>
                        <path d="M48 160 h24 l8 30 h-40 z M52 190 l-4 60 M68 190 l4 60 M48 168 l-14 10 M72 168 l14 10"/>
                        {{-- Dent de Man --}}
                        <path d="M130 240 l30 -54 l12 14 l14 -40 l16 30 l14 -12 l34 62"/>
                        <path d="M10 130 h100" stroke-dasharray="2 10"/>
                        @break
                    @case('gouro')
                        {{-- Masque du Zaouli : visage et cimier --}}
                        <path d="M60 60 q22 0 24 34 q0 26 -24 36 q-24 -10 -24 -36 q2 -34 24 -34 z"/>
                        <path d="M48 90 q6 -4 10 0 M62 90 q6 -4 10 0 M54 112 h12"/>
                        <path d="M36 60 h48 l-8 -24 h-32 z M44 36 l-8 -20 M76 36 l8 -20 M60 36 v-22"/>
                        {{-- Frises de bandes --}}
                        <path d="M140 40 h100 M140 52 h100 M140 64 h100" stroke-dasharray="14 6"/>
                        <path d="M140 90 l12 -12 l12 12 l12 -12 l12 12 l12 -12 l12 12 l12 -12 l12 12"/>
                        {{-- Oiseau stylisé du cimier --}}
                        <path d="M40 200 q20 -20 50 -8 l20 -6 l-16 14 q-24 18 -54 0 z"/>
                        <circle cx="200" cy="200" r="22"/><path d="M178 200 h44 M200 178 v44"/>
                        @break
                    @case('mande')
                        {{-- Mosquée de style soudanais (tours et torons) --}}
                        <path d="M30 130 v-60 l10 -16 l10 16 v-10 h30 v10 l10 -16 l10 16 v60 z"/>
                        <path d="M24 84 h12 M24 104 h12 M84 84 h12 M84 104 h12 M50 76 v-6 M70 76 v-6"/>
                        <path d="M54 130 v-20 q6 -8 12 0 v20"/>
                        <path d="M40 54 v-10 M100 54 v-10"/>
                        {{-- Calebasse gravée --}}
                        <path d="M150 70 a40 40 0 0 0 80 0 z M160 80 l10 10 l10 -10 l10 10 l10 -10 l10 10 l10 -10"/>
                        {{-- Losanges --}}
                        <path d="M20 190 l20 -20 l20 20 l-20 20 z M70 190 l20 -20 l20 20 l-20 20 z M120 190 l20 -20 l20 20 l-20 20 z"/>
                        <path d="M36 190 h8 M86 190 h8 M136 190 h8"/>
                        {{-- Étoile --}}
                        <path d="M210 170 l6 16 h16 l-13 10 l5 16 l-14 -10 l-14 10 l5 -16 l-13 -10 h16 z"/>
                        @break
                    @case('lobi')
                        {{-- Antilope --}}
                        <path d="M30 80 h50 q10 0 12 -10 l8 -14 M100 56 l-6 -26 M100 56 l6 -24 M36 80 v26 M74 80 v26 M30 80 l-8 -8"/>
                        {{-- Éléphant --}}
                        <path d="M150 90 q0 -40 40 -40 q36 0 36 36 v20 M226 86 q14 4 10 30 M160 90 v20 M200 90 v20 M150 90 h60"/>
                        <circle cx="210" cy="70" r="3" fill="#B85600"/>
                        {{-- Bateba (statuette aux bras levés) --}}
                        <circle cx="60" cy="160" r="9"/>
                        <path d="M60 170 v40 M60 178 l-18 -18 M60 178 l18 -18 M60 210 l-10 30 M60 210 l10 30"/>
                        {{-- Arc et flèche --}}
                        <path d="M150 230 q40 -70 90 -60 M150 230 l90 -60 M232 166 l10 2 l-4 9"/>
                        <path d="M10 130 h240" stroke-dasharray="2 10"/>
                        @break
                    {{-- ── Secteurs d'activité (bandeaux des listes) ── --}}
                    @case('hotels')
                        {{-- Case au toit de paille --}}
                        <path d="M26 92 l34 -34 l34 34 z"/>
                        <path d="M40 120 v-28 h40 v28"/>
                        <path d="M54 120 v-16 h12 v16"/>
                        <path d="M38 80 l8 -6 M50 70 l8 -6 M70 70 l8 6 M82 80 l-6 -4"/>
                        {{-- Clé --}}
                        <circle cx="180" cy="50" r="11"/>
                        <path d="M191 50 h40 M220 50 v9 M230 50 v11"/>
                        {{-- Lit --}}
                        <path d="M26 214 h84 M26 196 v26 M110 204 v18 M26 204 h84"/>
                        <path d="M32 204 q8 -14 22 0"/>
                        {{-- Palmier --}}
                        <path d="M196 236 q-3 -32 2 -60 M198 176 q-18 -6 -30 6 M198 176 q18 -6 30 6 M198 176 q-12 -16 -26 -14 M198 176 q12 -16 26 -14"/>
                        {{-- Soleil --}}
                        <circle cx="128" cy="140" r="9"/>
                        <path d="M128 122 v-6 M128 158 v6 M110 140 h-6 M146 140 h6"/>
                        @break
                    @case('restaurants')
                        {{-- Canari (marmite en terre) --}}
                        <path d="M40 74 q-18 34 20 46 q38 -12 20 -46 z"/>
                        <path d="M42 74 h36 M46 66 h28"/>
                        <path d="M48 96 l6 6 l6 -6 l6 6 l6 -6"/>
                        {{-- Calebasse --}}
                        <path d="M150 56 a36 36 0 0 0 72 0 z"/>
                        <path d="M160 64 l8 8 l8 -8 l8 8 l8 -8 l8 8 l8 -8"/>
                        {{-- Poisson braisé --}}
                        <path d="M140 160 q24 -18 52 0 q-28 18 -52 0 z M192 160 l14 -10 v20 z"/>
                        <path d="M154 150 l6 20 M166 148 l6 24 M178 150 l6 20"/>
                        {{-- Louche --}}
                        <path d="M50 170 v32"/><circle cx="50" cy="214" r="12"/>
                        {{-- Piment --}}
                        <path d="M200 206 q22 -10 32 10 q-10 28 -42 30 q12 -18 10 -40"/>
                        <path d="M214 204 q2 -10 10 -12"/>
                        @break
                    @case('experiences')
                        {{-- Montagne --}}
                        <path d="M18 120 l40 -62 l20 26 l14 -18 l38 54 z"/>
                        <path d="M48 74 l10 8 l8 -6"/>
                        {{-- Boussole --}}
                        <circle cx="192" cy="62" r="28"/>
                        <path d="M192 40 l7 22 l-7 22 l-7 -22 z"/>
                        <path d="M192 28 v6 M192 90 v6 M158 62 h6 M220 62 h6"/>
                        {{-- Jumelles --}}
                        <circle cx="46" cy="200" r="15"/><circle cx="84" cy="200" r="15"/>
                        <path d="M61 196 h8 M40 185 v-10 h12 v10 M78 185 v-10 h12 v10"/>
                        {{-- Sentier --}}
                        <path d="M140 236 q30 -50 104 -36" stroke-dasharray="4 8"/>
                        <path d="M150 170 l8 -10 l8 10 M200 176 l6 -8 l6 8"/>
                        @break
                    @case('agences')
                        {{-- Avion --}}
                        <path d="M24 74 l70 -22 l-8 10 l-28 8 l-14 22 h-8 l4 -18 l-16 4 z"/>
                        {{-- Valise --}}
                        <rect x="160" y="50" width="60" height="44" rx="7"/>
                        <path d="M178 50 v-8 h24 v8 M176 50 v44 M204 50 v44"/>
                        {{-- Épingle de carte --}}
                        <path d="M60 214 q-26 -30 -14 -46 q14 -16 28 0 q12 16 -14 46 z"/>
                        <circle cx="60" cy="180" r="6"/>
                        {{-- Itinéraire --}}
                        <path d="M120 232 q34 -64 120 -52" stroke-dasharray="3 9"/>
                        <circle cx="240" cy="180" r="4"/><circle cx="120" cy="232" r="4"/>
                        @break
                    @case('loisirs')
                        {{-- Djembé --}}
                        <path d="M38 56 h46 l-10 34 l10 32 h-46 l10 -32 z"/>
                        <path d="M42 56 l20 34 M80 56 l-20 34 M48 90 h26"/>
                        {{-- Masque --}}
                        <path d="M190 30 q24 0 26 36 q0 30 -26 42 q-26 -12 -26 -42 q2 -36 26 -36 z"/>
                        <path d="M176 60 q6 -4 12 0 M192 60 q6 -4 12 0 M190 68 v12 M182 92 q8 6 16 0"/>
                        {{-- Notes de musique --}}
                        <path d="M152 210 v-34 l32 -8 v34"/>
                        <circle cx="146" cy="212" r="7"/><circle cx="178" cy="204" r="7"/>
                        {{-- Étoile --}}
                        <path d="M60 172 l7 18 h18 l-14 11 l6 18 l-17 -11 l-17 11 l6 -18 l-14 -11 h18 z"/>
                        @break
                    @case('transports')
                        {{-- Gbaka (minibus) --}}
                        <rect x="20" y="48" width="96" height="42" rx="9"/>
                        <path d="M30 58 h18 v14 h-18 z M56 58 h18 v14 h-18 z M82 58 h18 v14 h-18 z"/>
                        <circle cx="42" cy="94" r="9"/><circle cx="96" cy="94" r="9"/>
                        {{-- Pirogue --}}
                        <path d="M150 74 q48 22 96 0 q-48 8 -96 0 z"/>
                        <circle cx="196" cy="46" r="5"/><path d="M196 52 v16 M196 58 l14 -6 M210 52 l10 30"/>
                        {{-- Roue --}}
                        <circle cx="60" cy="190" r="24"/><circle cx="60" cy="190" r="5"/>
                        <path d="M60 166 v48 M36 190 h48 M43 173 l34 34 M77 173 l-34 34"/>
                        {{-- Route --}}
                        <path d="M130 236 l110 -64 M146 240 l100 -58"/>
                        <path d="M140 234 l104 -60" stroke-dasharray="8 8"/>
                        @break
                    @case('newsletter')
                        {{-- Enveloppe --}}
                        <rect x="24" y="44" width="80" height="54" rx="6"/>
                        <path d="M24 50 l40 30 l40 -30"/>
                        {{-- Avion en papier --}}
                        <path d="M150 90 l90 -46 l-30 70 l-18 -20 z M192 94 l18 -50"/>
                        <path d="M140 108 q-14 10 -6 26" stroke-dasharray="3 6"/>
                        {{-- Arobase --}}
                        <circle cx="60" cy="190" r="10"/>
                        <path d="M70 190 v6 q0 8 8 8 q10 0 10 -14 a28 28 0 1 0 -12 23"/>
                        {{-- Lettre et cachet --}}
                        <rect x="160" y="160" width="64" height="70" rx="4"/>
                        <path d="M172 176 h40 M172 188 h40 M172 200 h26"/>
                        <circle cx="212" cy="216" r="8"/>
                        @break
                    @case('bd')
                        {{-- Bulle de dialogue --}}
                        <path d="M28 40 h84 q10 0 10 10 v38 q0 10 -10 10 h-50 l-18 18 v-18 h-16 q-10 0 -10 -10 v-38 q0 -10 10 -10 z"/>
                        <path d="M44 62 h52 M44 76 h34"/>
                        {{-- Étoile « pop » --}}
                        <path d="M196 40 l9 22 l23 -6 l-14 19 l19 14 l-24 3 l2 24 l-17 -17 l-17 17 l2 -24 l-24 -3 l19 -14 l-14 -19 l23 6 z"/>
                        {{-- Crayon --}}
                        <path d="M40 232 l70 -70 l16 16 l-70 70 l-22 6 z M102 170 l16 16"/>
                        {{-- Livre ouvert --}}
                        <path d="M150 168 q30 -12 48 4 q18 -16 48 -4 v62 q-30 -12 -48 4 q-18 -16 -48 -4 z M198 172 v62"/>
                        @break
                    @case('evenements')
                        {{-- Feuille de calendrier --}}
                        <rect x="24" y="40" width="66" height="62" rx="8"/>
                        <path d="M24 58 h66 M42 32 v14 M72 32 v14"/>
                        <path d="M38 72 h8 M54 72 h8 M70 72 h8 M38 86 h8 M54 86 h8"/>
                        {{-- Billet --}}
                        <path d="M150 46 h80 v14 a8 8 0 0 0 0 16 v14 h-80 v-14 a8 8 0 0 0 0 -16 z"/>
                        <path d="M178 46 v44" stroke-dasharray="3 5"/>
                        {{-- Tambour parleur --}}
                        <path d="M40 160 q20 -10 40 0 v44 q-20 10 -40 0 z M40 160 q20 10 40 0"/>
                        <path d="M44 172 l32 20 M76 172 l-32 20 M88 150 l14 -14"/>
                        {{-- Guirlande de fête --}}
                        <path d="M130 134 q60 22 120 0"/>
                        <path d="M146 140 l6 14 l6 -12 M176 146 l6 14 l6 -14 M206 146 l6 14 l6 -14 M232 140 l6 12 l4 -14"/>
                        {{-- Feu d'artifice --}}
                        <circle cx="196" cy="206" r="4"/>
                        <path d="M196 186 v-10 M196 226 v10 M176 206 h-10 M216 206 h10 M182 192 l-7 -7 M210 192 l7 -7 M182 220 l-7 7 M210 220 l7 7"/>
                        @break
                    @case('magazine')
                        {{-- Plume --}}
                        <path d="M30 110 q20 -60 70 -84 q-10 40 -50 70 z M30 110 l40 -50"/>
                        {{-- Livre ouvert --}}
                        <path d="M150 56 q24 -10 44 0 v46 q-20 -10 -44 0 z M194 56 q24 -10 44 0 v46 q-20 -10 -44 0"/>
                        <path d="M158 66 h26 M158 76 h26 M202 66 h26 M202 76 h26"/>
                        {{-- Journal --}}
                        <rect x="22" y="160" width="76" height="60" rx="4"/>
                        <path d="M32 172 h56 M32 184 h26 M32 194 h26 M32 204 h26 M66 184 h22 v20 h-22 z"/>
                        {{-- Appareil photo --}}
                        <rect x="150" y="170" width="64" height="44" rx="8"/>
                        <circle cx="182" cy="192" r="12"/>
                        <path d="M166 170 l6 -10 h20 l6 10"/>
                        {{-- Guillemets --}}
                        <path d="M118 128 q-10 4 -10 16 h10 v-10 h-6 M134 128 q-10 4 -10 16 h10 v-10 h-6"/>
                        {{-- Micro --}}
                        <rect x="232" y="196" width="14" height="22" rx="7"/>
                        <path d="M226 210 q0 14 13 14 q13 0 13 -14 M239 224 v10"/>
                        @break
                    @case('cultures')
                        {{-- Masque Goli (Baoulé) --}}
                        <circle cx="56" cy="62" r="24"/>
                        <path d="M38 44 q-8 -24 6 -32 M74 44 q8 -24 -6 -32 M46 58 h8 M60 58 h8 M50 74 h12"/>
                        {{-- Masque dan --}}
                        <path d="M190 26 q22 0 24 34 q0 28 -24 40 q-24 -12 -24 -40 q2 -34 24 -34 z"/>
                        <path d="M178 54 q6 -4 11 0 M193 54 q6 -4 11 0 M190 62 v12 M182 84 q8 5 16 0"/>
                        {{-- Calao (Sénoufo) --}}
                        <path d="M24 186 q14 -20 38 -12 q10 4 8 12 q-2 10 -16 12 q-20 2 -30 -12 z M66 178 l24 -6 l-22 12"/>
                        <circle cx="60" cy="180" r="2" fill="#B85600"/>
                        <path d="M38 196 l-4 14 M50 197 l2 14"/>
                        {{-- Sankofa (Akan) --}}
                        <path d="M190 214 q-30 -20 -18 -40 q11 -14 25 -2 q7 -11 18 -5 q14 9 -4 27 q-9 9 -21 20 z"/>
                        {{-- Djembé --}}
                        <path d="M116 128 h30 l-7 22 l7 20 h-30 l7 -20 z M120 128 l13 22 M142 128 l-13 22"/>
                        {{-- Frise de chevrons --}}
                        <path d="M8 124 l10 -8 l10 8 l10 -8 l10 8 l10 -8 l10 8"/>
                        <path d="M184 128 l10 -8 l10 8 l10 -8 l10 8 l10 -8 l10 8"/>
                        @break
                    @case('regions')
                        {{-- Rose des vents --}}
                        <circle cx="60" cy="62" r="26"/>
                        <path d="M60 30 l7 32 l-7 32 l-7 -32 z M28 62 l32 -6 l32 6 l-32 6 z"/>
                        {{-- Silhouette simplifiée de la Côte d'Ivoire --}}
                        <path d="M160 34 l22 -6 l20 8 l18 -4 l14 12 l-2 20 l6 18 l-4 22 l-20 6 l-24 -4 l-22 6 l-12 -14 l4 -22 l-10 -16 l4 -20 z"/>
                        <circle cx="200" cy="86" r="3"/>
                        {{-- Éléphant (emblème national) --}}
                        <path d="M30 196 q0 -36 36 -36 q32 0 32 32 v18 M98 192 q12 4 8 28 M40 196 v22 M76 196 v22 M30 196 h54"/>
                        <circle cx="86" cy="178" r="2.5"/>
                        {{-- Vagues de la côte --}}
                        <path d="M130 214 q15 -12 30 0 t30 0 t30 0 t30 0"/>
                        <path d="M130 230 q15 -12 30 0 t30 0 t30 0 t30 0"/>
                        {{-- Soleil --}}
                        <circle cx="196" cy="168" r="10"/>
                        <path d="M196 148 v-6 M196 188 v6 M176 168 h-6 M216 168 h6 M182 154 l-4 -4 M210 154 l4 -4"/>
                        @break
                    @case('art')
                        {{-- Palette --}}
                        <path d="M28 90 q-6 -40 40 -46 q44 -4 48 26 q2 16 -16 14 q-14 -2 -12 12 q2 16 -20 16 q-34 0 -40 -22 z"/>
                        <circle cx="52" cy="62" r="5"/><circle cx="74" cy="56" r="5"/><circle cx="94" cy="66" r="5"/><circle cx="44" cy="84" r="5"/>
                        {{-- Pinceau --}}
                        <path d="M226 30 l-46 62 M180 92 q-10 4 -12 18 q12 -2 18 -12"/>
                        {{-- Statuette --}}
                        <circle cx="60" cy="158" r="12"/>
                        <path d="M60 170 v10 M46 180 h28 l-4 44 h-20 z M46 186 l-10 18 M74 186 l10 18"/>
                        {{-- Cadre --}}
                        <rect x="156" y="162" width="74" height="58" rx="3"/>
                        <rect x="166" y="172" width="54" height="38"/>
                        <path d="M168 206 l14 -16 l10 10 l8 -8 l18 14"/>
                        @break
                    @default
                        {{-- Sénoufo : toiles de Korhogo (calao, poisson, crocodile, tortue, frises) --}}
                        <path d="M30 70 q14 -22 40 -14 q10 4 8 14 q-2 10 -18 12 q-22 2 -30 -12 z"/>
                        <path d="M76 60 l26 -6 l-24 12"/>
                        <circle cx="70" cy="62" r="2.2" fill="#B85600"/>
                        <path d="M44 82 l-4 14 M56 83 l2 14"/>
                        <path d="M30 70 l-14 -8 l4 12 l-10 2 l16 6"/>
                        <path d="M150 40 q24 -18 52 0 q-28 18 -52 0 z"/>
                        <path d="M202 40 l14 -10 v20 z"/>
                        <circle cx="162" cy="37" r="2" fill="#B85600"/>
                        <path d="M172 32 v16 M182 32 v16 M192 34 v12"/>
                        <path d="M120 150 q40 -12 96 0 q-14 8 -40 8 h-40 q-10 0 -16 -8 z"/>
                        <path d="M136 146 l6 -8 l6 8 l6 -8 l6 8 l6 -8 l6 8 l6 -8 l6 8"/>
                        <path d="M216 150 l22 -2 l-20 6"/>
                        <path d="M146 158 l-6 12 M176 158 l6 12"/>
                        <circle cx="56" cy="186" r="22"/>
                        <path d="M40 176 l32 20 M72 176 l-32 20 M56 164 v44"/>
                        <path d="M38 168 l-10 -8 M74 168 l10 -8 M38 204 l-10 8 M74 204 l10 8 M56 208 v10"/>
                        <path d="M8 124 l10 -8 l10 8 l10 -8 l10 8 l10 -8 l10 8 l10 -8 l10 8"/>
                        <path d="M140 226 h100" stroke-dasharray="1 9"/>
                        <path d="M140 236 h100" stroke-dasharray="1 9" stroke-dashoffset="5"/>
                        <circle cx="236" cy="96" r="3"/><circle cx="246" cy="106" r="3"/><circle cx="226" cy="106" r="3"/>
                @endswitch
                </g>
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#{{ $miId }})"/>
    </svg>
    @if($miMark)<p class="ivm-mark"><span>Trésors</span> <span>d'Ivoire</span></p>@endif
</div>
@if($miBand)
    <div class="ivm-band" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <defs>
                <pattern id="ivm-kita" width="96" height="16" patternUnits="userSpaceOnUse">
                    <rect width="96" height="16" fill="#0E3B2A"/>
                    <rect x="0" width="22" height="16" fill="#EB6E00"/>
                    <rect x="4" y="5" width="4" height="6" fill="#EB6E00"/><rect x="14" y="5" width="4" height="6" fill="#EB6E00"/>
                    <rect x="24" width="10" height="16" fill="#EB6E00"/>
                    <rect x="36" width="22" height="16" fill="#00874F"/>
                    <rect x="40" y="0" width="4" height="4" fill="#D8E9E0"/><rect x="50" y="12" width="4" height="4" fill="#D8E9E0"/>
                    <rect x="60" width="6" height="16" fill="#D8E9E0"/>
                    <rect x="68" width="22" height="16" fill="#EB6E00"/>
                    <rect x="72" y="6" width="14" height="4" fill="#0E3B2A"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#ivm-kita)"/>
        </svg>
    </div>
@endif
