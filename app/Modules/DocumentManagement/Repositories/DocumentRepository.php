<?php

namespace App\Modules\DocumentManagement\Repositories;

use App\Models\Modules\DocumentManagement\Models\Document;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentRepository
{
    /**
     * Get all documents for a branch with pagination
     */
    public function getAllForBranch(string $branchId, int $perPage = 25): LengthAwarePaginator
    {
        return Document::where('branch_id', $branchId)
            ->with(['category', 'uploader', 'reviewer'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get documents by category
     */
    public function getByCategory(int $categoryId, int $perPage = 25): LengthAwarePaginator
    {
        return Document::where('category_id', $categoryId)
            ->with(['uploader', 'reviewer'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get documents pending review
     */
    public function getPendingReview(string $branchId): Collection
    {
        return Document::where('branch_id', $branchId)
            ->where('requires_review', true)
            ->where('review_status', 'pending')
            ->with(['category', 'uploader'])
            ->latest()
            ->get();
    }

    /**
     * Get expired documents
     */
    public function getExpired(string $branchId): Collection
    {
        return Document::where('branch_id', $branchId)
            ->where('is_expired', true)
            ->orWhere(function ($query) {
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
            })
            ->with(['category', 'uploader'])
            ->latest('expiry_date')
            ->get();
    }

    /**
     * Get expiring soon documents (within 30 days)
     */
    public function getExpiringSoon(string $branchId): Collection
    {
        return Document::where('branch_id', $branchId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('is_expired', false)
            ->with(['category', 'uploader'])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Search documents
     */
    public function search(string $branchId, string $query, int $perPage = 25): LengthAwarePaginator
    {
        return Document::where('branch_id', $branchId)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('document_number', 'like', "%{$query}%");
            })
            ->with(['category', 'uploader'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get documents by tag
     */
    public function getByTag(string $branchId, string $tag): Collection
    {
        return Document::where('branch_id', $branchId)
            ->whereJsonContains('tags', $tag)
            ->with(['category', 'uploader'])
            ->latest()
            ->get();
    }

    /**
     * Get accessible documents for user
     */
    public function getAccessibleForUser(string $userId, string $branchId): Collection
    {
        $user = \App\Models\User::findOrFail($userId);

        return Document::where('branch_id', $branchId)
            ->where(function ($query) use ($userId) {
                $query->where('visibility', 'public')
                    ->orWhere('uploaded_by', $userId)
                    ->orWhere(function ($q) use ($userId) {
                        $q->where('visibility', 'restricted')
                            ->whereJsonContains('restricted_to', $userId);
                    });
            })
            ->with(['category', 'uploader'])
            ->latest()
            ->get();
    }

    /**
     * Create new document
     */
    public function create(array $data): Document
    {
        return Document::create($data);
    }

    /**
     * Update document
     */
    public function update(Document $document, array $data): Document
    {
        $document->update($data);
        return $document->fresh();
    }

    /**
     * Delete document
     */
    public function delete(Document $document): bool
    {
        return $document->delete();
    }

    /**
     * Get document by number
     */
    public function findByNumber(string $documentNumber): ?Document
    {
        return Document::where('document_number', $documentNumber)
            ->with(['category', 'uploader', 'reviewer', 'versions'])
            ->first();
    }

    /**
     * Get recently accessed documents for user
     */
    public function getRecentlyAccessedForUser(string $userId, int $limit = 10): Collection
    {
        return Document::whereHas('accessLogs', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['category', 'accessLogs' => function ($query) use ($userId) {
            $query->where('user_id', $userId)->latest()->limit(1);
        }])
        ->latest('updated_at')
        ->limit($limit)
        ->get();
    }
}
