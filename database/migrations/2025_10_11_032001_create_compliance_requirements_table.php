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
        Schema::create('compliance_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            // Requirement details
            $table->string('requirement_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'legal',
                'regulatory',
                'industry',
                'internal',
                'certification',
            ])->default('internal');

            // Compliance metadata
            $table->enum('frequency', [
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'yearly',
                'once',
            ])->default('monthly');
            $table->date('due_date')->nullable();
            $table->date('last_review_date')->nullable();
            $table->date('next_review_date')->nullable();

            // Ownership
            $table->foreignUuid('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('reviewer_id')->nullable()->constrained('users')->onDelete('set null');

            // Status tracking
            $table->enum('status', [
                'compliant',
                'non-compliant',
                'partial',
                'not-applicable',
                'under-review',
            ])->default('under-review');
            $table->integer('compliance_score')->nullable(); // 0-100

            // Evidence and documentation
            $table->text('evidence_required')->nullable();
            $table->json('evidence_files')->nullable();
            $table->text('notes')->nullable();

            // Risk and impact
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('non_compliance_impact')->nullable();

            // Audit trail
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('category');
            $table->index('status');
            $table->index('due_date');
            $table->index('next_review_date');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_requirements');
    }
};
