<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\EventCategory */
class EventCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->localized('name'),
            'icon' => $this->icon,
            'color' => $this->color_hex,
            'events_count' => $this->whenCounted('events'),
        ];
    }
}
