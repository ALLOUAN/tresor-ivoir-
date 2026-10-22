<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commandes au restaurant (à emporter ou en livraison), à côté des réservations de table :
 *  - booking_requests : lignes de commande (plat, quantité, prix au moment de la commande) et mode de retrait ;
 *  - restaurants : le restaurant accepte-t-il les commandes, et livre-t-il ?
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->json('order_items')->nullable()->after('item_id');
            $table->string('fulfillment', 20)->nullable()->after('order_items'); // pickup | delivery
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('accepts_orders')->default(true)->after('max_party_size');
            $table->boolean('offers_delivery')->default(false)->after('accepts_orders');
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', fn (Blueprint $t) => $t->dropColumn(['order_items', 'fulfillment']));
        Schema::table('restaurants', fn (Blueprint $t) => $t->dropColumn(['accepts_orders', 'offers_delivery']));
    }
};
