<?php

namespace App\Models;

use App\Enums\InformationPageSlug;
use Illuminate\Database\Eloquent\Model;

class InformationPage extends Model
{
    protected $fillable = [
        'slug',
        'title_fr',
        'title_en',
        'body_fr',
        'body_en',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function slugEnum(): ?InformationPageSlug
    {
        return InformationPageSlug::tryFrom($this->slug);
    }
}
