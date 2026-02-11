<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alignment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_session_id')->nullable()->constrained()->nullOnDelete();
            $table->text('action_description');
            $table->json('tags')->nullable();
            $table->integer('good_evil_delta')->default(0);
            $table->integer('law_chaos_delta')->default(0);
            $table->integer('ai_suggested_ge')->nullable();
            $table->integer('ai_suggested_lc')->nullable();
            $table->boolean('dm_overridden')->default(false);
            $table->timestamps();

            $table->index(['character_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alignment_events');
    }
};
