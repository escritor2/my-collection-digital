<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('color', 32)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('collection_user_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->onDelete('cascade');
            $table->foreignId('user_book_id')->constrained('user_books')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['collection_id', 'user_book_id']);
        });

        Schema::create('tag_user_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->foreignId('user_book_id')->constrained('user_books')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['tag_id', 'user_book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_user_book');
        Schema::dropIfExists('collection_user_book');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('tags');
    }
};
