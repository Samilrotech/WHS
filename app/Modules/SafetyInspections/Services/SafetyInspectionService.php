<?php

namespace App\Modules\SafetyInspections\Services;

use App\Models\User;
use App\Modules\SafetyInspections\Models\SafetyChecklistItem;
use App\Modules\SafetyInspections\Models\SafetyInspection;
use App\Modules\SafetyInspections\Models\SafetyInspectionTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SafetyInspectionService
{
    /**
     * Create inspection from template
     */
    public function createFromTemplate(
        SafetyInspectionTemplate $template,
        User $inspector,
        array $additionalData = []
    ): SafetyInspection {
        return DB::transaction(function () use ($template, $inspector, $additionalData) {
            // Create inspection
            $inspection = $template->createInspection($inspector, $additionalData);

            // Create checklist items from template
            if ($template->checklist_items && is_array($template->checklist_items)) {
                foreach ($template->checklist_items as $index => $item) {
                    SafetyChecklistItem::create([
                        'inspection_id' => $inspection->id,
                        'sequence_order' => $index + 1,
                        'item_code' => $item['code'] ?? null,
                        'category' => $item['category'] ?? null,
                        'question' => $item['question'],
                        'guidance_notes' => $item['guidance'] ?? null,
                        'item_type' => $item['type'] ?? 'checkbox',
                        'response_options' => $item['options'] ?? null,
                        'is_critical' => $item['critical'] ?? false,
                        'score_weight' => $item['weight'] ?? 1,
                        'regulation_reference' => $item['regulation'] ?? null,
                        'compliance_standard' => $item['standard'] ?? null,
                        'result' => 'pending',
                    ]);
                }
            }

            return $inspection->fresh(['checklistItems']);
        });
    }

    /**
     * Start inspection
     */
    public function startInspection(SafetyInspection $inspection): SafetyInspection
    {
        $inspection->start();

        return $inspection->fresh();
    }

    /**
     * Record checklist item response
     */
    public function recordItemResponse(
        SafetyChecklistItem $item,
        string $result,
        $responseValue = null,
        ?string $notes = null,
        array $photoUrls = []
    ): SafetyChecklistItem {
        $item->recordResponse($result, $responseValue, $notes, $photoUrls);

        // Update inspection progress
        $this->updateInspectionProgress($item->inspection);

        return $item->fresh();
    }

    /**
     * Mark item as non-compliant
     */
    public function markItemNonCompliant(
        SafetyChecklistItem $item,
        string $severity,
        string $notes,
        ?string $correctiveAction = null,
        ?\DateTime $dueDate = null
    ): SafetyChecklistItem {
        $item->markNonCompliant($severity, $notes, $correctiveAction, $dueDate);

        // Update inspection non-compliance tracking
        $inspection = $item->inspection;
        $inspection->calculateScore();
        $inspection->save();

        return $item->fresh();
    }

    /**
     * Update inspection progress
     */
    protected function updateInspectionProgress(SafetyInspection $inspection): void
    {
        $inspection->calculateScore();
        $inspection->save();
    }

    /**
     * Complete inspection
     */
    public function completeInspection(
        SafetyInspection $inspection,
        ?string $inspectorNotes = null,
        ?string $signaturePath = null
    ): SafetyInspection {
        return DB::transaction(function () use ($inspection, $inspectorNotes, $signaturePath) {
            if ($inspectorNotes) {
                $inspection->inspector_notes = $inspectorNotes;
            }

            if ($signaturePath) {
                $inspection->inspector_signature_path = $signaturePath;
            }

            $inspection->complete();

            // Auto-escalate if critical non-compliance
            if ($inspection->non_compliance_severity === 'critical') {
                $this->escalateInspection($inspection);
            }

            return $inspection->fresh();
        });
    }

    /**
     * Submit inspection for review
     */
    public function submitForReview(SafetyInspection $inspection): SafetyInspection
    {
        $inspection->submit();

        // Send notification to reviewer (implement notification logic)

        return $inspection->fresh();
    }

    /**
     * Approve inspection
     */
    public function approveInspection(
        SafetyInspection $inspection,
        User $reviewer,
        ?string $comments = null
    ): SafetyInspection {
        $inspection->approve($reviewer, $comments);

        return $inspection->fresh();
    }

    /**
     * Reject inspection
     */
    public function rejectInspection(
        SafetyInspection $inspection,
        User $reviewer,
        string $reason
    ): SafetyInspection {
        $inspection->reject($reviewer, $reason);

        return $inspection->fresh();
    }

    /**
     * Escalate inspection
     */
    public function escalateInspection(
        SafetyInspection $inspection,
        ?User $assignedTo = null
    ): SafetyInspection {
        $inspection->escalate($assignedTo);

        // Send escalation notification (implement notification logic)

        return $inspection->fresh();
    }

    /**
     * Get inspection statistics
     */
    public function getInspectionStatistics(array $filters = []): array
    {
        $query = SafetyInspection::query();

        // Apply filters
        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $inspections = $query->get();

        return [
            'total_inspections' => $inspections->count(),
            'completed' => $inspections->whereIn('status', ['completed', 'submitted', 'approved'])->count(),
            'in_progress' => $inspections->where('status', 'in_progress')->count(),
            'overdue' => $inspections->filter->isOverdue()->count(),
            'passed' => $inspections->where('passed', true)->count(),
            'failed' => $inspections->where('passed', false)->count(),
            'with_non_compliance' => $inspections->where('has_non_compliance', true)->count(),
            'critical_issues' => $inspections->where('non_compliance_severity', 'critical')->count(),
            'average_score' => round($inspections->whereNotNull('inspection_score')->avg('inspection_score'), 2),
            'pass_rate' => $inspections->whereNotNull('passed')->count() > 0
                ? round(($inspections->where('passed', true)->count() / $inspections->whereNotNull('passed')->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get compliance trends
     */
    public function getComplianceTrends(int $months = 6): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();

            $inspections = SafetyInspection::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('passed')
                ->get();

            $trends[] = [
                'month' => $startDate->format('M Y'),
                'total' => $inspections->count(),
                'passed' => $inspections->where('passed', true)->count(),
                'failed' => $inspections->where('passed', false)->count(),
                'pass_rate' => $inspections->count() > 0
                    ? round(($inspections->where('passed', true)->count() / $inspections->count()) * 100, 2)
                    : 0,
                'non_compliance_count' => $inspections->where('has_non_compliance', true)->count(),
            ];
        }

        return $trends;
    }

    /**
     * Get non-compliance summary
     */
    public function getNonComplianceSummary(): array
    {
        $items = SafetyChecklistItem::nonCompliant()
            ->with('inspection')
            ->get();

        $summary = [
            'total_issues' => $items->count(),
            'by_severity' => [
                'critical' => $items->where('severity', 'critical')->count(),
                'high' => $items->where('severity', 'high')->count(),
                'medium' => $items->where('severity', 'medium')->count(),
                'low' => $items->where('severity', 'low')->count(),
            ],
            'by_category' => $items->groupBy('category')->map->count()->toArray(),
            'requiring_action' => $items->whereNotNull('corrective_action_required')->count(),
            'overdue_corrections' => $items->where('correction_due_date', '<', now())->count(),
        ];

        return $summary;
    }

    /**
     * Generate audit trail
     */
    public function generateAuditTrail(SafetyInspection $inspection): array
    {
        $trail = [
            'inspection_number' => $inspection->inspection_number,
            'created_at' => $inspection->created_at,
            'inspector' => $inspection->inspector->name,
            'location' => $inspection->location,
            'status_history' => $inspection->audit_log ?? [],
            'checklist_items' => $inspection->checklistItems->map(function ($item) {
                return [
                    'question' => $item->question,
                    'result' => $item->result,
                    'responded_at' => $item->responded_at,
                    'non_compliant' => $item->non_compliant,
                    'severity' => $item->severity,
                    'photos_count' => $item->photos_count,
                ];
            }),
            'non_compliance_items' => $inspection->checklistItems->where('non_compliant', true)->values(),
            'final_score' => $inspection->inspection_score,
            'passed' => $inspection->passed,
            'completed_at' => $inspection->completed_at,
            'reviewer' => $inspection->reviewer ? $inspection->reviewer->name : null,
            'reviewed_at' => $inspection->reviewed_at,
        ];

        return $trail;
    }

    /**
     * Schedule recurring inspections
     */
    public function scheduleRecurringInspections(
        SafetyInspectionTemplate $template,
        User $inspector,
        \DateTime $startDate,
        int $occurrences = 12
    ): Collection {
        $inspections = collect();

        $frequencyMap = [
            'daily' => 1,
            'weekly' => 7,
            'monthly' => 30,
            'quarterly' => 90,
            'annual' => 365,
        ];

        $interval = $frequencyMap[$template->frequency] ?? 30;

        for ($i = 0; $i < $occurrences; $i++) {
            $scheduledDate = (clone $startDate)->modify("+{$i} {$interval} days");

            $inspection = $this->createFromTemplate($template, $inspector, [
                'scheduled_date' => $scheduledDate,
                'status' => 'scheduled',
            ]);

            $inspections->push($inspection);
        }

        return $inspections;
    }
}
