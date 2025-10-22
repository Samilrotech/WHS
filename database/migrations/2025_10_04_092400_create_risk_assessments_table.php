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
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('category', ['warehouse', 'pos-installation', 'on-road', 'office', 'contractor']);
            $table->string('task_description');
            $table->string('location');
            $table->date('assessment_date');

            // Initial Risk (Before Controls)
            $table->integer('initial_likelihood')->comment('1-5');
            $table->integer('initial_consequence')->comment('1-5');
            $table->integer('initial_risk_score')->comment('1-25');
            $table->enum('initial_risk_level', ['green', 'yellow', 'orange', 'red']);

            // Residual Risk (After Controls)
            $table->integer('residual_likelihood')->comment('1-5');
            $table->integer('residual_consequence')->comment('1-5');
            $table->integer('residual_risk_score')->comment('1-25');
            $table->enum('residual_risk_level', ['green', 'yellow', 'orange', 'red']);

            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->date('review_date')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['branch_id', 'category', 'status']);
            $table->index(['initial_risk_level', 'residual_risk_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
