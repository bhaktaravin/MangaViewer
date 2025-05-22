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
        Schema::connection('manga_db')->create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id');
            $table->string('title');
            $table->integer('chapter_number');
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('manga_db')->dropIfExists('chapters');
    }
};
