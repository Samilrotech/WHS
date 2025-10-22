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
        Schema::create('evacuation_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('emergency_alert_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('evacuation_time');
            $table->string('assembly_point')->nullable();
            $table->timestamp('arrived_at_assembly')->nullable();
            $table->enum('status', ['evacuating', 'at_assembly', 'accounted_for', 'missing'])->default('evacuating');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'emergency_alert_id', 'status']);
            $table->index(['user_id', 'evacuation_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evacuation_records');
    }
};
