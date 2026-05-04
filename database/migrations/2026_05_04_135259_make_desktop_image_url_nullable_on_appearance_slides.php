<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appearance_slides', function (Blueprint $table) {
            $table->string('desktop_image_url', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('appearance_slides', function (Blueprint $table) {
            $table->string('desktop_image_url', 500)->nullable(false)->change();
        });
    }
};
