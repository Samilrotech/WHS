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
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['injury', 'near-miss', 'property-damage', 'environmental', 'security']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->dateTime('incident_datetime');
            $table->string('location_branch');
            $table->string('location_specific');
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->text('description');
            $table->text('immediate_actions')->nullable();
            $table->boolean('requires_emergency')->default(false);
            $table->boolean('notify_authorities')->default(false);
            $table->enum('status', ['reported', 'investigating', 'resolved', 'closed'])->default('reported');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('root_cause')->nullable();
            $table->text('voice_note_path')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('incident_datetime');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
