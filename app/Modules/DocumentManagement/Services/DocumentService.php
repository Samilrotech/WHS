<?php

namespace App\Modules\DocumentManagement\Services;

use App\Models\Modules\DocumentManagement\Models\Document;
use App\Models\Modules\DocumentManagement\Models\DocumentAccessLog;
use App\Models\Modules\DocumentManagement\Models\DocumentVersion;
use App\Modules\DocumentManagement\Repositories\DocumentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function __construct(
        protected DocumentRepository $repository
    ) {}

    /**
     * Get all documents for a branch with pagination
     */
    public function getAllForBranch(string $branchId, int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->getAllForBranch($branchId, $perPage);
    }

    /**
     * Get documents by category
     */
    public function getByCategory(int $categoryId, int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->getByCategory($categoryId, $perPage);
    }

    /**
     * Get documents pending review
     */
    public function getPendingReview(string $branchId): Collection
    {
        return $this->repository->getPendingReview($branchId);
    }

    /**
     * Get expired documents
     */
    public function getExpired(string $branchId): Collection
    {
        return $this->repository->getExpired($branchId);
    }

    /**
     * Get expiring soon documents
     */
    public function getExpiringSoon(string $branchId): Collection
    {
        return $this->repository->getExpiringSoon($branchId);
    }

    /**
     * Search documents
     */
    public function search(string $branchId, string $query, int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->search($branchId, $query, $perPage);
    }

    /**
     * Create new document with file upload
     */
    public function create(array $data, UploadedFile $file): Document
    {
        // Store file
        $filePath = $file->store('documents/' . date('Y/m'), 'private');
        $fileHash = hash_file('sha256', $file->getRealPath());

        $documentData = array_merge($data, [
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_hash' => $fileHash,
            'current_version' => 1,
            'uploaded_by' => auth()->id(),
        ]);

        $document = $this->repository->create($documentData);

        // Create initial version record
        DocumentVersion::create([
            'document_id' => $document->id,
            'created_by' => auth()->id(),
            'version_number' => 1,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_hash' => $fileHash,
            'change_notes' => 'Initial upload',
        ]);

        Log::info('Document created', [
            'document_id' => $document->id,
            'document_number' => $document->document_number,
            'uploaded_by' => auth()->id(),
        ]);

        return $document;
    }

    /**
     * Create new version of document
     */
    public function createNewVersion(Document $document, UploadedFile $file, string $changeNotes = null): Document
    {
        // Store new file
        $filePath = $file->store('documents/' . date('Y/m'), 'private');
        $fileHash = hash_file('sha256', $file->getRealPath());

        $newVersion = $document->current_version + 1;

        // Update document
        $document = $this->repository->update($document, [
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'file_hash' => $fileHash,
            'current_version' => $newVersion,
        ]);

        // Create version record
        DocumentVersion::create([
            'document_id' => $document->id,
            'created_by' => auth()->id(),
            'version_number' => $newVersion,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_hash' => $fileHash,
            'change_notes' => $changeNotes ?? 'Version ' . $newVersion,
        ]);

        Log::info('Document version created', [
            'document_id' => $document->id,
            'version_number' => $newVersion,
            'created_by' => auth()->id(),
        ]);

        return $document->fresh();
    }

    /**
     * Update document metadata
     */
    public function update(Document $document, array $data): Document
    {
        $document = $this->repository->update($document, $data);

        Log::info('Document updated', [
            'document_id' => $document->id,
            'updated_by' => auth()->id(),
        ]);

        return $document;
    }

    /**
     * Approve document
     */
    public function approve(Document $document, string $notes = null): Document
    {
        $document = $this->repository->update($document, [
            'review_status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        Log::info('Document approved', [
            'document_id' => $document->id,
            'reviewed_by' => auth()->id(),
        ]);

        return $document;
    }

    /**
     * Reject document
     */
    public function reject(Document $document, string $notes): Document
    {
        $document = $this->repository->update($document, [
            'review_status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        Log::info('Document rejected', [
            'document_id' => $document->id,
            'reviewed_by' => auth()->id(),
        ]);

        return $document;
    }

    /**
     * Log document access
     */
    public function logAccess(Document $document, string $action, array $metadata = []): DocumentAccessLog
    {
        $log = DocumentAccessLog::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);

        return $log;
    }

    /**
     * Download document
     */
    public function download(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Check file exists
        if (!Storage::disk('private')->exists($document->file_path)) {
            throw new \Exception('File not found');
        }

        // Log access
        $this->logAccess($document, 'download');

        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete document
     */
    public function delete(Document $document): bool
    {
        // Delete file from storage
        if (Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        // Delete all version files
        foreach ($document->versions as $version) {
            if (Storage::disk('private')->exists($version->file_path)) {
                Storage::disk('private')->delete($version->file_path);
            }
        }

        $documentId = $document->id;
        $documentNumber = $document->document_number;

        $deleted = $this->repository->delete($document);

        if ($deleted) {
            Log::info('Document deleted', [
                'document_id' => $documentId,
                'document_number' => $documentNumber,
                'deleted_by' => auth()->id(),
            ]);
        }

        return $deleted;
    }

    /**
     * Get accessible documents for user
     */
    public function getAccessibleForUser(string $userId, string $branchId): Collection
    {
        return $this->repository->getAccessibleForUser($userId, $branchId);
    }

    /**
     * Get recently accessed documents for user
     */
    public function getRecentlyAccessedForUser(string $userId, int $limit = 10): Collection
    {
        return $this->repository->getRecentlyAccessedForUser($userId, $limit);
    }
}
