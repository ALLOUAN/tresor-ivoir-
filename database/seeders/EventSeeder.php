<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $festival = EventCategory::where('slug', 'festivals')->first();
        $traditions = EventCategory::where('slug', 'traditions')->first();
        $concert = EventCategory::where('slug', 'concerts')->first();

        $events = [
            [
                'uuid' => (string) Str::uuid(),
                'category_id' => $festival->id,
                'created_by' => $admin->id,
                'title_fr' => 'Festival des Masques — Man',
                'title_en' => 'Festival of Masks — Man',
                'slug' => 'festival-masques-man-2025',
                'description_fr' => 'Le plus grand rassemblement de masques traditionnels de Côte d\'Ivoire. Pendant 3 jours, les masques Dan, Wé et Toura se retrouvent à Man pour des danses, cérémonies et rencontres interculturelles ouvertes au public.',
                'description_en' => 'The largest gathering of traditional masks in Ivory Coast. For 3 days, the Dan, Wé and Toura masks meet in Man for dances, ceremonies and intercultural meetings open to the public.',
                'starts_at' => now()->addMonths(2)->setTime(9, 0),
                'ends_at' => now()->addMonths(2)->addDays(2)->setTime(20, 0),
                'location_name' => 'Stade Municipal de Man',
                'city' => 'Man',
                'latitude' => 7.41260000,
                'longitude' => -7.55380000,
                'is_free' => false,
                'price' => 2000.00,
                'is_recurring' => true,
                'recurrence_rule' => 'FREQ=YEARLY',
                'organizer_name' => 'Ministère de la Culture et de la Francophonie',
                'organizer_phone' => '+22520214000',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'category_id' => $concert->id,
                'created_by' => $admin->id,
                'title_fr' => 'Nuit de l\'Ivoire — Abidjan',
                'title_en' => 'Nuit de l\'Ivoire — Abidjan',
                'slug' => 'nuit-ivoire-abidjan-2025',
                'description_fr' => 'La grande soirée musicale qui réunit chaque année les meilleures voix de la musique ivoirienne : coupé-décalé, zoblazo, afrobeats et ndombolo. Une nuit inoubliable au Palais de la Culture.',
                'starts_at' => now()->addMonths(1)->setTime(20, 0),
                'ends_at' => now()->addMonths(1)->addDays(1)->setTime(3, 0),
                'location_name' => 'Palais de la Culture de Treichville',
                'address' => 'Boulevard de Marseille, Treichville, Abidjan',
                'city' => 'Abidjan',
                'latitude' => 5.30380000,
                'longitude' => -4.01500000,
                'is_free' => false,
                'price' => 10000.00,
                'ticket_url' => 'https://tickets.tresorsdivoire.ci/nuit-ivoire-2025',
                'organizer_name' => 'Abidjan Events Production',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'category_id' => $traditions->id,
                'created_by' => $admin->id,
                'title_fr' => 'Fête des Ignames — Bondoukou',
                'slug' => 'fete-ignames-bondoukou-2025',
                'description_fr' => 'Cérémonies d\'action de grâce pour la nouvelle récolte d\'ignames chez les Abron de Bondoukou. Danses royales, libations et processions autour du palais du chef traditionnel.',
                'starts_at' => now()->addMonths(3)->setTime(8, 0),
                'ends_at' => now()->addMonths(3)->addDays(1)->setTime(18, 0),
                'location_name' => 'Palais du Gyaasehene, Bondoukou',
                'city' => 'Bondoukou',
                'latitude' => 8.04080000,
                'longitude' => -2.79810000,
                'is_free' => true,
                'is_recurring' => true,
                'recurrence_rule' => 'FREQ=YEARLY',
                'organizer_name' => 'Chefferie Abron de Bondoukou',
                'status' => 'published',
                'published_at' => now(),
            ],
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(['slug' => $event['slug']], $event);
        }
    }
}
