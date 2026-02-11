<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('session_number')->default(1);
            $table->string('type')->default('sequential');
            $table->string('status')->default('draft');
            $table->text('setup_text')->nullable();
            $table->text('recap_text')->nullable();
            $table->text('dm_notes')->nullable();
            $table->text('generated_narrative')->nullable();
            $table->text('generated_bullets')->nullable();
            $table->text('generated_hooks')->nullable();
            $table->text('generated_world_state')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
