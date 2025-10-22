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
        Schema::create('induction_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            // Module details
            $table->string('title');
            $table->text('description');
            $table->text('content');

            // Video content
            $table->string('video_url')->nullable();
            $table->integer('video_duration_minutes')->nullable();

            // Quiz configuration
            $table->boolean('has_quiz')->default(false);
            $table->integer('pass_mark_percentage')->default(80);

            // Validity
            $table->integer('validity_months')->default(12);
            $table->boolean('is_mandatory')->default(true);

            // Status and ordering
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->integer('display_order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('status');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('induction_modules');
    }
};
