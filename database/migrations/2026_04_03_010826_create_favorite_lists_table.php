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
        Schema::create('favorite_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('crossword_favorite_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('favorite_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crossword_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['favorite_list_id', 'crossword_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crossword_favorite_list');
        Schema::dropIfExists('favorite_lists');
    }
};
