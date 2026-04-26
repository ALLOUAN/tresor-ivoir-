<?php

namespace Database\Seeders;

use App\Enums\PartnershipType;
use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'name' => 'Université Félix Houphouët-Boigny',
                'partnership_type' => PartnershipType::Educational->value,
                'website_url' => 'https://www.univ-fhb.edu.ci',
                'partnership_start_date' => '2024-02-01',
                'description' => 'Programme de collaboration pour la valorisation du patrimoine culturel ivoirien.',
                'contact_person' => 'Dr. Awa Konan',
                'contact_email' => 'partenariats@univ-fhb.edu.ci',
                'contact_phone' => '+225 27 22 48 10 10',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ministère du Tourisme et des Loisirs',
                'partnership_type' => PartnershipType::Government->value,
                'website_url' => 'https://www.tourisme.gouv.ci',
                'partnership_start_date' => '2023-09-15',
                'description' => 'Coopération institutionnelle pour la promotion des destinations touristiques locales.',
                'contact_person' => 'M. Yao N’Guessan',
                'contact_email' => 'cooperation@tourisme.gouv.ci',
                'contact_phone' => '+225 27 20 31 20 50',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Orange Digital Center Côte d’Ivoire',
                'partnership_type' => PartnershipType::Technology->value,
                'website_url' => 'https://www.orange.ci',
                'partnership_start_date' => '2024-06-10',
                'description' => 'Appui technique sur les outils digitaux de diffusion des contenus culturels.',
                'contact_person' => 'Mme Christelle Kouassi',
                'contact_email' => 'odc.ci@orange.com',
                'contact_phone' => '+225 27 21 23 23 23',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'ONG Patrimoine Vivant',
                'partnership_type' => PartnershipType::Ngo->value,
                'website_url' => null,
                'partnership_start_date' => '2025-01-20',
                'description' => 'Actions de terrain pour documenter et préserver les pratiques culturelles locales.',
                'contact_person' => 'Mme Mariam Traoré',
                'contact_email' => 'contact@patrimoinedurable.ci',
                'contact_phone' => '+225 07 08 09 10 11',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Réseau Culture et Médias',
                'partnership_type' => PartnershipType::Misc->value,
                'website_url' => null,
                'partnership_start_date' => null,
                'description' => 'Relais éditorial pour amplifier la visibilité des articles et événements.',
                'contact_person' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($partners as $partnerData) {
            Partner::updateOrCreate(
                ['name' => $partnerData['name']],
                $partnerData
            );
        }
    }
}
