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
        Schema::create('warehouse_equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained()->cascadeOnDelete();

            // Equipment Identification
            $table->string('equipment_code')->unique();
            $table->string('equipment_name');
            $table->enum('equipment_type', [
                'forklift',
                'pallet_jack',
                'scissor_lift',
                'reach_truck',
                'order_picker',
                'hand_tools',
                'power_tools',
                'safety_equipment',
                'racking',
                'conveyor',
                'loading_dock',
                'other'
            ]);

            // Manufacturer Details
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();

            // Purchase Information
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();

            // Identification & Tracking
            $table->string('qr_code_path')->nullable();
            $table->string('nfc_tag_id')->nullable();

            // Status & Location
            $table->enum('status', ['available', 'in_use', 'maintenance', 'out_of_service', 'retired'])->default('available');
            $table->string('location')->nullable();

            // Safety & Compliance
            $table->integer('load_rating')->nullable()->comment('Maximum load in kg');
            $table->boolean('requires_license')->default(false);
            $table->string('license_type')->nullable();
            $table->boolean('requires_ppe')->default(false);
            $table->json('required_ppe_types')->nullable();

            // Inspection & Maintenance
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_due')->nullable();
            $table->date('maintenance_due_date')->nullable();
            $table->integer('inspection_frequency_days')->default(30);
            $table->integer('maintenance_frequency_days')->default(90);

            // Additional Information
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('equipment_type');
            $table->index('status');
            $table->index(['branch_id', 'equipment_type']);
            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_equipment');
    }
};
