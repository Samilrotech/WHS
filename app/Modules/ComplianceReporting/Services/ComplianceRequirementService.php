<?php

namespace App\Modules\ComplianceReporting\Services;

use App\Modules\ComplianceReporting\Models\ComplianceRequirement;
use App\Modules\ComplianceReporting\Repositories\ComplianceRequirementRepository;
use Illuminate\Support\Facades\Log;

class ComplianceRequirementService
{
    protected ComplianceRequirementRepository $repository;

    public function __construct(ComplianceRequirementRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all requirements for a branch
     */
    public function getAllForBranch(string $branchId)
    {
        return $this->repository->getByBranch($branchId);
    }

    /**
     * Get paginated requirements
     */
    public function getPaginated(string $branchId, array $filters = [], int $perPage = 15)
    {
        return $this->repository->getPaginated($branchId, $filters, $perPage);
    }

    /**
     * Find requirement by ID
     */
    public function findById(int $id): ?ComplianceRequirement
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new requirement
     */
    public function create(array $data): ComplianceRequirement
    {
        $data['created_by'] = auth()->id();

        $requirement = $this->repository->create($data);

        Log::info('Compliance requirement created', [
            'requirement_id' => $requirement->id,
            'requirement_number' => $requirement->requirement_number,
            'created_by' => auth()->id(),
        ]);

        return $requirement;
    }

    /**
     * Update requirement
     */
    public function update(ComplianceRequirement $requirement, array $data): ComplianceRequirement
    {
        $data['updated_by'] = auth()->id();

        // If status changes to compliant, update compliance_score to 100
        if (isset($data['status']) && $data['status'] === 'compliant' && !isset($data['compliance_score'])) {
            $data['compliance_score'] = 100;
        }

        // If status changes to non-compliant, update compliance_score to 0
        if (isset($data['status']) && $data['status'] === 'non-compliant' && !isset($data['compliance_score'])) {
            $data['compliance_score'] = 0;
        }

        $requirement = $this->repository->update($requirement, $data);

        Log::info('Compliance requirement updated', [
            'requirement_id' => $requirement->id,
            'updated_by' => auth()->id(),
        ]);

        return $requirement;
    }

    /**
     * Delete requirement
     */
    public function delete(ComplianceRequirement $requirement): bool
    {
        // Check if requirement has associated checks or actions
        if ($requirement->checks()->count() > 0) {
            throw new \Exception('Cannot delete requirement with existing compliance checks');
        }

        if ($requirement->actions()->count() > 0) {
            throw new \Exception('Cannot delete requirement with existing compliance actions');
        }

        $deleted = $this->repository->delete($requirement);

        if ($deleted) {
            Log::info('Compliance requirement deleted', [
                'requirement_id' => $requirement->id,
                'deleted_by' => auth()->id(),
            ]);
        }

        return $deleted;
    }

    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics(string $branchId): array
    {
        $compliant = $this->repository->getCompliant($branchId);
        $nonCompliant = $this->repository->getNonCompliant($branchId);
        $overdue = $this->repository->getOverdue($branchId);
        $reviewDueSoon = $this->repository->getReviewDueSoon($branchId);

        $total = $compliant->count() + $nonCompliant->count();
        $complianceRate = $total > 0 ? round(($compliant->count() / $total) * 100, 2) : 0;

        return [
            'total_requirements' => $total,
            'compliant' => $compliant->count(),
            'non_compliant' => $nonCompliant->count(),
            'overdue' => $overdue->count(),
            'review_due_soon' => $reviewDueSoon->count(),
            'compliance_rate' => $complianceRate,
            'by_category' => $this->getRequirementsByCategory($branchId),
            'by_risk_level' => $this->getRequirementsByRiskLevel($branchId),
        ];
    }

    /**
     * Get requirements grouped by category
     */
    protected function getRequirementsByCategory(string $branchId): array
    {
        $categories = ['legal', 'regulatory', 'industry', 'internal', 'certification'];
        $result = [];

        foreach ($categories as $category) {
            $result[$category] = $this->repository->getByCategory($branchId, $category)->count();
        }

        return $result;
    }

    /**
     * Get requirements grouped by risk level
     */
    protected function getRequirementsByRiskLevel(string $branchId): array
    {
        $allRequirements = $this->repository->getByBranch($branchId);

        return [
            'low' => $allRequirements->where('risk_level', 'low')->count(),
            'medium' => $allRequirements->where('risk_level', 'medium')->count(),
            'high' => $allRequirements->where('risk_level', 'high')->count(),
            'critical' => $allRequirements->where('risk_level', 'critical')->count(),
        ];
    }

    /**
     * Update compliance status based on latest check
     */
    public function updateStatusFromCheck(ComplianceRequirement $requirement): ComplianceRequirement
    {
        $latestCheck = $requirement->latestCheck();

        if (!$latestCheck) {
            return $requirement;
        }

        $status = match($latestCheck->result) {
            'pass' => 'compliant',
            'fail' => 'non-compliant',
            'partial' => 'partial',
            'not-applicable' => 'not-applicable',
            default => 'under-review',
        };

        $data = [
            'status' => $status,
            'compliance_score' => $latestCheck->score,
            'last_review_date' => $latestCheck->check_date,
            'next_review_date' => $requirement->calculateNextReviewDate(),
        ];

        return $this->update($requirement, $data);
    }
}
