<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_monsters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('hp_max')->default(1);
            $table->unsignedInteger('hp_current')->nullable();
            $table->unsignedInteger('armor_class')->default(10);
            $table->integer('initiative')->nullable();
            $table->json('stats')->nullable();
            $table->json('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_monsters');
    }
};
