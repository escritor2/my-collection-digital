<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_book_id')->constrained('user_books')->onDelete('cascade');
            $table->enum('format', ['pdf', 'epub']);
            $table->json('locator'); // { page: number } or { cfi: string }
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'user_book_id']);
            $table->index(['user_id']);
            $table->index(['user_book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_positions');
    }
};
