<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->unsignedTinyInteger('percent_off')->nullable();
            $table->unsignedInteger('amount_off_xof')->nullable();
            $table->unsignedInteger('min_amount_xof')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['provider_id', 'code']);
        });

        foreach (['reservations', 'booking_requests'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->string('promo_code', 30)->nullable();
                $table->unsignedInteger('promo_discount_xof')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['reservations', 'booking_requests'] as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->dropColumn(['promo_code', 'promo_discount_xof']));
        }
        Schema::dropIfExists('provider_promo_codes');
    }
};
