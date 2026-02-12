<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('srd_monsters', function (Blueprint $table) {
            $table->id();
            $table->string('index')->unique();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('type')->nullable();
            $table->string('subtype')->nullable();
            $table->string('alignment')->nullable();
            $table->unsignedInteger('armor_class')->default(10);
            $table->string('armor_class_type')->nullable();
            $table->unsignedInteger('hit_points')->default(1);
            $table->string('hit_dice')->nullable();
            $table->json('speed')->nullable();
            $table->unsignedTinyInteger('strength')->default(10);
            $table->unsignedTinyInteger('dexterity')->default(10);
            $table->unsignedTinyInteger('constitution')->default(10);
            $table->unsignedTinyInteger('intelligence')->default(10);
            $table->unsignedTinyInteger('wisdom')->default(10);
            $table->unsignedTinyInteger('charisma')->default(10);
            $table->json('proficiencies')->nullable();
            $table->json('damage_vulnerabilities')->nullable();
            $table->json('damage_resistances')->nullable();
            $table->json('damage_immunities')->nullable();
            $table->json('condition_immunities')->nullable();
            $table->json('senses')->nullable();
            $table->string('languages')->nullable();
            $table->float('challenge_rating')->default(0);
            $table->unsignedInteger('xp')->default(0);
            $table->json('special_abilities')->nullable();
            $table->json('actions')->nullable();
            $table->json('legendary_actions')->nullable();
            $table->json('reactions')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('challenge_rating');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srd_monsters');
    }
};
