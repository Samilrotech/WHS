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
        Schema::create('compliance_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained('compliance_requirements')->onDelete('cascade');
            $table->foreignId('check_id')->nullable()->constrained('compliance_checks')->onDelete('set null');

            // Action details
            $table->string('action_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Ownership and assignment
            $table->foreignUuid('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('assigned_by')->constrained('users')->onDelete('cascade');

            // Timeline
            $table->date('due_date');
            $table->date('completed_date')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();

            // Status tracking
            $table->enum('status', [
                'pending',
                'in-progress',
                'completed',
                'overdue',
                'cancelled',
            ])->default('pending');
            $table->integer('progress')->default(0); // 0-100

            // Evidence and completion
            $table->text('completion_notes')->nullable();
            $table->json('evidence_files')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('requirement_id');
            $table->index('check_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('due_date');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_actions');
    }
};
