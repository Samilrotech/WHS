<?php

namespace App\Modules\ComplianceReporting\Repositories;

use App\Modules\ComplianceReporting\Models\ComplianceReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ComplianceReportRepository
{
    /**
     * Get all reports for a branch
     */
    public function getByBranch(string $branchId): Collection
    {
        return ComplianceReport::where('branch_id', $branchId)
            ->with(['creator', 'reviewer', 'approver'])
            ->latest()
            ->get();
    }

    /**
     * Get paginated reports
     */
    public function getPaginated(string $branchId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ComplianceReport::where('branch_id', $branchId)
            ->with(['creator', 'reviewer', 'approver']);

        // Apply filters
        if (isset($filters['report_type'])) {
            $query->where('report_type', $filters['report_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['period'])) {
            $query->forPeriod($filters['period']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('report_number', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find report by ID
     */
    public function findById(int $id): ?ComplianceReport
    {
        return ComplianceReport::with(['creator', 'reviewer', 'approver'])
            ->find($id);
    }

    /**
     * Create new report
     */
    public function create(array $data): ComplianceReport
    {
        return ComplianceReport::create($data);
    }

    /**
     * Update report
     */
    public function update(ComplianceReport $report, array $data): ComplianceReport
    {
        $report->update($data);
        return $report->fresh();
    }

    /**
     * Delete report
     */
    public function delete(ComplianceReport $report): bool
    {
        return $report->delete();
    }

    /**
     * Get published reports
     */
    public function getPublished(string $branchId): Collection
    {
        return ComplianceReport::where('branch_id', $branchId)
            ->published()
            ->with(['creator'])
            ->latest('report_date')
            ->get();
    }

    /**
     * Get draft reports
     */
    public function getDrafts(string $branchId): Collection
    {
        return ComplianceReport::where('branch_id', $branchId)
            ->draft()
            ->with(['creator'])
            ->latest()
            ->get();
    }

    /**
     * Get reports for a specific period
     */
    public function getForPeriod(string $branchId, string $period): Collection
    {
        return ComplianceReport::where('branch_id', $branchId)
            ->forPeriod($period)
            ->with(['creator'])
            ->latest('report_date')
            ->get();
    }

    /**
     * Get reports by type
     */
    public function getByType(string $branchId, string $type): Collection
    {
        return ComplianceReport::where('branch_id', $branchId)
            ->where('report_type', $type)
            ->with(['creator'])
            ->latest('report_date')
            ->get();
    }
}
