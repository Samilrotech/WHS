<?php

namespace App\Modules\CAPAManagement\Services;

use App\Modules\CAPAManagement\Models\CAPA;
use App\Modules\CAPAManagement\Models\CAPAAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CAPAService
{
    /**
     * Get all CAPAs with pagination and filtering
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        $query = CAPA::with(['raisedBy', 'assignedTo', 'incident'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to_user_id'])) {
            $query->where('assigned_to_user_id', $filters['assigned_to_user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new CAPA
     */
    public function create(array $data): CAPA
    {
        $data['branch_id'] = auth()->user()->branch_id;
        $data['raised_by_user_id'] = auth()->id();
        $data['capa_number'] = $this->generateCAPANumber();
        $data['status'] = 'draft';

        return CAPA::create($data);
    }

    /**
     * Generate unique CAPA number
     */
    protected function generateCAPANumber(): string
    {
        $year = now()->year;
        $branchId = auth()->user()->branch_id;

        // Get the last CAPA number for this year and branch
        $lastCAPA = CAPA::where('branch_id', $branchId)
            ->where('capa_number', 'like', "CAPA-{$year}-%")
            ->orderBy('capa_number', 'desc')
            ->first();

        if ($lastCAPA) {
            // Extract the sequence number and increment
            $parts = explode('-', $lastCAPA->capa_number);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('CAPA-%d-%04d', $year, $sequence);
    }

    /**
     * Submit CAPA for approval
     */
    public function submit(CAPA $capa): CAPA
    {
        $capa->update(['status' => 'submitted']);

        return $capa->fresh();
    }

    /**
     * Approve CAPA
     */
    public function approve(CAPA $capa, ?string $notes = null): CAPA
    {
        $capa->update([
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approval_date' => now(),
            'approval_notes' => $notes,
        ]);

        return $capa->fresh();
    }

    /**
     * Reject CAPA
     */
    public function reject(CAPA $capa, string $reason): CAPA
    {
        $capa->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $capa->fresh();
    }

    /**
     * Start CAPA implementation
     */
    public function startImplementation(CAPA $capa): CAPA
    {
        $capa->update(['status' => 'in_progress']);

        return $capa->fresh();
    }

    /**
     * Complete CAPA implementation
     */
    public function completeImplementation(CAPA $capa): CAPA
    {
        $capa->update([
            'status' => 'completed',
            'actual_completion_date' => now(),
        ]);

        return $capa->fresh();
    }

    /**
     * Verify CAPA effectiveness
     */
    public function verify(CAPA $capa, array $data): CAPA
    {
        $capa->update([
            'status' => 'verified',
            'verified_by_user_id' => auth()->id(),
            'verification_date' => now(),
            'verification_method' => $data['verification_method'] ?? null,
            'verification_results' => $data['verification_results'] ?? null,
            'effectiveness_confirmed' => $data['effectiveness_confirmed'] ?? false,
        ]);

        return $capa->fresh();
    }

    /**
     * Close CAPA
     */
    public function close(CAPA $capa, ?string $notes = null): CAPA
    {
        $capa->update([
            'status' => 'closed',
            'closed_by_user_id' => auth()->id(),
            'closure_date' => now(),
            'closure_notes' => $notes,
        ]);

        return $capa->fresh();
    }

    /**
     * Create action for CAPA
     */
    public function createAction(CAPA $capa, array $data): CAPAAction
    {
        $data['capa_id'] = $capa->id;

        // Auto-calculate sequence order if not provided
        if (!isset($data['sequence_order'])) {
            $data['sequence_order'] = $capa->actions()->count() + 1;
        }

        $data['status'] = 'pending';
        $data['is_completed'] = false;

        return CAPAAction::create($data);
    }

    /**
     * Complete action
     */
    public function completeAction(CAPAAction $action, array $data): CAPAAction
    {
        $action->update([
            'is_completed' => true,
            'status' => 'completed',
            'completed_date' => now(),
            'completed_by_user_id' => auth()->id(),
            'completion_notes' => $data['completion_notes'] ?? null,
            'evidence_paths' => $data['evidence_paths'] ?? null,
        ]);

        // Check if all actions are complete and auto-update CAPA status
        $capa = $action->capa;
        $allActionsComplete = $capa->actions()->where('is_completed', false)->count() === 0;

        if ($allActionsComplete && $capa->status === 'in_progress') {
            $this->completeImplementation($capa);
        }

        return $action->fresh();
    }

    /**
     * Get overdue CAPAs
     */
    public function getOverdueCAPAs(): Collection
    {
        return CAPA::overdue()
            ->with(['assignedTo', 'raisedBy'])
            ->get();
    }

    /**
     * Get CAPAs pending approval
     */
    public function getPendingApproval(): Collection
    {
        return CAPA::pendingApproval()
            ->with(['raisedBy', 'assignedTo'])
            ->get();
    }

    /**
     * Get CAPAs pending verification
     */
    public function getPendingVerification(): Collection
    {
        return CAPA::pendingVerification()
            ->with(['assignedTo', 'raisedBy'])
            ->get();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => CAPA::count(),
            'draft' => CAPA::where('status', 'draft')->count(),
            'pending_approval' => CAPA::where('status', 'submitted')->count(),
            'in_progress' => CAPA::where('status', 'in_progress')->count(),
            'pending_verification' => CAPA::where('status', 'completed')->count(),
            'verified' => CAPA::where('status', 'verified')->count(),
            'closed' => CAPA::where('status', 'closed')->count(),
            'overdue' => CAPA::overdue()->count(),
            'corrective_actions' => CAPA::where('type', 'corrective')->count(),
            'preventive_actions' => CAPA::where('type', 'preventive')->count(),
            'critical_priority' => CAPA::where('priority', 'critical')->count(),
        ];
    }

    /**
     * Get CAPA metrics
     */
    public function getMetrics(): array
    {
        $completedCAPAs = CAPA::whereIn('status', ['verified', 'closed'])
            ->get();

        $avgCompletionDays = $completedCAPAs->avg(function ($capa) {
            if ($capa->actual_completion_date && $capa->created_at) {
                return $capa->created_at->diffInDays($capa->actual_completion_date);
            }
            return 0;
        });

        $onTimeCompletionRate = 0;
        if ($completedCAPAs->count() > 0) {
            $onTimeCount = $completedCAPAs->filter(function ($capa) {
                return $capa->actual_completion_date <= $capa->target_completion_date;
            })->count();

            $onTimeCompletionRate = round(($onTimeCount / $completedCAPAs->count()) * 100);
        }

        return [
            'avg_completion_days' => round($avgCompletionDays, 1),
            'on_time_completion_rate' => $onTimeCompletionRate,
            'total_completed' => $completedCAPAs->count(),
        ];
    }
}
