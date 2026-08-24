<?php

namespace Database\Seeders;

use App\Models\FlashInfo;
use Illuminate\Database\Seeder;

class FlashInfoSeeder extends Seeder
{
    public function run(): void
    {
        $flashInfos = [
            [
                'message' => 'Bienvenue sur Trésors Ivoire — découvrez les plus belles destinations de Côte d’Ivoire.',
                'link_url' => null,
                'link_label' => null,
                'type' => 'info',
                'is_active' => true,
                'is_dismissible' => true,
                'display_order' => 1,
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'message' => 'Nouveau ! Réservez votre hébergement directement en ligne, sans intermédiaire.',
                'link_url' => '/tourisme',
                'link_label' => 'Voir les hébergements',
                'type' => 'success',
                'is_active' => false,
                'is_dismissible' => true,
                'display_order' => 2,
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'message' => 'Maintenance programmée du site le week-end prochain, certaines fonctionnalités pourront être ralenties.',
                'link_url' => null,
                'link_label' => null,
                'type' => 'warning',
                'is_active' => false,
                'is_dismissible' => true,
                'display_order' => 3,
                'starts_at' => null,
                'ends_at' => null,
            ],
            [
                'message' => 'Alerte météo : fortes pluies annoncées dans le Sud du pays cette semaine.',
                'link_url' => null,
                'link_label' => null,
                'type' => 'urgent',
                'is_active' => false,
                'is_dismissible' => false,
                'display_order' => 4,
                'starts_at' => null,
                'ends_at' => null,
            ],
        ];

        foreach ($flashInfos as $data) {
            FlashInfo::updateOrCreate(
                ['message' => $data['message']],
                $data
            );
        }
    }
}
