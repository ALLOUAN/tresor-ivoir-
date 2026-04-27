<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProviderPublicationsTestSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::query()->orderBy('id')->first();
        $editor = User::query()->where('role', 'editor')->first() ?? User::query()->where('role', 'admin')->first();
        $admin = User::query()->where('role', 'admin')->first() ?? $editor;

        $articleCategory = ArticleCategory::query()->orderBy('id')->first();
        $eventCategory = EventCategory::query()->orderBy('id')->first();

        if (! $provider || ! $editor || ! $admin || ! $articleCategory || ! $eventCategory) {
            $this->command?->warn('Seeder ignoré: données de base manquantes (provider/user/category).');
            return;
        }

        $articles = [
            [
                'slug_fr' => 'test-publication-prestataire-1',
                'title_fr' => 'Publication test prestataire #1',
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
            [
                'slug_fr' => 'test-publication-prestataire-2',
                'title_fr' => 'Publication test prestataire #2',
                'status' => 'draft',
                'published_at' => null,
            ],
            [
                'slug_fr' => 'test-publication-prestataire-3',
                'title_fr' => 'Publication test prestataire #3',
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($articles as $entry) {
            Article::query()->updateOrCreate(
                ['slug_fr' => $entry['slug_fr']],
                [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $articleCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => $entry['title_fr'],
                    'slug_en' => $entry['slug_fr'].'-en',
                    'title_en' => $entry['title_fr'].' EN',
                    'excerpt_fr' => 'Contenu de test pour la liste des publications prestataire.',
                    'content_fr' => '<p>Contenu de test.</p>',
                    'is_sponsored' => true,
                    'sponsor_id' => $provider->id,
                    'status' => $entry['status'],
                    'published_at' => $entry['published_at'],
                ]
            );
        }

        $events = [
            [
                'slug' => 'test-evenement-prestataire-1',
                'title_fr' => 'Événement test prestataire #1',
                'status' => 'published',
                'starts_at' => now()->addDays(7)->setTime(18, 0),
                'published_at' => now()->subDay(),
            ],
            [
                'slug' => 'test-evenement-prestataire-2',
                'title_fr' => 'Événement test prestataire #2',
                'status' => 'draft',
                'starts_at' => now()->addDays(15)->setTime(9, 0),
                'published_at' => null,
            ],
        ];

        foreach ($events as $entry) {
            Event::query()->updateOrCreate(
                ['slug' => $entry['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $eventCategory->id,
                    'created_by' => $admin->id,
                    'provider_id' => $provider->id,
                    'title_fr' => $entry['title_fr'],
                    'description_fr' => 'Événement de test lié au prestataire.',
                    'starts_at' => $entry['starts_at'],
                    'ends_at' => (clone $entry['starts_at'])->addHours(3),
                    'city' => $provider->city ?: 'Abidjan',
                    'location_name' => $provider->name,
                    'status' => $entry['status'],
                    'published_at' => $entry['published_at'],
                ]
            );
        }

        $this->command?->info('ProviderPublicationsTestSeeder exécuté: publications de test créées.');
    }
}

