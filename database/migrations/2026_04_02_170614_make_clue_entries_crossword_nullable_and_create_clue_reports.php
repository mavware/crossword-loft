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
        Schema::table('clue_entries', function (Blueprint $table) {
            $table->dropUnique(['crossword_id', 'direction', 'clue_number']);

            $table->foreignId('crossword_id')->nullable()->change();
            $table->string('direction')->nullable()->change();
            $table->unsignedSmallInteger('clue_number')->nullable()->change();

            $table->unique(['crossword_id', 'direction', 'clue_number']);
        });

        Schema::create('clue_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clue_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['clue_entry_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clue_reports');

        Schema::table('clue_entries', function (Blueprint $table) {
            $table->dropUnique(['crossword_id', 'direction', 'clue_number']);

            $table->foreignId('crossword_id')->nullable(false)->change();
            $table->string('direction')->nullable(false)->change();
            $table->unsignedSmallInteger('clue_number')->nullable(false)->change();

            $table->unique(['crossword_id', 'direction', 'clue_number']);
        });
    }
};
