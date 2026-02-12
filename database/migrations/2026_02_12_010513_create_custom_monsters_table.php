<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_monsters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('type')->nullable();
            $table->string('alignment')->nullable();
            $table->unsignedInteger('armor_class')->default(10);
            $table->unsignedInteger('hit_points')->default(1);
            $table->string('hit_dice')->nullable();
            $table->json('speed')->nullable();
            $table->unsignedTinyInteger('strength')->default(10);
            $table->unsignedTinyInteger('dexterity')->default(10);
            $table->unsignedTinyInteger('constitution')->default(10);
            $table->unsignedTinyInteger('intelligence')->default(10);
            $table->unsignedTinyInteger('wisdom')->default(10);
            $table->unsignedTinyInteger('charisma')->default(10);
            $table->float('challenge_rating')->nullable();
            $table->unsignedInteger('xp')->nullable();
            $table->json('special_abilities')->nullable();
            $table->json('actions')->nullable();
            $table->json('legendary_actions')->nullable();
            $table->json('senses')->nullable();
            $table->string('languages')->nullable();
            $table->json('damage_vulnerabilities')->nullable();
            $table->json('damage_resistances')->nullable();
            $table->json('damage_immunities')->nullable();
            $table->json('condition_immunities')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_monsters');
    }
};
