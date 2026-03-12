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
        Schema::table('session_logs', function (Blueprint $table) {
            $table->json('character_ids')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('session_logs', function (Blueprint $table) {
            $table->dropColumn('character_ids');
        });
    }
};
