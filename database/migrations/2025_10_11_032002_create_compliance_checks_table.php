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
        Schema::create('compliance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained('compliance_requirements')->onDelete('cascade');

            // Check details
            $table->string('check_number')->unique();
            $table->date('check_date');
            $table->foreignUuid('checked_by')->constrained('users')->onDelete('cascade');

            // Results
            $table->enum('result', [
                'pass',
                'fail',
                'partial',
                'not-applicable',
            ])->default('pass');
            $table->integer('score')->nullable(); // 0-100
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();

            // Evidence
            $table->json('evidence_files')->nullable();
            $table->text('notes')->nullable();

            // Follow-up
            $table->boolean('requires_action')->default(false);
            $table->date('action_due_date')->nullable();
            $table->foreignUuid('action_owner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('action_status', [
                'pending',
                'in-progress',
                'completed',
                'cancelled',
            ])->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('requirement_id');
            $table->index('check_date');
            $table->index('result');
            $table->index('requires_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_checks');
    }
};
