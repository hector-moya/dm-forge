<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('srd_equipment', function (Blueprint $table) {
            $table->id();
            $table->string('index')->unique();
            $table->string('name');
            $table->string('equipment_category');
            $table->string('weapon_category')->nullable();
            $table->string('weapon_range')->nullable();
            $table->string('armor_category')->nullable();
            $table->float('cost_gp')->nullable();
            $table->float('weight')->nullable();
            $table->text('description')->nullable();
            $table->json('damage')->nullable();
            $table->json('two_handed_damage')->nullable();
            $table->json('range')->nullable();
            $table->json('armor_class')->nullable();
            $table->json('properties')->nullable();
            $table->json('special')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('equipment_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srd_equipment');
    }
};
