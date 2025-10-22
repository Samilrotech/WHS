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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->index();
            $table->string('registration_number')->unique();
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('vin_number')->unique()->nullable();
            $table->string('color')->nullable();
            $table->string('fuel_type')->nullable();
            $table->integer('odometer_reading')->default(0);

            // Financial Information
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->enum('depreciation_method', ['straight_line', 'declining_balance'])->default('straight_line');
            $table->decimal('depreciation_rate', 5, 2)->nullable(); // Percentage

            // Insurance Information
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->decimal('insurance_premium', 10, 2)->nullable();

            // Compliance
            $table->date('rego_expiry_date')->nullable();
            $table->date('inspection_due_date')->nullable();

            // Asset Management
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive', 'sold'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('branch_id')
                  ->references('id')
                  ->on('branches')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
