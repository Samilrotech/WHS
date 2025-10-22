<?php

namespace App\Modules\DocumentManagement\Repositories;

use App\Models\Modules\DocumentManagement\Models\DocumentCategory;
use Illuminate\Database\Eloquent\Collection;

class DocumentCategoryRepository
{
    /**
     * Get all categories for a branch
     */
    public function getAllForBranch(string $branchId): Collection
    {
        return DocumentCategory::where('branch_id', $branchId)
            ->where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get top-level categories (no parent) for a branch
     */
    public function getTopLevelForBranch(string $branchId): Collection
    {
        return DocumentCategory::where('branch_id', $branchId)
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get category by slug
     */
    public function findBySlug(string $slug): ?DocumentCategory
    {
        return DocumentCategory::where('slug', $slug)->first();
    }

    /**
     * Get category with documents count
     */
    public function getWithDocumentsCount(int $categoryId): ?DocumentCategory
    {
        return DocumentCategory::withCount('documents')
            ->find($categoryId);
    }

    /**
     * Create new category
     */
    public function create(array $data): DocumentCategory
    {
        return DocumentCategory::create($data);
    }

    /**
     * Update category
     */
    public function update(DocumentCategory $category, array $data): DocumentCategory
    {
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete category
     */
    public function delete(DocumentCategory $category): bool
    {
        return $category->delete();
    }

    /**
     * Get categories requiring approval
     */
    public function getApprovalRequired(string $branchId): Collection
    {
        return DocumentCategory::where('branch_id', $branchId)
            ->where('requires_approval', true)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get category tree structure
     */
    public function getCategoryTree(string $branchId): Collection
    {
        return DocumentCategory::where('branch_id', $branchId)
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->with(['children' => function ($query) {
                $query->where('status', 'active')
                    ->orderBy('display_order')
                    ->orderBy('name');
            }])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }
}
