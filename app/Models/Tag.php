<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $timestamps = false;

    protected $fillable = ['slug', 'name_fr', 'name_en', 'type'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag')->withPivot('created_at');
    }

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_tag')->withPivot('created_at');
    }
}
