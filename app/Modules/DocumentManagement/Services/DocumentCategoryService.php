<?php

namespace App\Modules\DocumentManagement\Services;

use App\Models\Modules\DocumentManagement\Models\DocumentCategory;
use App\Modules\DocumentManagement\Repositories\DocumentCategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentCategoryService
{
    public function __construct(
        protected DocumentCategoryRepository $repository
    ) {}

    /**
     * Get all categories for a branch
     */
    public function getAllForBranch(string $branchId): Collection
    {
        return $this->repository->getAllForBranch($branchId);
    }

    /**
     * Get top-level categories for a branch
     */
    public function getTopLevelForBranch(string $branchId): Collection
    {
        return $this->repository->getTopLevelForBranch($branchId);
    }

    /**
     * Get category tree structure
     */
    public function getCategoryTree(string $branchId): Collection
    {
        return $this->repository->getCategoryTree($branchId);
    }

    /**
     * Create new category
     */
    public function create(array $data): DocumentCategory
    {
        // Auto-generate slug from name if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;

        while ($this->repository->findBySlug($data['slug'])) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category = $this->repository->create($data);

        Log::info('Document category created', [
            'category_id' => $category->id,
            'name' => $category->name,
            'branch_id' => $category->branch_id,
            'created_by' => auth()->id(),
        ]);

        return $category;
    }

    /**
     * Update category
     */
    public function update(DocumentCategory $category, array $data): DocumentCategory
    {
        // If name changed, update slug
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = Str::slug($data['name']);

            // Ensure new slug is unique
            $originalSlug = $data['slug'];
            $counter = 1;

            while ($this->repository->findBySlug($data['slug']) && $data['slug'] !== $category->slug) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $category = $this->repository->update($category, $data);

        Log::info('Document category updated', [
            'category_id' => $category->id,
            'name' => $category->name,
            'updated_by' => auth()->id(),
        ]);

        return $category;
    }

    /**
     * Delete category
     */
    public function delete(DocumentCategory $category): bool
    {
        // Check if category has documents
        if ($category->documents()->count() > 0) {
            throw new \Exception('Cannot delete category with existing documents. Please move or delete documents first.');
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            throw new \Exception('Cannot delete category with sub-categories. Please delete sub-categories first.');
        }

        $categoryId = $category->id;
        $categoryName = $category->name;

        $deleted = $this->repository->delete($category);

        if ($deleted) {
            Log::info('Document category deleted', [
                'category_id' => $categoryId,
                'name' => $categoryName,
                'deleted_by' => auth()->id(),
            ]);
        }

        return $deleted;
    }

    /**
     * Get category with documents count
     */
    public function getWithDocumentsCount(int $categoryId): ?DocumentCategory
    {
        return $this->repository->getWithDocumentsCount($categoryId);
    }

    /**
     * Get categories requiring approval
     */
    public function getApprovalRequired(string $branchId): Collection
    {
        return $this->repository->getApprovalRequired($branchId);
    }

    /**
     * Reorder categories
     */
    public function reorder(array $orderData): bool
    {
        try {
            foreach ($orderData as $item) {
                $category = DocumentCategory::find($item['id']);
                if ($category) {
                    $category->update(['display_order' => $item['order']]);
                }
            }

            Log::info('Categories reordered', [
                'count' => count($orderData),
                'reordered_by' => auth()->id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reorder categories', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return false;
        }
    }
}
