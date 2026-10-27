<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\FormatsValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Photo ou vidéo rattachée à une fiche. @mixin \App\Models\Media */
class MediaResource extends JsonResource
{
    use FormatsValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'url' => $this->absoluteUrl($this->url ?: ($this->file_path ? '/storage/'.ltrim($this->file_path, '/') : null)),
            'thumb_url' => $this->absoluteUrl($this->thumb_url),
            'alt' => $this->alt_text,
            'caption' => $this->caption,
            'width' => $this->width,
            'height' => $this->height,
            'duration_sec' => $this->duration_sec,
        ];
    }
}
