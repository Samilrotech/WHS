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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'employment_status')) {
                $table->string('employment_status', 25)->default('active')->after('is_active');
            }

            if (!Schema::hasColumn('users', 'employment_start_date')) {
                $table->date('employment_start_date')->nullable()->after('employment_status');
            }

            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('employment_start_date');
            }

            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');
            }

            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('emergency_contact_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->dropColumn('emergency_contact_phone');
            }

            if (Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->dropColumn('emergency_contact_name');
            }

            if (Schema::hasColumn('users', 'employment_start_date')) {
                $table->dropColumn('employment_start_date');
            }

            if (Schema::hasColumn('users', 'employment_status')) {
                $table->dropColumn('employment_status');
            }
        });
    }
};