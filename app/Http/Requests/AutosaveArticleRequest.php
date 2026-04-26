<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AutosaveArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $articleId = $this->route('article')?->id;

        return [
            'title_fr' => ['nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'slug_fr' => ['nullable', 'string', 'max:300', Rule::unique('articles', 'slug_fr')->ignore($articleId)],
            'slug_en' => ['nullable', 'string', 'max:300', Rule::unique('articles', 'slug_en')->ignore($articleId)],
            'category_id' => ['nullable', 'exists:article_categories,id'],
            'excerpt_fr' => ['nullable', 'string', 'max:500'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'content_fr' => ['nullable', 'string'],
            'content_en' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'url', 'max:500'],
            'cover_alt' => ['nullable', 'string', 'max:300'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
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
        if ($this->filled('title_fr') && ! $this->filled('slug_fr')) {
            $this->merge(['slug_fr' => Str::slug($this->input('title_fr'))]);
        }
        if ($this->filled('title_en') && ! $this->filled('slug_en')) {
            $this->merge(['slug_en' => Str::slug($this->input('title_en'))]);
        }
    }
}
