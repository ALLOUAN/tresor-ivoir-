<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Position manuelle (1 à 5) dans la section « À la une » de l'accueil,
     * qui affiche jusqu'à 5 articles à la une dans un ordre fixe (1 = article
     * principal, 2-3 = colonne gauche, 4-5 = colonne droite — voir
     * welcome.blade.php et la requête d'accueil dans routes/web.php).
     * Nullable : un article « à la une » sans position explicite se classe
     * après ceux qui en ont une (repli sur la date de publication).
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->tinyInteger('featured_position')->nullable()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('featured_position');
        });
    }
};
