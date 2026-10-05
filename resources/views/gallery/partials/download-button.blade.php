@php
    $count = $img->downloads_count ?? 0;
@endphp
{{-- Téléchargement direct (comportement natif du navigateur via l'attribut
     `download`) — le suivi du compteur se fait en parallèle par un appel en
     tâche de fond (voir gallery-like-script.blade.php), sans jamais bloquer
     ni remplacer le téléchargement réel du fichier. --}}
<a href="{{ url($img->url) }}" download
   class="{{ $class ?? '' }} gallery-download-btn"
   data-media-uuid="{{ $img->uuid }}"
   title="Télécharger" aria-label="Télécharger cette image"
   onclick="event.stopPropagation();">
    <i class="fas fa-download text-xs"></i>
    <span class="download-count">{{ $count }}</span>
</a>
