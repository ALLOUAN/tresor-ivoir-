<?php

namespace Database\Seeders;

use App\Models\BookingRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\ProviderHour;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

/**
 * Démonstration complète d'un restaurant EXISTANT : plan de salle, réservations de tables
 * (tables grisées sur leur créneau), commandes à emporter et en livraison, horaires, carte.
 *
 *   php artisan db:seed --class=RestaurantDemoSeeder --force
 *   DEMO_RESTAURANT=chez-fatou-maquis-du-plateau php artisan db:seed --class=RestaurantDemoSeeder --force
 *
 * Relançable sans doublon : les tables et les plats sont créés seulement s'ils manquent, et les
 * réservations de démonstration (e-mails @demo.tresorsivoire.ci) sont remplacées à chaque passage,
 * avec des dates recalculées à partir d'aujourd'hui. Rien de ce que le restaurant a saisi n'est modifié,
 * hormis l'activation des commandes et de la livraison.
 */
class RestaurantDemoSeeder extends Seeder
{
    public const DEMO_DOMAIN = '@demo.tresorsivoire.ci';

    public function run(): void
    {
        $restaurant = $this->restaurant();
        if (! $restaurant) {
            $this->command?->warn('Aucun restaurant actif rattaché à un prestataire : rien à faire.');

            return;
        }
        $this->command?->info("Restaurant de démonstration : {$restaurant->name} ({$restaurant->slug})");

        // ── Réglages : commandes, livraison, durée d'occupation d'une table ──
        $restaurant->update([
            'accepts_orders' => true,
            'offers_delivery' => true,
            'table_duration_minutes' => $restaurant->table_duration_minutes ?: 90,
            'slot_minutes' => $restaurant->slot_minutes ?: 30,
            'max_party_size' => $restaurant->max_party_size ?: 12,
        ]);

        // ── Horaires (seulement si le prestataire n'en a pas) : fermé le lundi ──
        if (! ProviderHour::where('provider_id', $restaurant->provider_id)->exists()) {
            foreach (range(0, 6) as $day) {
                ProviderHour::create([
                    'provider_id' => $restaurant->provider_id, 'day_of_week' => $day,
                    'open_time' => '11:30', 'close_time' => '23:00', 'is_closed' => $day === 1,
                ]);
            }
            $this->command?->info('✓ Horaires ajoutés : 11 h 30 – 23 h, fermé le lundi.');
        }

        // ── Plan de salle ──
        $tables = [
            ['T1', 2, 'Salle', 'square'], ['T2', 2, 'Salle', 'square'], ['T3', 4, 'Salle', 'round'],
            ['T4', 4, 'Salle', 'round'], ['T5', 4, 'Salle', 'round'], ['T6', 6, 'Salle', 'long'],
            ['T7', 4, 'Terrasse', 'round'], ['T8', 4, 'Terrasse', 'round'], ['T9', 2, 'Terrasse', 'square'],
            ['T10', 2, 'Terrasse', 'square'], ['V1', 8, 'VIP', 'long'], ['V2', 10, 'VIP', 'long'],
        ];
        $created = 0;
        foreach ($tables as $i => [$label, $seats, $zone, $shape]) {
            $t = $restaurant->tables()->firstOrCreate(['label' => $label], ['seats' => $seats, 'zone' => $zone, 'shape' => $shape, 'sort_order' => $i + 1]);
            $created += (int) $t->wasRecentlyCreated;
        }
        $this->command?->info("✓ Plan de salle : {$created} table(s) ajoutée(s), ".$restaurant->tables()->count().' au total.');

        // ── Carte : plats ivoiriens classiques, ajoutés seulement s'ils manquent ──
        $cat = fn (array $names) => MenuCategory::query()->where('is_active', true)
            ->where(fn ($q) => collect($names)->each(fn ($n) => $q->orWhere('name_fr', 'like', "%{$n}%")))->value('id')
            ?? MenuCategory::query()->where('is_active', true)->orderBy('sort_order')->value('id');
        $dishes = [
            ['Garba (attiéké, thon frit)', 1500, ['Plat'], 'Attiéké, thon frit, piment frais, oignons et tomates.'],
            ['Kedjenou de poulet', 6500, ['Plat'], 'Poulet mijoté à l\'étouffée avec légumes, servi avec attiéké ou riz.'],
            ['Placali sauce kplala', 4000, ['Plat'], 'Pâte de manioc fermenté et sauce gluante aux feuilles.'],
            ['Foutou banane, sauce graine', 5500, ['Plat'], 'Banane plantain pilée, sauce à la noix de palme et viande fumée.'],
            ['Dégué (couscous de mil au lait caillé)', 1500, ['Dessert'], 'Dessert frais au mil et lait caillé sucré.'],
            ['Gnamakoudji (jus de gingembre)', 1000, ['Boisson'], 'Jus de gingembre frais, ananas et citron.'],
        ];
        $added = 0;
        foreach ($dishes as $i => [$name, $price, $catNames, $desc]) {
            $item = MenuItem::firstOrCreate(
                ['provider_id' => $restaurant->provider_id, 'name' => $name],
                ['category_id' => $cat($catNames), 'price_xof' => $price, 'description' => $desc, 'is_available' => true, 'sort_order' => 50 + $i, 'images' => []]
            );
            $added += (int) $item->wasRecentlyCreated;
        }
        $this->command?->info("✓ Carte : {$added} plat(s) ajouté(s).");

        // ── Réservations et commandes de démonstration (remplacées à chaque passage) ──
        BookingRequest::where('bookable_id', $restaurant->id)
            ->whereIn('bookable_type', [BookingRequest::TYPE_RESTAURANT, BookingRequest::TYPE_RESTAURANT_ORDER])
            ->where('email', 'like', '%'.self::DEMO_DOMAIN)->delete();

        $byLabel = $restaurant->tables()->get()->keyBy('label');
        $tomorrow = now('Africa/Abidjan')->addDay()->toDateString();
        $later = now('Africa/Abidjan')->addDays(2)->toDateString();
        $bookings = [
            // [date, heure, table, couverts, client, statut]
            [$tomorrow, '12:30', 'T3', 4, 'Aya Kouassi', BookingRequest::STATUS_CONFIRMED],
            [$tomorrow, '13:00', 'T7', 3, 'Koffi Yao', BookingRequest::STATUS_CONFIRMED],
            [$tomorrow, '20:00', 'T1', 2, 'Mariam Traoré', BookingRequest::STATUS_CONFIRMED],
            [$tomorrow, '20:00', 'T5', 4, 'Jean-Marc Bamba', BookingRequest::STATUS_NEW],
            [$tomorrow, '20:30', 'V1', 8, 'Anniversaire Adjoua', BookingRequest::STATUS_CONFIRMED],
            [$tomorrow, '21:00', 'T8', 2, 'Sékou Diabaté', BookingRequest::STATUS_CANCELLED], // annulée : la table reste libre
            [$later, '19:30', 'T6', 6, 'Famille N\'Guessan', BookingRequest::STATUS_CONFIRMED],
            [$later, '20:00', 'T9', 2, 'Awa Ouattara', BookingRequest::STATUS_NEW],
        ];
        $n = 0;
        foreach ($bookings as [$date, $time, $label, $party, $name, $status]) {
            $table = $byLabel[$label] ?? null;
            if (! $table) {
                continue;
            }
            $table->setRelation('restaurant', $restaurant);
            BookingRequest::create([
                'provider_id' => $restaurant->provider_id, 'bookable_type' => BookingRequest::TYPE_RESTAURANT,
                'bookable_id' => $restaurant->id, 'bookable_name' => $restaurant->name,
                'restaurant_table_id' => $table->id, 'item_name' => $table->displayName(),
                'booking_date' => $date, 'booking_time' => $time, 'party_size' => $party,
                'full_name' => $name, 'email' => \Illuminate\Support\Str::slug($name).self::DEMO_DOMAIN, 'phone' => '+225 07 00 00 00 '.str_pad((string) ++$n, 2, '0', STR_PAD_LEFT),
                'status' => $status, 'answered_at' => $status !== BookingRequest::STATUS_NEW ? now() : null,
                'cancelled_at' => $status === BookingRequest::STATUS_CANCELLED ? now() : null,
            ]);
        }

        // Deux commandes : à emporter (confirmée, prix fixé) et en livraison (en attente).
        $menu = MenuItem::query()->available()->where('provider_id', $restaurant->provider_id)->orderBy('price_xof', 'desc')->take(3)->get()->values();
        if ($menu->count() >= 2) {
            $orders = [
                ['pickup', [[$menu[0], 2], [$menu[1], 1]], '12:45', 'Ibrahim Koné', BookingRequest::STATUS_CONFIRMED, null],
                ['delivery', [[$menu[1], 3], [$menu->last(), 2]], '19:45', 'Clarisse Aka', BookingRequest::STATUS_NEW, 'Cocody Riviera 2, près de la pharmacie'],
            ];
            foreach ($orders as [$mode, $lines, $time, $name, $status, $address]) {
                $items = collect($lines)->map(fn ($l) => ['id' => $l[0]->id, 'name' => $l[0]->name, 'qty' => $l[1], 'price_xof' => (int) $l[0]->price_xof])->all();
                BookingRequest::create([
                    'provider_id' => $restaurant->provider_id, 'bookable_type' => BookingRequest::TYPE_RESTAURANT_ORDER,
                    'bookable_id' => $restaurant->id, 'bookable_name' => $restaurant->name,
                    'order_items' => $items, 'fulfillment' => $mode,
                    'item_name' => \Illuminate\Support\Str::limit(BookingRequest::FULFILLMENT_LABELS[$mode].' · '.collect($items)->map(fn ($l) => $l['qty'].' × '.$l['name'])->join(', '), 250),
                    'pickup_location' => $address ? $restaurant->name : null, 'dropoff_location' => $address,
                    'amount_due_xof' => collect($items)->sum(fn ($l) => $l['qty'] * $l['price_xof']),
                    'booking_date' => $tomorrow, 'booking_time' => $time, 'party_size' => collect($items)->sum('qty'),
                    'full_name' => $name, 'email' => \Illuminate\Support\Str::slug($name).self::DEMO_DOMAIN,
                    'status' => $status, 'answered_at' => $status === BookingRequest::STATUS_CONFIRMED ? now() : null,
                ]);
            }
        }

        $this->command?->info('✓ Démonstration : '.count($bookings).' réservations de tables (dont 1 annulée) et 2 commandes, pour demain et après-demain.');
        $this->command?->line("   Plan de salle à voir sur /restaurants/{$restaurant->slug} : demain à 20:00, les tables T1, T5 et V1 sont grisées.");
    }

    /** Restaurant choisi : DEMO_RESTAURANT (slug), sinon La Table du Chef, sinon le premier restaurant actif avec prestataire. */
    private function restaurant(): ?Restaurant
    {
        $base = Restaurant::query()->active()->whereNotNull('provider_id');

        return (clone $base)->where('slug', env('DEMO_RESTAURANT', 'la-table-du-chef-cocody'))->first()
            ?? (clone $base)->orderBy('id')->first();
    }
}
