<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remplace la bannière unique (cover_image, string) par une liste de bannières
     * (cover_images, json) — une ethnie peut désormais avoir plusieurs images de
     * bannière. Les valeurs existantes sont préservées (converties en tableau à un
     * élément) avant suppression de l'ancienne colonne.
     */
    public function up(): void
    {
        Schema::table('cultural_peoples', function (Blueprint $table) {
            $table->json('cover_images')->nullable()->after('cover_image');
        });

        DB::table('cultural_peoples')->whereNotNull('cover_image')->where('cover_image', '!=', '')->orderBy('id')
            ->get()->each(function ($row) {
                DB::table('cultural_peoples')->where('id', $row->id)->update([
                    'cover_images' => json_encode([$row->cover_image]),
                ]);
            });

        Schema::table('cultural_peoples', function (Blueprint $table) {
            $table->dropColumn('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('cultural_peoples', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('thumbnail');
        });

        DB::table('cultural_peoples')->whereNotNull('cover_images')->orderBy('id')
            ->get()->each(function ($row) {
                $images = json_decode((string) $row->cover_images, true) ?: [];
                if (! empty($images)) {
                    DB::table('cultural_peoples')->where('id', $row->id)->update([
                        'cover_image' => $images[0],
                    ]);
                }
            });

        Schema::table('cultural_peoples', function (Blueprint $table) {
            $table->dropColumn('cover_images');
        });
    }
};
