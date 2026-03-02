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
        Schema::create('messages', function (Blueprint $populated) {
            $populated->uuid('id')->primary();
            $populated->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $populated->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $populated->text('content');
            $populated->string('type')->default('text');
            $populated->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
