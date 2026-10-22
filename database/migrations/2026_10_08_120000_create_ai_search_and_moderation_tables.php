<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Requêtes de recherche (texte seul, sans identifiant de visiteur) : mesure des requêtes sans résultat.
        Schema::create('ai_search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('query', 200);
            $table->unsignedSmallInteger('keyword_results')->default(0);   // recherche classique du site
            $table->unsignedSmallInteger('smart_results')->default(0);     // recherche intelligente
            $table->string('did_you_mean', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });

        // Rapports de modération assistée : un rapport par contenu et par nature d'analyse.
        Schema::create('moderation_reports', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type', 24);          // article | review | provider | event | media | accommodation_media
            $table->unsignedBigInteger('subject_id');
            $table->string('kind', 8)->default('text');  // text | image
            $table->string('label', 200)->nullable();    // intitulé affiché dans la file
            $table->string('url', 300)->nullable();
            $table->unsignedTinyInteger('score')->default(0);          // 0 = sain, 100 = très probablement problématique
            $table->string('verdict', 16)->default('ok');              // ok | review | likely
            $table->json('reasons')->nullable();
            $table->json('suggestions')->nullable();                   // catégorie, étiquettes, doublons
            $table->string('content_hash', 80)->nullable();            // évite de ré-analyser un contenu inchangé
            $table->string('status', 12)->default('pending');          // pending | confirmed | dismissed
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id', 'kind']);
            $table->index(['verdict', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_reports');
        Schema::dropIfExists('ai_search_logs');
    }
};
