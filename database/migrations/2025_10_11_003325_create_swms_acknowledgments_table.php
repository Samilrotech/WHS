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
        Schema::create('swms_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('contractor_id')->constrained()->onDelete('cascade');

            // SWMS document reference
            $table->string('swms_title');
            $table->string('swms_reference_number');
            $table->date('swms_version_date');
            $table->string('work_activity');

            // Acknowledgment
            $table->dateTime('acknowledged_at');
            $table->string('signature_path')->nullable(); // Digital signature image
            $table->string('ip_address')->nullable();
            $table->text('declaration_text'); // The statement they agreed to

            // Verification
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('verified_at')->nullable();

            // Validity
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');

            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('contractor_id');
            $table->index('swms_reference_number');
            $table->index('expiry_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swms_acknowledgments');
    }
};
