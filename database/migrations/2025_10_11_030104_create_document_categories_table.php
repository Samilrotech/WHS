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
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            // Hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('document_categories')->onDelete('cascade');
            $table->integer('display_order')->default(0);

            // Settings
            $table->boolean('requires_approval')->default(false);
            $table->integer('retention_days')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('parent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_categories');
    }
};
