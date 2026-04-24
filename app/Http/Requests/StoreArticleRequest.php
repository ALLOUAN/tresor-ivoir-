<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $articleId = $this->route('article')?->id;

        return [
            'title_fr' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'slug_fr' => ['required', 'string', 'max:300', "unique:articles,slug_fr,{$articleId}"],
            'slug_en' => ['nullable', 'string', 'max:300', "unique:articles,slug_en,{$articleId}"],
            'category_id' => ['required', 'exists:article_categories,id'],
            'excerpt_fr' => ['nullable', 'string', 'max:500'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'content_fr' => ['nullable', 'string'],
            'content_en' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'url', 'max:500'],
            'cover_alt' => ['nullable', 'string', 'max:300'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'is_featured' => ['boolean'],
            'is_destination' => ['boolean'],
            'is_sponsored' => ['boolean'],
            'status' => ['required', 'in:draft,review,published,archived'],
            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
            'meta_title_fr' => ['nullable', 'string', 'max:70'],
            'meta_desc_fr' => ['nullable', 'string', 'max:165'],
            'meta_title_en' => ['nullable', 'string', 'max:70'],
            'meta_desc_en' => ['nullable', 'string', 'max:165'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->slug_fr) && $this->title_fr) {
            $this->merge(['slug_fr' => Str::slug($this->title_fr)]);
        }
        if (empty($this->slug_en) && $this->title_en) {
            $this->merge(['slug_en' => Str::slug($this->title_en)]);
        }

        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_destination' => $this->boolean('is_destination'),
            'is_sponsored' => $this->boolean('is_sponsored'),
        ]);

        if ($this->status === 'published' && empty($this->published_at)) {
            $this->merge(['published_at' => now()]);
        }
    }

    public function messages(): array
    {
        return [
            'title_fr.required' => 'Le titre en français est obligatoire.',
            'category_id.required' => 'Veuillez sélectionner une rubrique.',
            'category_id.exists' => 'La rubrique sélectionnée est invalide.',
            'slug_fr.unique' => 'Ce slug est déjà utilisé par un autre article.',
        ];
    }
}
