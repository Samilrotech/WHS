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
        Schema::create('capas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->index();
            $table->uuid('incident_id')->nullable()->index(); // Link to incident if CAPA is from incident
            $table->uuid('raised_by_user_id')->index(); // User who raised the CAPA
            $table->uuid('assigned_to_user_id')->nullable()->index(); // User responsible for implementation

            // CAPA Details
            $table->string('capa_number')->unique(); // e.g., CAPA-2025-001
            $table->enum('type', ['corrective', 'preventive']); // Corrective or Preventive Action
            $table->string('title');
            $table->text('description');

            // Root Cause Analysis
            $table->text('problem_statement')->nullable();
            $table->text('root_cause_analysis')->nullable();
            $table->text('five_whys')->nullable(); // JSON or text
            $table->text('contributing_factors')->nullable();

            // Action Plan
            $table->text('proposed_action');
            $table->text('implementation_steps')->nullable(); // JSON array of steps
            $table->text('resources_required')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();

            // Timeline
            $table->date('target_completion_date');
            $table->date('actual_completion_date')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();

            // Status & Tracking
            $table->enum('status', [
                'draft',           // Being created
                'submitted',       // Awaiting review
                'approved',        // Approved for implementation
                'in_progress',     // Being implemented
                'completed',       // Implementation complete, awaiting verification
                'verified',        // Effectiveness verified
                'closed',          // Closed successfully
                'rejected',        // Rejected
                'cancelled',       // Cancelled
            ])->default('draft');

            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Verification
            $table->date('verification_date')->nullable();
            $table->uuid('verified_by_user_id')->nullable()->index();
            $table->text('verification_method')->nullable();
            $table->text('verification_results')->nullable();
            $table->boolean('effectiveness_confirmed')->default(false);

            // Approval
            $table->uuid('approved_by_user_id')->nullable()->index();
            $table->date('approval_date')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Closure
            $table->uuid('closed_by_user_id')->nullable()->index();
            $table->date('closure_date')->nullable();
            $table->text('closure_notes')->nullable();

            // Documentation
            $table->json('attachment_paths')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('set null');
            $table->foreign('raised_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verified_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('closed_by_user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance
            $table->index('capa_number');
            $table->index('status');
            $table->index('type');
            $table->index('priority');
            $table->index('target_completion_date');
            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capas');
    }
};
