<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('followed_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['follower_id', 'followed_id']);
        });

        Schema::create('book_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('title')->nullable();
            $table->text('content');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->index(['book_id', 'is_public']);
        });

        Schema::create('curated_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('curated_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curated_list_id')->constrained('curated_lists')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['curated_list_id', 'book_id']);
        });

        Schema::create('shared_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reader_annotation_id')->constrained('reader_annotations')->onDelete('cascade');
            $table->boolean('is_public')->default(true);
            $table->string('share_token')->unique();
            $table->timestamps();
        });

        Schema::create('book_clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('book_club_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_club_id')->constrained('book_clubs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role', 20)->default('member'); // owner | member
            $table->timestamps();
            $table->unique(['book_club_id', 'user_id']);
        });

        Schema::create('book_club_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_club_id')->constrained('book_clubs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_club_posts');
        Schema::dropIfExists('book_club_memberships');
        Schema::dropIfExists('book_clubs');
        Schema::dropIfExists('shared_highlights');
        Schema::dropIfExists('curated_list_items');
        Schema::dropIfExists('curated_lists');
        Schema::dropIfExists('book_reviews');
        Schema::dropIfExists('user_follows');
    }
};
