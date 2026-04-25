<?php

namespace Database\Seeders;

use App\Enums\InformationPageSlug;
use App\Models\InformationPage;
use Database\Seeders\Data\InformationPageDefaults;
use Illuminate\Database\Seeder;

class InformationPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (InformationPageSlug::ordered() as $index => $slug) {
            $defaults = InformationPageDefaults::pack($slug->value);

            $page = InformationPage::query()->firstOrNew(['slug' => $slug->value]);

            if (! $page->exists) {
                $page->fill([
                    'title_fr' => $defaults['title_fr'],
                    'title_en' => $defaults['title_en'],
                    'body_fr' => $defaults['body_fr'],
                    'body_en' => $defaults['body_en'],
                    'sort_order' => $index,
                ]);
            } else {
                $page->sort_order = $index;
                if (blank(trim((string) $page->body_fr))) {
                    $page->body_fr = $defaults['body_fr'];
                }
                if (blank(trim((string) $page->body_en))) {
                    $page->body_en = $defaults['body_en'];
                }
                if (blank(trim((string) $page->title_en))) {
                    $page->title_en = $defaults['title_en'];
                }
            }

            $page->save();
        }
    }
}
