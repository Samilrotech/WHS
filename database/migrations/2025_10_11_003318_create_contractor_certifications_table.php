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
        Schema::create('contractor_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('contractor_id')->constrained()->onDelete('cascade');

            // Certification details
            $table->string('certification_type'); // e.g., "White Card", "High Risk Work License"
            $table->string('certification_number')->unique();
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('issuing_authority');

            // Document management
            $table->string('document_path')->nullable();
            $table->string('document_hash')->nullable();

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('verification_notes')->nullable();

            // Status and alerts
            $table->enum('status', ['valid', 'expired', 'pending_verification', 'rejected'])->default('pending_verification');
            $table->boolean('expiry_alert_sent')->default(false);

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('contractor_id');
            $table->index('certification_number');
            $table->index('expiry_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_certifications');
    }
};
