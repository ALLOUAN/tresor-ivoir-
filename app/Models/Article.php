<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'category_id', 'author_id',
        'title_fr', 'title_en', 'slug_fr', 'slug_en',
        'excerpt_fr', 'excerpt_en', 'content_fr', 'content_en',
        'cover_url', 'cover_alt', 'reading_time', 'word_count',
        'is_featured', 'is_destination', 'is_sponsored', 'sponsor_id',
        'status',
        'meta_title_fr', 'meta_desc_fr', 'meta_title_en', 'meta_desc_en',
        'published_at', 'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_destination' => 'boolean',
            'is_sponsored' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Article $a) => $a->uuid ??= (string) Str::uuid());
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function uploaders()
    {
        return $this->belongsToMany(User::class, 'article_uploader')
            ->withTimestamps();
    }

    public function getDisplayUploadersAttribute(): EloquentCollection
    {
        $fallback = new EloquentCollection();
        if ($this->author) {
            $fallback->push($this->author);
        }

        if (! Schema::hasTable('article_uploader')) {
            return $fallback;
        }

        try {
            $uploaders = $this->relationLoaded('uploaders')
                ? $this->getRelation('uploaders')
                : $this->uploaders()->get();

            return $uploaders->isNotEmpty() ? $uploaders : $fallback;
        } catch (QueryException) {
            return $fallback;
        }
    }

    public function sponsor()
    {
        return $this->belongsTo(Provider::class, 'sponsor_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag')->withPivot('created_at');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function analytics()
    {
        return $this->hasMany(ArticleAnalytic::class);
    }

    public function comments()
    {
        return $this->hasMany(ArticleComment::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }
}
