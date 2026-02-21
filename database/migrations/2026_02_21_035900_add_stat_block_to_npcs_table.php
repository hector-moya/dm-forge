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
        Schema::table('npcs', function (Blueprint $table) {
            $table->text('backstory')->nullable()->after('motivation');
            $table->string('race')->nullable()->after('backstory');
            $table->string('size')->nullable()->after('race');
            $table->string('alignment')->nullable()->after('size');
            $table->unsignedSmallInteger('armor_class')->nullable()->after('alignment');
            $table->string('armor_type')->nullable()->after('armor_class');
            $table->unsignedSmallInteger('hp_max')->nullable()->after('armor_type');
            $table->string('hit_dice')->nullable()->after('hp_max');
            $table->string('speed')->nullable()->after('hit_dice');
            $table->string('challenge_rating')->nullable()->after('speed');
        });
    }

    public function down(): void
    {
        Schema::table('npcs', function (Blueprint $table) {
            $table->dropColumn([
                'backstory', 'race', 'size', 'alignment',
                'armor_class', 'armor_type', 'hp_max', 'hit_dice',
                'speed', 'challenge_rating',
            ]);
        });
    }
};
