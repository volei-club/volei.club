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
        Schema::create('conversation_user', function (Blueprint $populated) {
            $populated->id();
            $populated->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $populated->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $populated->timestamp('last_read_at')->nullable();
            $populated->timestamps();

            $populated->unique(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_user');
    }
};
