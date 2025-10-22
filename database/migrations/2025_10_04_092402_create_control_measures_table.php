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
        Schema::create('control_measures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hazard_id')->constrained('hazards')->onDelete('cascade');
            $table->enum('hierarchy', ['elimination', 'substitution', 'engineering', 'administrative', 'ppe']);
            $table->text('description');
            $table->foreignUuid('responsible_person')->nullable()->constrained('users')->onDelete('set null');
            $table->date('implementation_date')->nullable();
            $table->enum('status', ['planned', 'implemented', 'verified'])->default('planned');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['hazard_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_measures');
    }
};
