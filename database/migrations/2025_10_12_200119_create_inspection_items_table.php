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
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspection_id')->index();
            $table->uuid('repaired_by_user_id')->nullable()->index();

            // Item identification and categorization
            $table->string('item_category'); // e.g., Engine, Tires, Brakes, Lights
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->integer('sequence_order')->default(0);

            // Inspection result
            $table->enum('result', ['pass', 'fail', 'na', 'pending'])->default('pending');

            // Defect information
            $table->enum('defect_severity', ['none', 'minor', 'major', 'critical'])->nullable();
            $table->text('defect_notes')->nullable();
            $table->text('repair_recommendation')->nullable();
            $table->string('urgency')->nullable();

            // Measurement data (for items with numeric values)
            $table->string('measurement_value')->nullable();
            $table->string('expected_range')->nullable();
            $table->boolean('within_tolerance')->default(true);

            // Photo evidence and annotations
            $table->json('photo_paths')->nullable(); // Array of photo URLs
            $table->json('annotations')->nullable(); // Array of annotations/marks on photos

            // Repair tracking
            $table->boolean('repair_required')->default(false);
            $table->date('repair_due_date')->nullable();
            $table->boolean('repair_completed')->default(false);
            $table->dateTime('repair_completion_date')->nullable();
            $table->decimal('repair_cost', 10, 2)->nullable();
            $table->text('repair_notes')->nullable();

            // Compliance and safety flags
            $table->boolean('safety_critical')->default(false); // Items critical to vehicle safety
            $table->boolean('compliance_item')->default(false); // Items required for regulatory compliance
            $table->string('compliance_standard')->nullable(); // e.g., "ADR 42/05" for tire tread depth

            $table->timestamps();

            // Foreign keys
            $table->foreign('inspection_id')->references('id')->on('inspections')->onDelete('cascade');
            $table->foreign('repaired_by_user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for common queries
            $table->index('item_category');
            $table->index('result');
            $table->index('defect_severity');
            $table->index('safety_critical');
            $table->index('repair_required');
            $table->index('repair_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
