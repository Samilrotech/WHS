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
        Schema::create('safety_inspection_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign keys
            $table->uuid('branch_id')->index();
            $table->uuid('created_by_user_id')->index();

            // Template information
            $table->string('template_name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();

            // Checklist configuration
            $table->json('checklist_items')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();

            // Photo and signature requirements
            $table->boolean('requires_photos')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->integer('photo_minimum_count')->nullable();

            // Scoring configuration
            $table->boolean('is_scored')->default(false);
            $table->integer('pass_threshold')->nullable()->comment('Percentage required to pass');
            $table->enum('scoring_method', ['percentage', 'weighted', 'pass_fail'])->default('percentage');

            // Compliance and scheduling
            $table->boolean('is_mandatory')->default(false);
            $table->string('frequency')->nullable()->comment('daily, weekly, monthly, etc.');
            $table->integer('reminder_days_before')->nullable();

            // Regulatory information
            $table->json('regulatory_references')->nullable();
            $table->json('compliance_requirements')->nullable();
            $table->json('required_certifications')->nullable();

            // Template lifecycle
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->integer('version')->default(1);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();

            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index('template_name');
            $table->index('category');
            $table->index('status');
            $table->index('is_mandatory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_inspection_templates');
    }
};
