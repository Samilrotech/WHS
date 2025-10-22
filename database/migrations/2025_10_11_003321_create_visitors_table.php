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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            // Personal details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('company')->nullable();

            // Purpose and host
            $table->string('purpose_of_visit');
            $table->foreignUuid('host_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Safety induction
            $table->boolean('safety_briefing_completed')->default(false);
            $table->foreignUuid('briefed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('briefing_completed_at')->nullable();

            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Vehicle details (if applicable)
            $table->string('vehicle_registration')->nullable();
            $table->string('parking_location')->nullable();

            // Status
            $table->enum('status', ['expected', 'on_site', 'departed', 'cancelled'])->default('expected');

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('host_user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
