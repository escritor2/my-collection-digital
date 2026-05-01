<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('cover_url')->nullable()->after('page_count');
            $table->string('language', 16)->nullable()->after('cover_url');
            $table->string('publisher')->nullable()->after('language');
            $table->string('published_date', 32)->nullable()->after('publisher');
            $table->json('categories')->nullable()->after('published_date');

            $table->string('google_volume_id')->nullable()->unique()->after('categories');
            $table->string('open_library_key')->nullable()->unique()->after('google_volume_id');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['google_volume_id']);
            $table->dropUnique(['open_library_key']);
            $table->dropColumn([
                'cover_url',
                'language',
                'publisher',
                'published_date',
                'categories',
                'google_volume_id',
                'open_library_key',
            ]);
        });
    }
};
