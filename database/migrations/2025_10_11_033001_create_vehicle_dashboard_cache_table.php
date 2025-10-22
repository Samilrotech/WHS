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
        Schema::create('vehicle_dashboard_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            // Cache metadata
            $table->string('cache_key')->index();
            $table->string('metric_type'); // fleet, inspections, maintenance, costs
            $table->enum('period', ['day', 'week', 'month', 'quarter', 'year']);
            $table->date('period_start');
            $table->date('period_end');

            // Cached metrics (JSON)
            $table->json('metrics');

            // Cache management
            $table->timestamp('cached_at');
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('metric_type');
            $table->index('period');
            $table->index(['branch_id', 'cache_key']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_dashboard_cache');
    }
};
