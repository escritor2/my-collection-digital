<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->integer('target_value');
            $table->enum('target_unit', ['pages', 'minutes', 'books']);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_goals');
    }
};