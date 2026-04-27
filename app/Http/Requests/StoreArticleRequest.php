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
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'article_images' => ['nullable', 'array', 'max:20'],
            'article_images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:6144'],
            'remove_media_ids' => ['nullable', 'array'],
            'remove_media_ids.*' => ['integer', 'exists:media,id'],
            'cover_alt' => ['nullable', 'string', 'max:300'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'is_featured' => ['boolean'],
            'is_destination' => ['boolean'],
            'is_sponsored' => ['boolean'],
            'sponsor_id' => ['nullable', 'required_if:is_sponsored,1', 'exists:providers,id'],
            'status' => ['required', 'in:draft,review,published,archived'],
            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date', 'required_if:publication_mode,schedule'],
            'publication_mode' => ['nullable', 'in:now,schedule'],
            'meta_title_fr' => ['nullable', 'string', 'max:70'],
            'meta_desc_fr' => ['nullable', 'string', 'max:165'],
            'meta_title_en' => ['nullable', 'string', 'max:70'],
            'meta_desc_en' => ['nullable', 'string', 'max:165'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'uploader_ids' => ['nullable', 'array'],
            'uploader_ids.*' => ['integer', 'exists:users,id'],
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

        if (! $this->boolean('is_sponsored')) {
            $this->merge(['sponsor_id' => null]);
        }

        if (
            $this->input('publication_mode') === 'schedule'
            && $this->filled('scheduled_at')
            && $this->input('status') !== 'draft'
        ) {
            $this->merge([
                'status' => 'published',
                'published_at' => $this->input('scheduled_at'),
            ]);
        }

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
            'cover_image.image' => 'Le fichier de couverture doit être une image valide.',
            'cover_image.mimes' => 'Le format de l’image doit être jpeg, jpg, png ou webp.',
            'cover_image.max' => 'L’image de couverture ne doit pas dépasser 4 Mo.',
            'article_images.array' => 'Le lot d’images est invalide.',
            'article_images.max' => 'Vous pouvez ajouter au maximum 20 images.',
            'article_images.*.image' => 'Chaque fichier de la galerie doit être une image valide.',
            'article_images.*.mimes' => 'Les images de la galerie doivent être au format jpeg, jpg, png ou webp.',
            'article_images.*.max' => 'Chaque image de la galerie ne doit pas dépasser 6 Mo.',
            'sponsor_id.required_if' => 'Veuillez sélectionner un sponsor pour un article sponsorisé.',
            'sponsor_id.exists' => 'Le sponsor sélectionné est invalide.',
            'scheduled_at.required_if' => 'Veuillez renseigner la date de planification.',
        ];
    }
}
