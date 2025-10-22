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
        Schema::create('journeys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->index();
            $table->uuid('user_id')->index(); // The lone worker/driver
            $table->uuid('vehicle_id')->nullable()->index(); // Optional vehicle assignment

            // Journey Planning
            $table->string('title');
            $table->text('purpose')->nullable();
            $table->string('destination');
            $table->string('destination_address')->nullable();
            $table->decimal('destination_latitude', 10, 7)->nullable();
            $table->decimal('destination_longitude', 10, 7)->nullable();

            // Route Information
            $table->text('planned_route')->nullable(); // JSON route waypoints
            $table->decimal('estimated_distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration_minutes')->nullable();

            // Journey Schedule
            $table->dateTime('planned_start_time');
            $table->dateTime('planned_end_time');
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();

            // Check-in Configuration
            $table->integer('checkin_interval_minutes')->default(60); // How often to check in
            $table->dateTime('last_checkin_time')->nullable();
            $table->dateTime('next_checkin_due')->nullable();
            $table->boolean('checkin_overdue')->default(false);

            // Emergency & Safety
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('hazards_identified')->nullable();
            $table->text('control_measures')->nullable();

            // Journey Status
            $table->enum('status', [
                'planned',      // Journey created but not started
                'active',       // Journey in progress
                'completed',    // Journey finished normally
                'overdue',      // Check-in missed, needs attention
                'emergency',    // Emergency assistance requested
                'cancelled',    // Journey cancelled
            ])->default('planned');

            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');

            // Indexes for performance
            $table->index('status');
            $table->index('planned_start_time');
            $table->index('next_checkin_due');
            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};
