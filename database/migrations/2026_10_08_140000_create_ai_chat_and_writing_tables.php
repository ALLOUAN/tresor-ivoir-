<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Journal de l'assistant : question tronquée (numéros et e-mails masqués), sans identifiant de visiteur.
        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 200);
            $table->string('status', 16);              // answered | no_source | smalltalk | handoff
            $table->float('top_score')->default(0);
            $table->unsignedTinyInteger('sources')->default(0);
            $table->string('lang', 2)->default('fr');
            $table->string('engine', 12)->default('fixed');
            $table->tinyInteger('rating')->nullable();  // 1 = utile, -1 = pas utile
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
            $table->index('status');
        });

        // Usage de l'aide à la rédaction : qui, quelle action, et la suggestion a-t-elle été appliquée ?
        Schema::create('ai_writing_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('action', 16);              // summary | titles | seo | translate
            $table->string('engine', 16);              // extractive | anthropic | none
            $table->string('status', 16)->default('ok'); // ok | rejected | unavailable
            $table->unsignedInteger('chars_in')->default(0);
            $table->unsignedInteger('chars_out')->default(0);
            $table->boolean('applied')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_writing_logs');
        Schema::dropIfExists('ai_chat_logs');
    }
};
