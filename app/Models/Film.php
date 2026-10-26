<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Film de la rubrique « Cinéma d'Ivoire » (Médiathèque) : documentaire ou fiction. */
class Film extends Model
{
    public const KINDS = [
        'documentaire' => ['label' => 'Documentaire', 'plural' => 'Documentaires', 'slug' => 'documentaires', 'icon' => 'fa-video'],
        'fiction' => ['label' => 'Fiction', 'plural' => 'Films de fiction', 'slug' => 'fictions', 'icon' => 'fa-clapperboard'],
    ];

    /** Genres proposés (liste ouverte : l'équipe peut en saisir d'autres). */
    public const GENRES = [
        'documentaire' => ['Patrimoine & traditions', 'Histoire', 'Société', 'Portrait', 'Nature & environnement', 'Territoires', 'Musique & arts'],
        'fiction' => ['Drame', 'Comédie', 'Historique', 'Romance', 'Thriller', 'Aventure', 'Court métrage'],
    ];

    public const STATUSES = [
        'draft' => 'Brouillon',
        'pending' => 'À valider',
        'published' => 'Publié',
        'rejected' => 'Refusé',
        'withdrawn' => 'Retiré',
    ];

    public const IMAGE_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    public const IMAGE_MAX_KB = 6144;

    protected $fillable = [
        'title', 'slug', 'kind', 'genre', 'tagline', 'synopsis', 'poster_path', 'backdrop_path',
        'director', 'director_bio', 'director_photo_path', 'cast', 'production', 'country', 'year', 'duration_minutes', 'language',
        'trailer_url', 'watch_url', 'watch_label', 'distributor_contact', 'awards', 'screenings', 'credits',
        'rights_confirmed', 'is_sponsored', 'is_featured', 'status', 'review_note',
        'submitter_name', 'submitter_email', 'submitter_phone', 'submitted_at', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'screenings' => 'array',
            'rights_confirmed' => 'boolean',
            'is_sponsored' => 'boolean',
            'is_featured' => 'boolean',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
            'year' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Film $f) {
            $f->slug ??= static::uniqueSlug($f->title);
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'film';
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('is_featured')->orderByDesc('published_at')->orderByDesc('id');
    }

    public static function kindFromSlug(?string $slug): ?string
    {
        foreach (self::KINDS as $kind => $meta) {
            if ($meta['slug'] === $slug) {
                return $kind;
            }
        }

        return null;
    }

    public function kindLabel(): string
    {
        return __(self::KINDS[$this->kind]['label'] ?? ucfirst((string) $this->kind));
    }

    public function kindIcon(): string
    {
        return self::KINDS[$this->kind]['icon'] ?? 'fa-film';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function posterUrl(): ?string
    {
        return $this->poster_path ? Storage::disk('public')->url($this->poster_path) : null;
    }

    public function directorPhotoUrl(): ?string
    {
        return $this->director_photo_path ? Storage::disk('public')->url($this->director_photo_path) : null;
    }

    /** Image paysage : le fond dédié, sinon l'affiche. */
    public function backdropUrl(): ?string
    {
        return $this->backdrop_path ? Storage::disk('public')->url($this->backdrop_path) : $this->posterUrl();
    }

    /** « 1 h 32 » à partir des minutes. */
    public function durationLabel(): ?string
    {
        $m = (int) $this->duration_minutes;
        if ($m <= 0) {
            return null;
        }

        return $m < 60 ? $m.' min' : intdiv($m, 60).' h'.($m % 60 ? ' '.str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT) : '');
    }

    /**
     * Bande-annonce intégrable (YouTube en mode confidentialité renforcée, ou Vimeo), sinon null.
     *
     * @return array{provider: string, embed: string, thumb: ?string}|null
     */
    public function trailer(): ?array
    {
        $url = (string) $this->trailer_url;
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return [
                'provider' => 'youtube',
                'embed' => 'https://www.youtube-nocookie.com/embed/'.$m[1].'?autoplay=1&rel=0',
                'thumb' => 'https://i.ytimg.com/vi/'.$m[1].'/hqdefault.jpg',
            ];
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return ['provider' => 'vimeo', 'embed' => 'https://player.vimeo.com/video/'.$m[1].'?autoplay=1&dnt=1', 'thumb' => null];
        }

        return null;
    }

    /** Projections à venir, triées par date. */
    public function upcomingScreenings(): array
    {
        return collect($this->screenings ?? [])
            ->filter(fn ($s) => ! empty($s['date']) && strtotime($s['date']) >= strtotime('today'))
            ->sortBy('date')->values()->all();
    }

    public function isNew(): bool
    {
        return $this->published_at !== null && $this->published_at->gt(now()->subDays(30));
    }

    /** Durée au format ISO 8601 (balisage schema.org). */
    public function isoDuration(): ?string
    {
        return $this->duration_minutes ? 'PT'.(int) $this->duration_minutes.'M' : null;
    }
}
