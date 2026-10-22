<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Justificatifs envoyés par les prestataires (pièce d'identité du gérant, registre du
 * commerce…). Fichiers stockés sur le disque privé, jamais exposés publiquement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('rejection_reason', 500)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_documents');
    }
};
