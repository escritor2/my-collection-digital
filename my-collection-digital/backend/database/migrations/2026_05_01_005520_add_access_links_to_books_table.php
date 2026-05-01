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
        Schema::table('books', function (Blueprint $table) {
            $table->string('preview_link', 500)->nullable();
            $table->string('web_reader_link', 500)->nullable();
            $table->string('pdf_link', 1000)->nullable();
            $table->string('epub_link', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['preview_link', 'web_reader_link', 'pdf_link', 'epub_link']);
        });
    }
};
