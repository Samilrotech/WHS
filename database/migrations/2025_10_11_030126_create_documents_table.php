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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('document_categories')->onDelete('cascade');
            $table->foreignUuid('uploaded_by')->constrained('users')->onDelete('cascade');

            // Document details
            $table->string('title');
            $table->string('document_number')->unique();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size'); // bytes
            $table->string('mime_type');
            $table->string('file_hash');

            // Versioning
            $table->integer('current_version')->default(1);

            // Metadata
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();

            // Review and approval
            $table->boolean('requires_review')->default(false);
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');

            // Expiry
            $table->date('expiry_date')->nullable();
            $table->boolean('is_expired')->default(false);

            // Access control
            $table->enum('visibility', ['public', 'private', 'restricted'])->default('private');
            $table->json('restricted_to')->nullable(); // User IDs or role names

            $table->enum('status', ['draft', 'active', 'archived'])->default('active');

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('category_id');
            $table->index('uploaded_by');
            $table->index('document_number');
            $table->index('expiry_date');
            $table->index('status');
            $table->index('review_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
