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
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }

            if (!Schema::hasColumn('branches', 'postcode')) {
                $table->string('postcode', 10)->nullable()->after('city');
            }

            if (!Schema::hasColumn('branches', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }

            if (Schema::hasColumn('branches', 'manager_email')) {
                $table->dropColumn('manager_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'manager_email')) {
                $table->string('manager_email')->nullable()->after('phone');
            }

            if (Schema::hasColumn('branches', 'email')) {
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('branches', 'postcode')) {
                $table->dropColumn('postcode');
            }

            if (Schema::hasColumn('branches', 'city')) {
                $table->dropColumn('city');
            }
        });
    }
};
