<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identifiant stable (uuid) de la chambre réservée, en complément de `room_name`.
     * Chaque chambre d'un hébergement dispose déjà d'un `id` uuid stable dans le JSON
     * `accommodations.room_types` (voir AccommodationController::ensureRoomIds) — le
     * stocker ici permet au calendrier de disponibilité et à la vérification de
     * chevauchement de cibler précisément UNE chambre, même si deux chambres d'un
     * même hébergement partagent le même nom (`room_name` seul serait ambigu).
     * Nullable et non contraint : les réservations créées avant cet ajout n'ont pas
     * de room_id et restent identifiées par room_name (repli déjà géré côté modèle).
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('room_id', 40)->nullable()->after('room_name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('room_id');
        });
    }
};
