<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->json('quick_replies')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('providers', fn (Blueprint $table) => $table->dropColumn('quick_replies'));
    }
};
