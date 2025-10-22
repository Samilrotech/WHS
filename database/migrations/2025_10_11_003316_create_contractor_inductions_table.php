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
        Schema::create('contractor_inductions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('contractor_id')->constrained()->onDelete('cascade');
            $table->foreignId('induction_module_id')->constrained()->onDelete('cascade');

            // Completion tracking
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('time_spent_minutes')->default(0);

            // Video tracking
            $table->boolean('video_watched')->default(false);
            $table->integer('video_progress_percentage')->default(0);

            // Quiz results
            $table->integer('quiz_score')->nullable();
            $table->integer('quiz_attempts')->default(0);
            $table->boolean('quiz_passed')->default(false);

            // Validity
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'expired'])->default('in_progress');

            // Certificate
            $table->string('certificate_number')->nullable()->unique();
            $table->dateTime('certificate_issued_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('contractor_id');
            $table->index('induction_module_id');
            $table->index('status');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_inductions');
    }
};
