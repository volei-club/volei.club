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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polimorfic relationship to the changed model
            $table->uuidMorphs('auditable');

            // The user who made the change (if any)
            $table->uuid('user_id')->nullable()->index();

            // Event type (created, updated, deleted, etc.)
            $table->string('event');

            // The actual data changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Request context
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
