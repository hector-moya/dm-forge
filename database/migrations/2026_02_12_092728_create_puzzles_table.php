<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('puzzles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scene_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->text('solution');
            $table->text('hint_tier_1')->nullable();
            $table->text('hint_tier_2')->nullable();
            $table->text('hint_tier_3')->nullable();
            $table->string('difficulty'); // easy, medium, hard
            $table->string('puzzle_type'); // riddle, logic, physical, cipher, pattern
            $table->boolean('is_solved')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puzzles');
    }
};
