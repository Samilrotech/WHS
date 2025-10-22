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
        Schema::create('inspection_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inspection_id')->constrained('equipment_inspections')->cascadeOnDelete();

            // Item Details
            $table->integer('sequence_order');
            $table->string('item_code');
            $table->string('category');
            $table->text('question');
            $table->enum('item_type', ['checkbox', 'rating', 'photo_required', 'text_input'])->default('checkbox');

            // Response
            $table->enum('result', ['pass', 'fail', 'na', 'pending'])->default('pending');
            $table->string('response_value')->nullable();
            $table->text('response_notes')->nullable();
            $table->timestamp('responded_at')->nullable();

            // Defect Information
            $table->boolean('defect_identified')->default(false);
            $table->text('defect_description')->nullable();
            $table->text('corrective_action_required')->nullable();
            $table->enum('defect_severity', ['minor', 'moderate', 'major', 'critical'])->nullable();

            // Importance
            $table->boolean('is_critical')->default(false);

            $table->timestamps();

            // Indexes
            $table->index('inspection_id');
            $table->index(['inspection_id', 'result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_checklist_items');
    }
};
