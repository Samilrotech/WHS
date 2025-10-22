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
        Schema::create('emergency_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('incident_type', ['fire', 'medical', 'evacuation', 'chemical_spill', 'natural_disaster', 'security', 'other']);
            $table->text('description');
            $table->json('steps'); // Array of procedure steps
            $table->text('equipment_needed')->nullable();
            $table->text('key_contacts')->nullable();
            $table->string('file_path')->nullable(); // PDF/Document attachment
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'incident_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_procedures');
    }
};
