<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationMedia extends Model
{
    protected $table = 'accommodation_media';

    protected $fillable = [
        'accommodation_id', 'type',
        'url', 'caption', 'alt_text', 'sort_order',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function isPhoto(): bool
    {
        return $this->type === 'photo';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function scopePhotos($query)
    {
        return $query->where('type', 'photo');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * URL d'intégration (iframe) pour un lien vidéo YouTube/Vimeo ; null si le format n'est pas
     * reconnu (ex. lien .mp4 direct, lu via <video>). $autoplay ajoute les paramètres nécessaires
     * au démarrage automatique — muet par défaut, seule option fiable acceptée par les navigateurs
     * pour un autoplay déclenché sans clic direct sur le lecteur.
     */
    public function embedUrl(bool $autoplay = false): ?string
    {
        if (! $this->isVideo()) {
            return null;
        }
        $base = null;
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $this->url, $m)) {
            $base = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (preg_match('/vimeo\.com\/(\d+)/', $this->url, $m)) {
            $base = 'https://player.vimeo.com/video/'.$m[1];
        }
        if ($base === null) {
            return null;
        }

        return $autoplay ? $base.'?autoplay=1&mute=1&muted=1&playsinline=1' : $base;
    }

    /** Vignette officielle YouTube pour la miniature du carrousel ; null pour Vimeo/lien direct (pas d'API appelée). */
    public function videoThumbnailUrl(): ?string
    {
        if (! $this->isVideo()) {
            return null;
        }
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $this->url, $m)) {
            return 'https://i.ytimg.com/vi/'.$m[1].'/hqdefault.jpg';
        }

        return null;
    }
}
