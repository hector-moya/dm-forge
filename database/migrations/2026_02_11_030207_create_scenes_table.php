<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_revealed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['game_session_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};
