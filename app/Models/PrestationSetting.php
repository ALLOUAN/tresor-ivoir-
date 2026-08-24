<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestationSetting extends Model
{
    protected $fillable = [
        'video_source', 'video_url', 'video_embed_url', 'video_poster_url',
        'catalog_title', 'catalog_file_url', 'catalog_enabled',
    ];

    protected function casts(): array
    {
        return [
            'catalog_enabled' => 'boolean',
        ];
    }

    /**
     * NB : on n'utilise pas firstOrCreate(['id' => 1], []) — 'id' n'étant pas
     * fillable (à raison), Eloquent l'ignore silencieusement à la création,
     * ce qui insère une nouvelle ligne à chaque appel au lieu de réutiliser
     * la ligne singleton dès que celle-ci n'existe plus.
     */
    public static function singleton(): self
    {
        $instance = static::query()->find(1);

        if (! $instance) {
            $instance = new static();
            $instance->id = 1;
            $instance->save();
        }

        return $instance;
    }

    public function isVideoEmbed(): bool
    {
        return $this->video_source === 'embed';
    }

    /** URL prête à être utilisée dans un <iframe> pour YouTube/Vimeo, ou l'URL brute sinon. */
    public function getVideoEmbedSrcAttribute(): ?string
    {
        if (! $this->video_embed_url) {
            return null;
        }

        if (preg_match('/(?:youtu\.be\/|youtube\.com\/watch\?v=|youtube\.com\/embed\/)([A-Za-z0-9_-]{6,})/', $this->video_embed_url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_embed_url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $this->video_embed_url;
    }

    public function hasVideo(): bool
    {
        return ($this->video_source === 'upload' && (bool) $this->video_url)
            || ($this->video_source === 'embed' && (bool) $this->video_embed_url);
    }
}
