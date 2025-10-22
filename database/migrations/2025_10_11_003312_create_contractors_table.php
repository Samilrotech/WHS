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
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('contractor_company_id')->constrained()->onDelete('cascade');

            // Personal details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->date('date_of_birth')->nullable();

            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Identification
            $table->string('driver_license_number')->nullable();
            $table->date('driver_license_expiry')->nullable();

            // Induction status
            $table->boolean('induction_completed')->default(false);
            $table->date('induction_completion_date')->nullable();
            $table->date('induction_expiry_date')->nullable();
            $table->foreignUuid('inducted_by')->nullable()->constrained('users')->onDelete('set null');

            // Access and status
            $table->boolean('site_access_granted')->default(false);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('contractor_company_id');
            $table->index('email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
