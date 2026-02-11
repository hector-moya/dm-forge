<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('player_name')->nullable();
            $table->string('class')->nullable();
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('hp_max')->default(10);
            $table->unsignedInteger('hp_current')->nullable();
            $table->unsignedInteger('armor_class')->default(10);
            $table->json('stats')->nullable();
            $table->integer('good_evil_score')->default(0);
            $table->integer('law_chaos_score')->default(0);
            $table->string('alignment_label')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
