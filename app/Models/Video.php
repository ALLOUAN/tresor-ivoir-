<?php

namespace App\Models;

use App\Services\Ai\Catalog;
use App\Services\Ai\CatalogItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Vidéo de la médiathèque (envoi direct ou lien YouTube / Vimeo). */
class Video extends Model
{
    public const CATEGORIES = [
        'destinations' => ['label' => 'Destinations', 'icon' => 'map-point'],
        'gastronomie' => ['label' => 'Gastronomie', 'icon' => 'chef-hat'],
        'culture' => ['label' => 'Culture & traditions', 'icon' => 'masks'],
        'aventure' => ['label' => 'Aventure & nature', 'icon' => 'running-round'],
        'evenements' => ['label' => 'Événements', 'icon' => 'confetti'],
        'hebergement' => ['label' => 'Où dormir', 'icon' => 'bed'],
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    /** Formats acceptés à l'envoi direct. */
    public const UPLOAD_MIMES = ['mp4', 'webm', 'mov'];

    public const UPLOAD_MAX_KB = 51200; // 50 Mo

    protected $fillable = [
        'uuid', 'title', 'slug', 'description', 'category', 'source', 'file_path', 'video_url', 'external_id',
        'poster_url', 'duration_sec', 'orientation', 'city_id', 'provider_id', 'submitted_by', 'links', 'status',
        'rejection_reason', 'is_featured', 'views_count', 'likes_count', 'shares_count', 'booking_clicks_count',
        'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'duration_sec' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Video $v) {
            $v->uuid ??= (string) Str::uuid();
            $v->slug ??= static::uniqueSlug($v->title);
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'video';
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(TouristCity::class, 'city_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PUBLISHED);
    }

    public function isEmbed(): bool
    {
        return in_array($this->source, ['youtube', 'vimeo'], true);
    }

    /**
     * Reconnaît un lien YouTube (watch, youtu.be, shorts, embed) ou Vimeo.
     *
     * @return array{0: string, 1: string}|null [source, identifiant]
     */
    public static function parseExternal(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return ['youtube', $m[1]];
        }
        if (preg_match('~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d{6,12})~', $url, $m)) {
            return ['vimeo', $m[1]];
        }

        return null;
    }

    /** Adresse du lecteur intégré (lecture auto muette, boucle, sans habillage). */
    public function embedUrl(bool $autoplay = true): ?string
    {
        $a = $autoplay ? 1 : 0;

        return match ($this->source) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.$this->external_id.'?rel=0&playsinline=1&modestbranding=1&enablejsapi=1&mute=1&autoplay='.$a.'&loop=1&playlist='.$this->external_id,
            'vimeo' => 'https://player.vimeo.com/video/'.$this->external_id.'?muted=1&autoplay='.$a.'&loop=1&title=0&byline=0&portrait=0&dnt=1',
            default => null,
        };
    }

    public function poster(): ?string
    {
        if ($this->poster_url) {
            return $this->poster_url;
        }

        return $this->source === 'youtube' && $this->external_id
            ? 'https://i.ytimg.com/vi/'.$this->external_id.'/hqdefault.jpg'
            : null;
    }

    public function durationLabel(): ?string
    {
        if (! $this->duration_sec) {
            return null;
        }

        return intdiv($this->duration_sec, 60).':'.str_pad((string) ($this->duration_sec % 60), 2, '0', STR_PAD_LEFT);
    }

    public function categoryLabel(): string
    {
        return __(self::CATEGORIES[$this->category]['label'] ?? ucfirst($this->category));
    }

    /**
     * Fiches liées (« Voir et réserver ») : celles choisies, sinon celles du prestataire qui a proposé la vidéo.
     *
     * @return list<CatalogItem>
     */
    public function linkedItems(int $max = 3): array
    {
        $catalog = app(Catalog::class);
        $items = [];
        foreach ((array) $this->links as $key) {
            if (is_string($key) && ($item = $catalog->get($key))) {
                $items[] = $item;
            }
        }
        if (! $items && $this->provider_id) {
            foreach ($catalog->items() as $item) {
                if ($item->providerId === $this->provider_id && $item->type !== 'provider') {
                    $items[] = $item;
                }
            }
        }

        return array_slice($items, 0, $max);
    }

    /** Données envoyées au lecteur plein écran. */
    public function toPlayer(): array
    {
        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => (string) $this->description,
            'category' => $this->categoryLabel(),
            'city' => $this->city?->name,
            'source' => $this->source,
            'src' => $this->video_url,
            'embed' => $this->embedUrl(),
            'poster' => $this->poster(),
            'portrait' => $this->orientation === 'portrait',
            'duration' => $this->durationLabel(),
            'views' => $this->views_count,
            'likes' => $this->likes_count,
            'url' => route('videos.show', $this),
            'links' => array_map(fn (CatalogItem $i) => [
                'title' => $i->title, 'type' => $i->typeLabel, 'image' => $i->image, 'price' => $i->price,
                'url' => $i->url.(str_contains($i->url, '?') ? '&' : '?').'ref=video-'.$this->id,
            ], $this->linkedItems()),
        ];
    }
}
