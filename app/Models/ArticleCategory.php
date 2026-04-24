<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name_fr', 'name_en',
        'description_fr', 'description_en',
        'cover_url', 'color_hex', 'icon',
        'sort_order', 'is_active',
        'meta_title_fr', 'meta_desc_fr',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function publishedArticles()
    {
        return $this->hasMany(Article::class, 'category_id')->where('status', 'published');
    }
}
