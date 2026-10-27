<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\FormatsValues;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    use FormatsValues;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim($this->first_name.' '.$this->last_name),
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->absoluteUrl($this->avatar_url),
            'role' => $this->role,
            'locale' => $this->locale,
            'email_verified' => $this->hasVerifiedEmail(),
            'created_at' => $this->isoDate($this->created_at),
        ];
    }
}
