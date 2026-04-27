<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_media_items', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->after('original_name');
            $table->string('alt_text', 300)->nullable()->after('title');
            $table->string('caption', 500)->nullable()->after('alt_text');
            $table->string('credit', 255)->nullable()->after('caption');
            $table->string('section', 80)->default('home_gallery')->after('credit');
            $table->boolean('is_active')->default(true)->after('section');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->unsignedSmallInteger('display_order')->default(0)->after('is_featured');
            $table->timestamp('published_at')->nullable()->after('display_order');

            $table->index(['section', 'is_active']);
            $table->index(['section', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::table('site_media_items', function (Blueprint $table) {
            $table->dropIndex(['section', 'is_active']);
            $table->dropIndex(['section', 'display_order']);
            $table->dropColumn([
                'title',
                'alt_text',
                'caption',
                'credit',
                'section',
                'is_active',
                'is_featured',
                'display_order',
                'published_at',
            ]);
        });
    }
};

