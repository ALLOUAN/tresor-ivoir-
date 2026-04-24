<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('price_quarterly', 10, 2)->nullable()->after('price_monthly');
            $table->decimal('price_semiannual', 10, 2)->nullable()->after('price_quarterly');
            $table->text('benefits_text')->nullable()->after('name_en');
            $table->string('covered_levels', 255)->nullable()->after('benefits_text');
            $table->tinyInteger('is_unlimited_features')->default(0)->after('description_chars');
            $table->tinyInteger('is_promotional')->default(0)->after('is_active');
            $table->timestamp('promo_starts_at')->nullable()->after('is_promotional');
            $table->timestamp('promo_ends_at')->nullable()->after('promo_starts_at');
            $table->string('group_target', 120)->nullable()->after('promo_ends_at');
        });

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->enum('discount_type', ['percent', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('promo_codes');

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'price_quarterly',
                'price_semiannual',
                'benefits_text',
                'covered_levels',
                'is_unlimited_features',
                'is_promotional',
                'promo_starts_at',
                'promo_ends_at',
                'group_target',
            ]);
        });
    }
};
