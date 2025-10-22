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
        Schema::create('tool_custody_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('equipment_id')->constrained('warehouse_equipment')->cascadeOnDelete();
            $table->foreignUuid('custodian_user_id')->constrained('users');

            // Checkout Information
            $table->timestamp('checked_out_at');
            $table->date('expected_return_date');
            $table->text('purpose')->nullable();
            $table->text('checkout_notes')->nullable();
            $table->enum('condition_on_checkout', ['excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');

            // Checkin Information
            $table->timestamp('checked_in_at')->nullable();
            $table->text('checkin_notes')->nullable();
            $table->enum('condition_on_checkin', ['excellent', 'good', 'fair', 'poor', 'damaged'])->nullable();
            $table->boolean('damage_reported')->default(false);
            $table->text('damage_description')->nullable();

            // Status
            $table->enum('status', ['checked_out', 'returned', 'overdue', 'lost'])->default('checked_out');
            $table->boolean('is_overdue')->default(false);
            $table->integer('days_overdue')->default(0);

            // Approval (if required for high-value equipment)
            $table->foreignUuid('approved_by_user_id')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('equipment_id');
            $table->index('custodian_user_id');
            $table->index('status');
            $table->index(['branch_id', 'status']);
            $table->index(['equipment_id', 'status']);
            $table->index(['custodian_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_custody_logs');
    }
};
