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
        Schema::create('hazards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('risk_assessment_id')->constrained('risk_assessments')->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('hazard_type');
            $table->text('description');
            $table->text('potential_consequences');
            $table->integer('persons_at_risk')->default(0);
            $table->json('affected_groups')->nullable(); // ['employees', 'contractors', 'public']
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['branch_id', 'hazard_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazards');
    }
};
