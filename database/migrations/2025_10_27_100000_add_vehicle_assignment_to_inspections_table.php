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
        Schema::table('inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('inspections', 'vehicle_assignment_id')) {
                $table->uuid('vehicle_assignment_id')->nullable()->after('vehicle_id');
                $table->foreign('vehicle_assignment_id')
                    ->references('id')
                    ->on('vehicle_assignments')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            if (Schema::hasColumn('inspections', 'vehicle_assignment_id')) {
                $table->dropForeign(['vehicle_assignment_id']);
                $table->dropColumn('vehicle_assignment_id');
            }
        });
    }
};
