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
        Schema::create('compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            // Report details
            $table->string('report_number')->unique();
            $table->string('title');
            $table->enum('report_type', [
                'periodic',
                'audit',
                'incident-based',
                'regulatory',
                'custom',
            ])->default('periodic');
            $table->enum('period', [
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'yearly',
                'custom',
            ])->default('monthly');

            // Date range
            $table->date('period_start');
            $table->date('period_end');
            $table->date('report_date');

            // Content
            $table->json('requirements_included')->nullable(); // Array of requirement IDs
            $table->json('metrics')->nullable(); // Summary metrics
            $table->text('executive_summary')->nullable();
            $table->text('key_findings')->nullable();
            $table->text('recommendations')->nullable();

            // Status tracking
            $table->enum('status', [
                'draft',
                'under-review',
                'approved',
                'published',
                'archived',
            ])->default('draft');

            // Ownership
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // File attachments
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('report_type');
            $table->index('period');
            $table->index('period_start');
            $table->index('period_end');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_reports');
    }
};
