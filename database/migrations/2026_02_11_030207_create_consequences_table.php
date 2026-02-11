<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_option_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // immediate, delayed, meta
            $table->text('description');
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consequences');
    }
};
