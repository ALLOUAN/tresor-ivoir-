<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProviderPhotoTestSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::query()->orderBy('id')->first();
        $uploader = User::query()->where('role', 'provider')->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();

        if (! $provider || ! $uploader) {
            $this->command?->warn('Seeder ignoré: provider ou uploader introuvable.');
            return;
        }

        $photos = [
            [
                'original_name' => 'photo-test-prestataire-1.jpg',
                'url' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1600&q=80',
                'caption' => 'Photo test 1 - vue panoramique',
            ],
            [
                'original_name' => 'photo-test-prestataire-2.jpg',
                'url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80',
                'caption' => 'Photo test 2 - ambiance destination',
            ],
            [
                'original_name' => 'photo-test-prestataire-3.jpg',
                'url' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1600&q=80',
                'caption' => 'Photo test 3 - expérience touristique',
            ],
            [
                'original_name' => 'photo-test-prestataire-4.jpg',
                'url' => 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=1600&q=80',
                'caption' => 'Photo test 4 - découverte locale',
            ],
        ];

        foreach ($photos as $index => $photo) {
            Media::query()->updateOrCreate(
                [
                    'mediable_type' => Provider::class,
                    'mediable_id' => $provider->id,
                    'original_name' => $photo['original_name'],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'collection' => 'provider-gallery',
                    'type' => 'image',
                    'mime_type' => 'image/jpeg',
                    'file_path' => 'seed/provider/'.$photo['original_name'],
                    'url' => $photo['url'],
                    'thumb_url' => null,
                    'size_bytes' => 450000,
                    'width' => 1600,
                    'height' => 1067,
                    'duration_sec' => null,
                    'alt_text' => 'Photo de test prestataire',
                    'caption' => $photo['caption'],
                    'sort_order' => $index + 1,
                    'uploaded_by' => $uploader->id,
                ]
            );
        }

        $this->command?->info('ProviderPhotoTestSeeder exécuté: photos de test ajoutées.');
    }
}

