<?php

namespace App\Modules\ComplianceReporting\Repositories;

use App\Modules\ComplianceReporting\Models\ComplianceRequirement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ComplianceRequirementRepository
{
    /**
     * Get all requirements for a branch
     */
    public function getByBranch(string $branchId): Collection
    {
        return ComplianceRequirement::where('branch_id', $branchId)
            ->with(['owner', 'reviewer', 'checks'])
            ->latest()
            ->get();
    }

    /**
     * Get paginated requirements
     */
    public function getPaginated(string $branchId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ComplianceRequirement::where('branch_id', $branchId)
            ->with(['owner', 'reviewer']);

        // Apply filters
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (isset($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('requirement_number', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find requirement by ID
     */
    public function findById(int $id): ?ComplianceRequirement
    {
        return ComplianceRequirement::with(['owner', 'reviewer', 'checks', 'actions'])
            ->find($id);
    }

    /**
     * Create new requirement
     */
    public function create(array $data): ComplianceRequirement
    {
        return ComplianceRequirement::create($data);
    }

    /**
     * Update requirement
     */
    public function update(ComplianceRequirement $requirement, array $data): ComplianceRequirement
    {
        $requirement->update($data);
        return $requirement->fresh();
    }

    /**
     * Delete requirement
     */
    public function delete(ComplianceRequirement $requirement): bool
    {
        return $requirement->delete();
    }

    /**
     * Get compliant requirements
     */
    public function getCompliant(string $branchId): Collection
    {
        return ComplianceRequirement::where('branch_id', $branchId)
            ->compliant()
            ->with(['owner'])
            ->get();
    }

    /**
     * Get non-compliant requirements
     */
    public function getNonCompliant(string $branchId): Collection
    {
        return ComplianceRequirement::where('branch_id', $branchId)
            ->nonCompliant()
            ->with(['owner'])
            ->get();
    }

    /**
     * Get overdue requirements
     */
    public function getOverdue(string $branchId): Collection
    {
        return ComplianceRequirement::where('branch_id', $branchId)
            ->overdue()
            ->with(['owner'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get requirements with review due soon
     */
    public function getReviewDueSoon(string $branchId, int $days = 30): Collection
    {
        return ComplianceRequirement::where('branch_id', $branchId)
            ->reviewDueSoon($days)
            ->with(['owner'])
            ->orderBy('next_review_date')
            ->get();
    }

    /**
     * Get requirements by category
     */
    public function getByCategory(string $branchId, string $category): Collection
    {
        return ComplianceRequirement::where('branch_id', $branchId)
            ->where('category', $category)
            ->with(['owner'])
            ->get();
    }

    /**
     * Get requirements by owner
     */
    public function getByOwner(string $ownerId): Collection
    {
        return ComplianceRequirement::where('owner_id', $ownerId)
            ->with(['checks', 'actions'])
            ->latest()
            ->get();
    }

    /**
     * Search requirements
     */
    public function search(string $branchId, string $query): Collection
    {
        return ComplianceRequirement::where('branch_id', $branchId)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhere('requirement_number', 'like', '%' . $query . '%');
            })
            ->with(['owner'])
            ->get();
    }
}
