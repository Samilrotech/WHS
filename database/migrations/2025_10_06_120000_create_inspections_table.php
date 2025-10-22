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
        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->index();
            $table->uuid('vehicle_id')->index();
            $table->uuid('inspector_user_id')->index();
            $table->uuid('approved_by_user_id')->nullable()->index();

            // Inspection identification
            $table->string('inspection_number')->unique();
            $table->enum('inspection_type', [
                'monthly_routine',        // Regular monthly check
                'pre_trip',              // Before journey
                'post_incident',         // After incident or accident
                'annual_compliance',     // Yearly compliance check
                'maintenance_followup',  // After maintenance work
                'random_spot_check'      // Random safety check
            ]);

            // Inspection details
            $table->dateTime('inspection_date');
            $table->integer('odometer_reading')->nullable();
            $table->string('location')->nullable(); // Where inspection performed
            $table->decimal('inspection_hours', 5, 2)->nullable(); // Time spent on inspection

            // Status and workflow
            $table->enum('status', [
                'pending',    // Created but not started
                'in_progress', // Currently being conducted
                'completed',   // Finished by inspector
                'approved',    // Approved by supervisor
                'rejected',    // Rejected - needs re-inspection
                'failed'       // Failed - vehicle cannot be used
            ])->default('pending');

            // Overall assessment
            $table->enum('overall_result', [
                'pass',         // All items passed
                'pass_minor',   // Minor defects noted but safe to operate
                'fail_major',   // Major defects - requires immediate repair
                'fail_critical' // Critical defects - cannot be operated
            ])->nullable();

            // Defect summary
            $table->integer('total_items_checked')->default(0);
            $table->integer('items_passed')->default(0);
            $table->integer('items_failed')->default(0);
            $table->integer('critical_defects')->default(0);
            $table->integer('major_defects')->default(0);
            $table->integer('minor_defects')->default(0);

            // Photos and evidence
            $table->json('photo_paths')->nullable(); // Array of photo URLs
            $table->text('inspector_notes')->nullable();
            $table->text('defects_summary')->nullable();
            $table->text('recommendations')->nullable();

            // Approval workflow
            $table->dateTime('approved_date')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Next inspection scheduling
            $table->date('next_inspection_due')->nullable();

            // Compliance and signatures
            $table->boolean('compliance_verified')->default(false);
            $table->string('inspector_signature_path')->nullable();
            $table->string('approver_signature_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('inspector_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by_user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for common queries
            $table->index('inspection_date');
            $table->index('status');
            $table->index('overall_result');
            $table->index('next_inspection_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
