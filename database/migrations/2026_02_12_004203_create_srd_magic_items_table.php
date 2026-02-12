<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('srd_magic_items', function (Blueprint $table) {
            $table->id();
            $table->string('index')->unique();
            $table->string('name');
            $table->string('equipment_category');
            $table->string('rarity');
            $table->text('description')->nullable();
            $table->boolean('variant')->default(false);
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('rarity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srd_magic_items');
    }
};
