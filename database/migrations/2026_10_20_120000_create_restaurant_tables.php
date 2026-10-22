<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plan de salle : tables du restaurant (numéro, places, zone, forme) que le client choisit en
 * réservant ; une table réservée apparaît grisée sur le créneau concerné.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->string('label', 30);                       // ex. « T4 », « Terrasse 2 »
            $table->unsignedTinyInteger('seats');               // nombre de places
            $table->string('zone', 40)->default('Salle');       // Salle, Terrasse, VIP, Bar…
            $table->string('shape', 10)->default('round');      // round | square | long
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['restaurant_id', 'is_active']);
        });

        Schema::table('booking_requests', function (Blueprint $table) {
            $table->foreignId('restaurant_table_id')->nullable()->after('item_id')
                ->constrained('restaurant_tables')->nullOnDelete();
        });

        Schema::table('restaurants', function (Blueprint $table) {
            // Durée pendant laquelle une table réservée reste occupée (et donc grisée).
            $table->unsignedSmallInteger('table_duration_minutes')->default(90)->after('slot_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', fn (Blueprint $t) => $t->dropConstrainedForeignId('restaurant_table_id'));
        Schema::table('restaurants', fn (Blueprint $t) => $t->dropColumn('table_duration_minutes'));
        Schema::dropIfExists('restaurant_tables');
    }
};
