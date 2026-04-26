<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AutosaveEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'title_fr' => ['nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:300', Rule::unique('events', 'slug')->ignore($eventId)],
            'category_id' => ['nullable', 'exists:event_categories,id'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'url', 'max:500'],
            'cover_alt' => ['nullable', 'string', 'max:300'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', 'string', 'max:255'],
            'registration_deadline' => ['nullable', 'date'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_free' => ['nullable', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'ticket_url' => ['nullable', 'url', 'max:500'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:150'],
            'organizer_name' => ['nullable', 'string', 'max:255'],
            'organizer_phone' => ['nullable', 'string', 'max:20'],
            'organizer_email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:draft,published,cancelled'],
            'meta_title_fr' => ['nullable', 'string', 'max:70'],
            'meta_desc_fr' => ['nullable', 'string', 'max:165'],
            'meta_title_en' => ['nullable', 'string', 'max:70'],
            'meta_desc_en' => ['nullable', 'string', 'max:165'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['cover_url', 'ticket_url'] as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        if ($this->has('provider_id') && $this->input('provider_id') === '') {
            $this->merge(['provider_id' => null]);
        }

        if ($this->has('category_id') && $this->input('category_id') === '') {
            $this->merge(['category_id' => null]);
        }

        foreach (['starts_at', 'ends_at', 'registration_deadline', 'latitude', 'longitude', 'capacity', 'price'] as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        if ($this->has('slug') && trim((string) $this->input('slug')) === '') {
            $this->replace($this->except('slug'));
        }

        if ($this->filled('title_fr') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('title_fr'))]);
        }
    }
}
