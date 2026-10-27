<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\FormatsValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Événement — version courte pour les listes. La fiche complète : EventDetailResource. @mixin \App\Models\Event */
class EventResource extends JsonResource
{
    use FormatsValues;

    public function toArray(Request $request): array
    {
        $now = now();
        $ongoing = $this->starts_at && $this->starts_at->lte($now)
            && ($this->ends_at ? $this->ends_at->gte($now) : $this->starts_at->isSameDay($now));

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->localized('title'),
            'subtitle' => $this->localized('subtitle'),
            'cover' => [
                'url' => $this->absoluteUrl($this->cover_url),
                'alt' => $this->cover_alt,
            ],
            'starts_at' => $this->isoDate($this->starts_at),
            'ends_at' => $this->isoDate($this->ends_at),
            'is_ongoing' => $ongoing,
            'city' => $this->city,
            'location_name' => $this->location_name,
            'is_free' => (bool) $this->is_free,
            'price' => $this->is_free ? 0.0 : $this->decimal($this->price),
            'currency' => 'XOF',
            'category' => EventCategoryResource::make($this->whenLoaded('category')),
            // Lien de la fiche sur le site : partage WhatsApp, « Ouvrir sur le site ».
            'web_url' => route('events.show', $this->slug),
        ];
    }
}
