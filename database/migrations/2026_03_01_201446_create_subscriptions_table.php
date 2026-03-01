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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscriptions');
        Schema::enableForeignKeyConstraints();

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('club_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 8, 2)->default(0);
            $table->string('period')->default('1_luna'); // 1_luna, 3_luni, 6_luni, 1_an
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
