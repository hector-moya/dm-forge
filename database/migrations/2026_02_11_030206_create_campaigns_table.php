<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('premise')->nullable();
            $table->text('lore')->nullable();
            $table->text('world_rules')->nullable();
            $table->string('theme_tone')->nullable();
            $table->json('special_mechanics')->nullable();
            $table->string('status')->default('draft');
            $table->text('bible_cache')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
