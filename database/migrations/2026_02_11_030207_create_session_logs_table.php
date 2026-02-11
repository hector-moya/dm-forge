<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->text('entry');
            $table->string('type')->default('narrative'); // narrative, decision, combat, note
            $table->json('tags')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['game_session_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
