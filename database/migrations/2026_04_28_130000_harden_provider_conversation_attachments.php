<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_conversation_attachments', function (Blueprint $table) {
            $table->string('thumbnail_path', 500)->nullable()->after('file_path');
            $table->string('checksum_sha256', 64)->nullable()->after('size_bytes');
            $table->string('scan_result', 20)->default('skipped')->after('checksum_sha256');
            $table->timestamp('scanned_at')->nullable()->after('scan_result');
        });
    }

    public function down(): void
    {
        Schema::table('provider_conversation_attachments', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_path', 'checksum_sha256', 'scan_result', 'scanned_at']);
        });
    }
};

