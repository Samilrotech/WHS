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
        Schema::create('equipment_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('equipment_id')->constrained('warehouse_equipment')->cascadeOnDelete();
            $table->foreignUuid('inspector_user_id')->constrained('users');

            // Inspection Details
            $table->string('inspection_number')->unique();
            $table->enum('inspection_type', [
                'pre_start',
                'scheduled_inspection',
                'post_incident',
                'defect_repair_verification',
                'compliance_audit',
                'annual_inspection'
            ]);

            // Scheduling
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Status Workflow
            $table->enum('status', [
                'scheduled',
                'in_progress',
                'completed',
                'submitted',
                'approved',
                'rejected',
                'cancelled'
            ])->default('scheduled');

            // Inspection Results
            $table->integer('total_items')->default(0);
            $table->integer('completed_items')->default(0);
            $table->decimal('inspection_score', 5, 2)->nullable();
            $table->boolean('passed')->nullable();

            // Defect Tracking
            $table->boolean('defects_found')->default(false);
            $table->integer('defect_count')->default(0);
            $table->enum('severity', ['none', 'minor', 'moderate', 'major', 'critical'])->default('none');
            $table->boolean('escalation_required')->default(false);

            // Inspector Information
            $table->text('inspector_notes')->nullable();
            $table->string('inspector_signature_path')->nullable();

            // Review Information
            $table->foreignUuid('reviewer_user_id')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_comments')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('equipment_id');
            $table->index('inspector_user_id');
            $table->index('inspection_type');
            $table->index('status');
            $table->index(['branch_id', 'status']);
            $table->index(['equipment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_inspections');
    }
};
