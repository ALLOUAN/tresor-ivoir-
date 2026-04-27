<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_media_items')) {
            return;
        }

        Schema::table('site_media_items', function (Blueprint $table) {
            if (! Schema::hasColumn('site_media_items', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_media_items')) {
            return;
        }

        Schema::table('site_media_items', function (Blueprint $table) {
            if (Schema::hasColumn('site_media_items', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
