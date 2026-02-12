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
            $table->text('voice_description')->nullable()->after('motivation');
            $table->text('speech_patterns')->nullable()->after('voice_description');
            $table->json('catchphrases')->nullable()->after('speech_patterns');
        });
    }

    public function down(): void
    {
        Schema::table('npcs', function (Blueprint $table) {
            $table->dropColumn(['voice_description', 'speech_patterns', 'catchphrases']);
        });
    }
};
