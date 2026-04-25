<?php

namespace App\Models;

use App\Enums\PartnershipType;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'name',
        'partnership_type',
        'website_url',
        'partnership_start_date',
        'description',
        'contact_person',
        'contact_email',
        'contact_phone',
        'logo_url',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'partnership_start_date' => 'date',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function typeEnum(): ?PartnershipType
    {
        return PartnershipType::tryFrom($this->partnership_type);
    }
}
