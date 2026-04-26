<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ArticleComment extends Model
{
    protected $fillable = [
        'uuid', 'article_id', 'user_id',
        'author_name', 'author_email', 'content', 'is_approved',
    ];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(fn (ArticleComment $c) => $c->uuid ??= (string) Str::uuid());
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->full_name ?? $this->author_name ?? 'Anonyme';
    }
}
