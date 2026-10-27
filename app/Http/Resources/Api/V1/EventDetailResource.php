<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

/** Fiche complète d'un événement (liste + description, lieu, organisateur, programme, médias). */
class EventDetailResource extends EventResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description_html' => $this->localized('description'),
            'address' => $this->address,
            'latitude' => $this->decimal($this->latitude),
            'longitude' => $this->decimal($this->longitude),
            'audience' => $this->audience,
            'ticket_url' => $this->ticket_url,
            'capacity' => $this->capacity,
            'registration_deadline' => $this->isoDate($this->registration_deadline),
            'is_recurring' => (bool) $this->is_recurring,
            'program' => $this->program ?: [],
            'organizer' => [
                'name' => $this->organizer_name,
                'phone' => $this->organizer_phone,
                'email' => $this->organizer_email,
            ],
            'provider' => $this->whenLoaded('provider', fn () => $this->provider ? [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
                'slug' => $this->provider->slug,
            ] : null),
            'photos' => MediaResource::collection($this->whenLoaded('media', fn () => $this->media->where('type', 'image')->sortBy('sort_order')->values())),
            'videos' => MediaResource::collection($this->whenLoaded('media', fn () => $this->media->where('type', 'video')->sortBy('sort_order')->values())),
        ];
    }
}
