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
        Schema::create('capa_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('capa_id')->index();
            $table->uuid('assigned_to_user_id')->nullable()->index();

            // Action Details
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sequence_order')->default(0);

            // Timeline
            $table->date('due_date');
            $table->date('completed_date')->nullable();

            // Status
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $table->boolean('is_completed')->default(false);

            // Completion Details
            $table->uuid('completed_by_user_id')->nullable()->index();
            $table->text('completion_notes')->nullable();
            $table->json('evidence_paths')->nullable(); // Photos/documents proving completion

            $table->timestamps();

            // Foreign keys
            $table->foreign('capa_id')->references('id')->on('capas')->onDelete('cascade');
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by_user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('due_date');
            $table->index(['capa_id', 'sequence_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capa_actions');
    }
};
