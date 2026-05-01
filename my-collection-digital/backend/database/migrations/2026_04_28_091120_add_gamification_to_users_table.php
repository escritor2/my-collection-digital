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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('xp')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->json('badges')->nullable();
            $table->unsignedInteger('streak_days')->default(0);
            $table->timestamp('last_reading_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'level', 'badges', 'streak_days', 'last_reading_at']);
        });
    }
};
