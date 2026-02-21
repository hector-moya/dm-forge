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
        Schema::table('characters', function (Blueprint $table) {
            $table->string('race')->nullable()->after('class');
            $table->string('background')->nullable()->after('race');
            $table->unsignedSmallInteger('speed')->nullable()->after('armor_class');
            $table->unsignedSmallInteger('proficiency_bonus')->nullable()->after('speed');
            $table->unsignedInteger('experience_points')->default(0)->after('proficiency_bonus');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['race', 'background', 'speed', 'proficiency_bonus', 'experience_points']);
        });
    }
};
