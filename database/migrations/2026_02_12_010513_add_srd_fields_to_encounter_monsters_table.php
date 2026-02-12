<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encounter_monsters', function (Blueprint $table) {
            $table->foreignId('srd_monster_id')->nullable()->after('encounter_id')->constrained('srd_monsters')->nullOnDelete();
            $table->foreignId('custom_monster_id')->nullable()->after('srd_monster_id')->constrained('custom_monsters')->nullOnDelete();
            $table->float('challenge_rating')->nullable()->after('conditions');
            $table->unsignedInteger('xp')->nullable()->after('challenge_rating');
        });
    }

    public function down(): void
    {
        Schema::table('encounter_monsters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('srd_monster_id');
            $table->dropConstrainedForeignId('custom_monster_id');
            $table->dropColumn(['challenge_rating', 'xp']);
        });
    }
};
