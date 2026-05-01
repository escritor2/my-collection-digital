<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_book_id')->constrained('user_books')->onDelete('cascade');
            $table->enum('type', ['bookmark', 'highlight', 'note']);
            $table->json('locator'); // { page, ... } or { cfi, ... }
            $table->text('selected_text')->nullable();
            $table->text('note')->nullable();
            $table->string('color', 32)->nullable(); // highlight color/theme hint
            $table->timestamps();

            $table->index(['user_id', 'user_book_id']);
            $table->index(['user_book_id']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_annotations');
    }
};
