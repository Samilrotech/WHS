<?php

namespace App\Modules\ComplianceReporting\Services;

use App\Modules\ComplianceReporting\Models\ComplianceReport;
use App\Modules\ComplianceReporting\Repositories\ComplianceReportRepository;
use App\Modules\ComplianceReporting\Repositories\ComplianceRequirementRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplianceReportService
{
    protected ComplianceReportRepository $repository;
    protected ComplianceRequirementRepository $requirementRepository;

    public function __construct(
        ComplianceReportRepository $repository,
        ComplianceRequirementRepository $requirementRepository
    ) {
        $this->repository = $repository;
        $this->requirementRepository = $requirementRepository;
    }

    /**
     * Get all reports for a branch
     */
    public function getAllForBranch(string $branchId)
    {
        return $this->repository->getByBranch($branchId);
    }

    /**
     * Get paginated reports
     */
    public function getPaginated(string $branchId, array $filters = [], int $perPage = 15)
    {
        return $this->repository->getPaginated($branchId, $filters, $perPage);
    }

    /**
     * Find report by ID
     */
    public function findById(int $id): ?ComplianceReport
    {
        return $this->repository->findById($id);
    }

    /**
     * Create new report
     */
    public function create(array $data): ComplianceReport
    {
        $data['created_by'] = auth()->id();

        // Set report date if not provided
        if (!isset($data['report_date'])) {
            $data['report_date'] = now();
        }

        $report = $this->repository->create($data);

        Log::info('Compliance report created', [
            'report_id' => $report->id,
            'report_number' => $report->report_number,
            'created_by' => auth()->id(),
        ]);

        return $report;
    }

    /**
     * Update report
     */
    public function update(ComplianceReport $report, array $data): ComplianceReport
    {
        $report = $this->repository->update($report, $data);

        Log::info('Compliance report updated', [
            'report_id' => $report->id,
            'updated_by' => auth()->id(),
        ]);

        return $report;
    }

    /**
     * Delete report
     */
    public function delete(ComplianceReport $report): bool
    {
        // Delete associated file if exists
        if ($report->file_path && Storage::exists($report->file_path)) {
            Storage::delete($report->file_path);
        }

        $deleted = $this->repository->delete($report);

        if ($deleted) {
            Log::info('Compliance report deleted', [
                'report_id' => $report->id,
                'deleted_by' => auth()->id(),
            ]);
        }

        return $deleted;
    }

    /**
     * Generate report with metrics
     */
    public function generateReport(string $branchId, array $data): ComplianceReport
    {
        // Calculate metrics for the period
        $metrics = $this->calculateReportMetrics($branchId, $data['period_start'], $data['period_end']);

        $data['metrics'] = $metrics;
        $data['branch_id'] = $branchId;

        return $this->create($data);
    }

    /**
     * Calculate report metrics for a period
     */
    protected function calculateReportMetrics(string $branchId, $periodStart, $periodEnd): array
    {
        $allRequirements = $this->requirementRepository->getByBranch($branchId);
        $compliant = $this->requirementRepository->getCompliant($branchId);
        $nonCompliant = $this->requirementRepository->getNonCompliant($branchId);
        $overdue = $this->requirementRepository->getOverdue($branchId);

        $total = $allRequirements->count();
        $complianceRate = $total > 0 ? round(($compliant->count() / $total) * 100, 2) : 0;

        return [
            'total_requirements' => $total,
            'compliant' => $compliant->count(),
            'non_compliant' => $nonCompliant->count(),
            'overdue' => $overdue->count(),
            'compliance_rate' => $complianceRate,
            'period_start' => Carbon::parse($periodStart)->format('Y-m-d'),
            'period_end' => Carbon::parse($periodEnd)->format('Y-m-d'),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Submit report for review
     */
    public function submitForReview(ComplianceReport $report): ComplianceReport
    {
        if (!$report->isDraft()) {
            throw new \Exception('Only draft reports can be submitted for review');
        }

        return $this->update($report, [
            'status' => 'under-review',
        ]);
    }

    /**
     * Approve report
     */
    public function approve(ComplianceReport $report): ComplianceReport
    {
        return $this->update($report, [
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Publish report
     */
    public function publish(ComplianceReport $report): ComplianceReport
    {
        if (!$report->isApproved()) {
            throw new \Exception('Only approved reports can be published');
        }

        return $this->update($report, [
            'status' => 'published',
        ]);
    }

    /**
     * Archive report
     */
    public function archive(ComplianceReport $report): ComplianceReport
    {
        return $this->update($report, [
            'status' => 'archived',
        ]);
    }

    /**
     * Attach file to report
     */
    public function attachFile(ComplianceReport $report, $file): ComplianceReport
    {
        $filePath = $file->store('compliance-reports/' . date('Y/m'), 'private');

        return $this->update($report, [
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Download report file
     */
    public function downloadFile(ComplianceReport $report)
    {
        if (!$report->file_path || !Storage::exists($report->file_path)) {
            throw new \Exception('Report file not found');
        }

        return Storage::download($report->file_path, $report->file_name);
    }
}
