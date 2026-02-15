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
        Schema::table('world_events', function (Blueprint $table) {
            $table->foreignId('npc_id')->nullable()->after('location_id')->constrained('npcs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_events', function (Blueprint $table) {
            $table->dropForeign(['npc_id']);
            $table->dropColumn('npc_id');
        });
    }
};
