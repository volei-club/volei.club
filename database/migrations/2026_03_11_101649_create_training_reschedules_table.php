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
        Schema::create('training_reschedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('training_id');
            $table->date('original_date');
            $table->date('new_date');
            $table->time('new_start_time');
            $table->time('new_end_time');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->unique(['training_id', 'original_date'], 'tr_training_id_original_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_reschedules');
    }
};
