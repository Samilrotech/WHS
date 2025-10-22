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
        Schema::create('witnesses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->string('name');
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('statement');
            $table->dateTime('statement_taken_at')->nullable();
            $table->foreignUuid('taken_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('incident_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('witnesses');
    }
};
