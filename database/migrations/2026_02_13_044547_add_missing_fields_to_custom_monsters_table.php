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
        Schema::table('custom_monsters', function (Blueprint $table) {
            $table->string('subtype')->nullable()->after('type');
            $table->string('armor_class_type')->nullable()->after('armor_class');
            $table->json('proficiencies')->nullable()->after('charisma');
            $table->json('reactions')->nullable()->after('legendary_actions');
            $table->string('image_url')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('custom_monsters', function (Blueprint $table) {
            $table->dropColumn(['subtype', 'armor_class_type', 'proficiencies', 'reactions', 'image_url']);
        });
    }
};
