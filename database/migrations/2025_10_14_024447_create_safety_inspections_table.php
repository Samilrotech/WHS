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
        Schema::create('safety_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign keys
            $table->uuid('branch_id')->index();
            $table->uuid('template_id')->nullable()->index();
            $table->uuid('inspector_user_id')->index();
            $table->uuid('reviewed_by_user_id')->nullable()->index();
            $table->uuid('assigned_to_user_id')->nullable()->index();
            $table->uuid('vehicle_id')->nullable()->index();

            // Identification
            $table->string('inspection_number')->unique();
            $table->enum('inspection_type', ['routine', 'incident', 'spot_check', 'audit', 'pre_operational', 'workplace', 'equipment', 'vehicle'])->default('routine');
            $table->string('title');
            $table->text('description')->nullable();

            // Location details
            $table->string('location')->nullable();
            $table->string('area')->nullable();
            $table->string('asset_tag')->nullable();
            $table->json('gps_coordinates')->nullable();

            // Scheduling and timing
            $table->dateTime('scheduled_date')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'submitted', 'approved', 'rejected', 'cancelled'])->default('scheduled');

            // Inspection results
            $table->integer('total_items')->default(0);
            $table->integer('completed_items')->default(0);
            $table->integer('passed_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->integer('na_items')->default(0);
            $table->decimal('inspection_score', 5, 2)->nullable();
            $table->integer('max_possible_score')->nullable();
            $table->boolean('passed')->default(false);

            // Non-compliance tracking
            $table->boolean('has_non_compliance')->default(false);
            $table->integer('non_compliance_count')->default(0);
            $table->enum('non_compliance_severity', ['none', 'low', 'medium', 'high', 'critical'])->nullable();
            $table->text('non_compliance_summary')->nullable();

            // Escalation
            $table->boolean('escalation_required')->default(false);
            $table->dateTime('escalated_at')->nullable();

            // Media
            $table->integer('photos_count')->default(0);
            $table->json('photo_urls')->nullable();
            $table->string('inspector_signature_path')->nullable();
            $table->string('reviewer_signature_path')->nullable();

            // Notes and comments
            $table->text('inspector_notes')->nullable();
            $table->text('reviewer_comments')->nullable();

            // Environmental conditions
            $table->string('weather_conditions')->nullable();
            $table->integer('temperature')->nullable()->comment('In Celsius');

            // Follow-up
            $table->boolean('requires_follow_up')->default(false);
            $table->date('follow_up_due_date')->nullable();

            // Duration tracking
            $table->integer('duration_minutes')->nullable();

            // Audit trail
            $table->json('audit_log')->nullable();

            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('safety_inspection_templates')->onDelete('set null');
            $table->foreign('inspector_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');

            // Indexes for performance
            $table->index('inspection_type');
            $table->index('status');
            $table->index('scheduled_date');
            $table->index('passed');
            $table->index('has_non_compliance');
            $table->index('escalation_required');
            $table->index('requires_follow_up');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_inspections');
    }
};
