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
        Schema::create('encounter_monster_loot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_monster_id')->constrained('encounter_monsters')->cascadeOnDelete();
            $table->string('lootable_type');
            $table->unsignedBigInteger('lootable_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['lootable_type', 'lootable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounter_monster_loot');
    }
};
