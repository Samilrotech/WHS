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
        Schema::create('sign_in_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            // Polymorphic relationship (contractor or visitor)
            $table->morphs('signable'); // Creates signable_id and signable_type

            // Sign-in details
            $table->dateTime('signed_in_at');
            $table->dateTime('signed_out_at')->nullable();
            $table->string('location')->nullable(); // Building/area

            // Purpose and work details
            $table->string('purpose');
            $table->text('work_description')->nullable();
            $table->json('areas_accessed')->nullable(); // Array of areas

            // Safety compliance
            $table->boolean('ppe_acknowledged')->default(false);
            $table->boolean('emergency_procedures_acknowledged')->default(false);
            $table->json('ppe_items')->nullable(); // Array of PPE items

            // QR code or manual entry
            $table->enum('entry_method', ['qr_code', 'manual', 'kiosk'])->default('manual');

            // Status
            $table->enum('status', ['signed_in', 'signed_out', 'overdue'])->default('signed_in');

            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index(['signable_id', 'signable_type']);
            $table->index('signed_in_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sign_in_logs');
    }
};
