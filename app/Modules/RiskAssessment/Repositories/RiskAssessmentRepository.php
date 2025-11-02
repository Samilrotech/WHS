<?php

namespace App\Modules\RiskAssessment\Repositories;

use App\Modules\RiskAssessment\Models\RiskAssessment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RiskAssessmentRepository
{
    /**
     * Get paginated risk assessments with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator // Increased for dense table view
    {
        $query = RiskAssessment::with(['user', 'branch', 'hazards']);

        // Filter by category
        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filter by risk level
        if (!empty($filters['risk_level'])) {
            $query->byRiskLevel($filters['risk_level']);
        }

        // Search in task description and location
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('task_description', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('location', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('assessment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('assessment_date', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create new risk assessment
     *
     * @param array $data
     * @return RiskAssessment
     */
    public function create(array $data): RiskAssessment
    {
        return RiskAssessment::create($data);
    }

    /**
     * Find risk assessment by ID with relationships
     *
     * @param string $id
     * @return RiskAssessment|null
     */
    public function findWithRelations(string $id): ?RiskAssessment
    {
        return RiskAssessment::with(['user', 'branch', 'hazards.controlMeasures', 'approver'])
            ->find($id);
    }

    /**
     * Update risk assessment
     *
     * @param RiskAssessment $assessment
     * @param array $data
     * @return RiskAssessment
     */
    public function update(RiskAssessment $assessment, array $data): RiskAssessment
    {
        $assessment->update($data);
        return $assessment->fresh();
    }

    /**
     * Get high risk assessments (orange and red)
     *
     * @param string $branchId
     * @return Collection
     */
    public function getHighRisk(string $branchId): Collection
    {
        return RiskAssessment::where('branch_id', $branchId)
            ->highRisk()
            ->with(['user', 'hazards'])
            ->latest()
            ->get();
    }

    /**
     * Get assessments requiring review (review_date in past or null)
     *
     * @param string $branchId
     * @return Collection
     */
    public function getRequiringReview(string $branchId): Collection
    {
        return RiskAssessment::where('branch_id', $branchId)
            ->where(function ($query) {
                $query->whereNull('review_date')
                    ->orWhere('review_date', '<', now());
            })
            ->with(['user', 'hazards'])
            ->latest()
            ->get();
    }

    /**
     * Get recent assessments
     *
     * @param string $branchId
     * @param int $limit
     * @return Collection
     */
    public function getRecent(string $branchId, int $limit = 10): Collection
    {
        return RiskAssessment::where('branch_id', $branchId)
            ->with(['user', 'hazards'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get assessments by category
     *
     * @param string $branchId
     * @param string $category
     * @return Collection
     */
    public function getByCategory(string $branchId, string $category): Collection
    {
        return RiskAssessment::where('branch_id', $branchId)
            ->byCategory($category)
            ->with(['user', 'hazards'])
            ->latest()
            ->get();
    }
}
