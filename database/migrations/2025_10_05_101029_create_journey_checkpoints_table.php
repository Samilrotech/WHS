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
        Schema::create('journey_checkpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('journey_id')->index();

            // Check-in Data
            $table->dateTime('checkin_time');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_name')->nullable();

            // Check-in Type
            $table->enum('type', [
                'scheduled',    // Regular scheduled check-in
                'manual',       // User-initiated check-in
                'automatic',    // Auto check-in (GPS/app)
                'missed',       // System-detected missed check-in
                'emergency',    // Emergency assistance request
            ])->default('manual');

            // Status & Health
            $table->enum('status', ['ok', 'assistance_needed', 'emergency'])->default('ok');
            $table->text('notes')->nullable();
            $table->text('issues_reported')->nullable();

            // Photos & Evidence
            $table->json('photo_paths')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('journey_id')->references('id')->on('journeys')->onDelete('cascade');

            // Indexes
            $table->index('checkin_time');
            $table->index(['journey_id', 'checkin_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journey_checkpoints');
    }
};
