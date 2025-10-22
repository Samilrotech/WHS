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
        Schema::create('contractor_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            // Company details
            $table->string('company_name');
            $table->string('abn')->unique();
            $table->string('acn')->nullable();
            $table->string('trading_name')->nullable();

            // Contact information
            $table->string('primary_contact_name');
            $table->string('primary_contact_phone');
            $table->string('primary_contact_email');
            $table->text('address')->nullable();

            // Insurance details
            $table->string('public_liability_insurer')->nullable();
            $table->string('public_liability_policy_number')->nullable();
            $table->date('public_liability_expiry_date')->nullable();
            $table->decimal('public_liability_coverage_amount', 15, 2)->nullable();

            $table->string('workers_comp_insurer')->nullable();
            $table->string('workers_comp_policy_number')->nullable();
            $table->date('workers_comp_expiry_date')->nullable();

            // Verification and status
            $table->boolean('is_verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Performance tracking
            $table->decimal('performance_rating', 3, 2)->default(0.00);
            $table->text('notes')->nullable();

            // Status
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('abn');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_companies');
    }
};
