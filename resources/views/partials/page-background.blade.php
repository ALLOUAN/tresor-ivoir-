{{--
    Fond décoratif uniforme des pages publiques : mêmes rayures diagonales
    orangées + halo radial chaud sur toutes les pages (référence : /abonnements
    et /login). Fixé à la fenêtre (ne défile pas) pour rester visible et
    identique quelle que soit la hauteur de la page ; z-index négatif pour
    rester sous tout le contenu normal.
--}}
<div class="fixed inset-0 -z-10 pointer-events-none" aria-hidden="true">
    <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(45deg,#f2790f 0,#f2790f 1px,transparent 0,transparent 50%);background-size:20px 20px"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full" style="background:radial-gradient(circle,rgba(242, 121, 15,0.07),transparent 70%)"></div>
</div>
