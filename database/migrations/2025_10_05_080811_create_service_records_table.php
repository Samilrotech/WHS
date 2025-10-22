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
        Schema::create('service_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vehicle_id')->index();
            $table->date('service_date');
            $table->enum('service_type', [
                'routine_maintenance',
                'repair',
                'inspection',
                'tire_replacement',
                'oil_change',
                'brake_service',
                'transmission',
                'other'
            ]);
            $table->string('service_provider');
            $table->decimal('cost', 10, 2);
            $table->integer('odometer_at_service');
            $table->text('description')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->date('next_service_due')->nullable();
            $table->integer('next_service_odometer')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
