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
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index(); // the athlete
            $table->uuid('coach_id')->index(); // who recorded
            $table->date('log_date');

            // Numeric Metrics
            $table->float('weight')->nullable();
            $table->float('vertical_jump')->nullable(); // Detenta
            $table->float('serve_speed')->nullable();

            // Rating Metrics (1-5)
            $table->tinyInteger('reception_rating')->nullable();
            $table->tinyInteger('attack_rating')->nullable();
            $table->tinyInteger('block_rating')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
