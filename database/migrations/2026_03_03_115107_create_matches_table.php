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
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('club_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('team_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('squad_id')->constrained()->onDelete('cascade');
            $table->string('opponent_name');
            $table->string('location');
            $table->dateTime('match_date');

            // Score for 5 sets
            $table->integer('set1_home')->nullable();
            $table->integer('set1_away')->nullable();
            $table->integer('set2_home')->nullable();
            $table->integer('set2_away')->nullable();
            $table->integer('set3_home')->nullable();
            $table->integer('set3_away')->nullable();
            $table->integer('set4_home')->nullable();
            $table->integer('set4_away')->nullable();
            $table->integer('set5_home')->nullable();
            $table->integer('set5_away')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
