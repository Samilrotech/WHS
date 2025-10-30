<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('vehicles', 'branch_id')) {
            DB::statement('ALTER TABLE vehicles MODIFY branch_id CHAR(36) NULL');
        }

        Schema::table('vehicle_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_assignments', 'branch_id')) {
                $table->uuid('branch_id')->nullable()->after('user_id');
                $table->foreign('branch_id')
                    ->references('id')
                    ->on('branches')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_assignments', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });

        if (Schema::hasColumn('vehicles', 'branch_id')) {
            DB::statement('ALTER TABLE vehicles MODIFY branch_id CHAR(36) NOT NULL');
        }
    }
};
