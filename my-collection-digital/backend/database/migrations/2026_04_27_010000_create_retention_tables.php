<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_retention_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('daily_goal_pages')->default(20);
            $table->unsignedInteger('weekly_goal_pages')->default(140);
            $table->boolean('reminders_enabled')->default(true);
            $table->unsignedTinyInteger('reminder_hour')->default(20); // 0-23
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->timestamps();
        });

        Schema::create('imported_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('source', 32); // kindle | goodreads | csv
            $table->string('book_title');
            $table->string('author')->nullable();
            $table->text('quote')->nullable();
            $table->text('note')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('highlighted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reading_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 64); // goal_at_risk | weekly_summary
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_notifications');
        Schema::dropIfExists('imported_highlights');
        Schema::dropIfExists('user_retention_settings');
    }
};
