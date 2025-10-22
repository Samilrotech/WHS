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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign keys
            $table->uuid('branch_id')->index();
            $table->uuid('vehicle_id')->index();
            $table->uuid('created_by_user_id')->index();

            // Schedule information
            $table->string('schedule_name');
            $table->text('description')->nullable();
            $table->enum('schedule_type', ['preventive', 'corrective', 'inspection', 'service', 'repair'])->default('preventive');

            // Recurrence settings
            $table->enum('recurrence_type', ['time_based', 'odometer_based', 'engine_hours_based', 'combined'])->default('time_based');
            $table->integer('recurrence_interval')->nullable()->comment('Days for time-based');
            $table->integer('odometer_interval')->nullable()->comment('Kilometers');
            $table->integer('engine_hours_interval')->nullable()->comment('Hours');

            // Scheduling dates
            $table->date('start_date');
            $table->date('next_due_date');
            $table->date('last_completed_date')->nullable();
            $table->integer('completed_count')->default(0);
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');

            // Cost tracking
            $table->decimal('estimated_cost_per_service', 10, 2)->nullable();
            $table->decimal('actual_total_cost', 10, 2)->nullable();

            // Vendor information
            $table->string('preferred_vendor')->nullable();
            $table->string('vendor_contact')->nullable();

            // Parts management
            $table->json('required_parts')->nullable();
            $table->boolean('auto_order_parts')->default(false);

            // Notification settings
            $table->integer('reminder_days_before')->nullable()->default(7);
            $table->boolean('email_notifications')->default(false);
            $table->boolean('sms_notifications')->default(false);

            // Priority
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index('schedule_type');
            $table->index('status');
            $table->index('next_due_date');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
