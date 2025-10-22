<?php

namespace App\Modules\ContractorManagement\Services;

use App\Modules\ContractorManagement\Models\InductionModule;
use App\Modules\ContractorManagement\Models\ContractorInduction;
use App\Modules\ContractorManagement\Models\Contractor;
use App\Modules\ContractorManagement\Repositories\InductionModuleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class InductionService
{
    protected InductionModuleRepository $repository;

    public function __construct(InductionModuleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all induction modules for current branch
     */
    public function getAllForBranch(string $branchId): Collection
    {
        return $this->repository->findByBranch($branchId);
    }

    /**
     * Get active induction modules
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get mandatory induction modules
     */
    public function getMandatory(): Collection
    {
        return $this->repository->getMandatory();
    }

    /**
     * Create new induction module
     */
    public function createModule(array $data): InductionModule
    {
        try {
            DB::beginTransaction();

            // Ensure branch_id is set from authenticated user if not provided
            if (!isset($data['branch_id']) && auth()->check()) {
                $data['branch_id'] = auth()->user()->branch_id;
            }

            $module = $this->repository->create($data);

            Log::info('Induction module created', [
                'module_id' => $module->id,
                'title' => $module->title,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $module;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create induction module', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update induction module
     */
    public function updateModule(InductionModule $module, array $data): InductionModule
    {
        try {
            DB::beginTransaction();

            $module = $this->repository->update($module, $data);

            Log::info('Induction module updated', [
                'module_id' => $module->id,
                'title' => $module->title,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $module;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update induction module', [
                'module_id' => $module->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Start induction for contractor
     */
    public function startInduction(Contractor $contractor, InductionModule $module): ContractorInduction
    {
        try {
            DB::beginTransaction();

            // Check if contractor already has an in-progress or completed induction for this module
            $existingInduction = ContractorInduction::where('contractor_id', $contractor->id)
                ->where('induction_module_id', $module->id)
                ->whereIn('status', ['in_progress', 'completed'])
                ->first();

            if ($existingInduction) {
                throw new \Exception('Contractor already has an active induction for this module');
            }

            $induction = ContractorInduction::create([
                'branch_id' => $contractor->branch_id,
                'contractor_id' => $contractor->id,
                'induction_module_id' => $module->id,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);

            Log::info('Contractor induction started', [
                'induction_id' => $induction->id,
                'contractor_id' => $contractor->id,
                'module_id' => $module->id,
            ]);

            DB::commit();

            return $induction;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start induction', [
                'contractor_id' => $contractor->id,
                'module_id' => $module->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update video progress
     */
    public function updateVideoProgress(ContractorInduction $induction, int $percentage): ContractorInduction
    {
        try {
            DB::beginTransaction();

            $data = [
                'video_progress_percentage' => min(100, max(0, $percentage)),
            ];

            // Mark video as watched if 100%
            if ($percentage >= 100) {
                $data['video_watched'] = true;
            }

            $induction->update($data);

            DB::commit();

            return $induction->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update video progress', [
                'induction_id' => $induction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Submit quiz attempt
     */
    public function submitQuiz(ContractorInduction $induction, int $score, int $totalQuestions): ContractorInduction
    {
        try {
            DB::beginTransaction();

            $module = $induction->inductionModule;
            $percentage = ($score / $totalQuestions) * 100;
            $passed = $percentage >= $module->pass_mark_percentage;

            $induction->update([
                'quiz_score' => $percentage,
                'quiz_attempts' => $induction->quiz_attempts + 1,
                'quiz_passed' => $passed,
            ]);

            Log::info('Quiz submitted', [
                'induction_id' => $induction->id,
                'score' => $percentage,
                'passed' => $passed,
                'attempt' => $induction->quiz_attempts,
            ]);

            // If passed and video watched, complete the induction
            if ($passed && $induction->video_watched) {
                $this->completeInduction($induction);
            }

            DB::commit();

            return $induction->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit quiz', [
                'induction_id' => $induction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Complete induction
     */
    public function completeInduction(ContractorInduction $induction): ContractorInduction
    {
        try {
            DB::beginTransaction();

            $module = $induction->inductionModule;

            // Check if all requirements are met
            if (!$induction->video_watched) {
                throw new \Exception('Video must be watched before completing induction');
            }

            if ($module->has_quiz && !$induction->quiz_passed) {
                throw new \Exception('Quiz must be passed before completing induction');
            }

            // Calculate time spent
            $timeSpent = $induction->started_at ? $induction->started_at->diffInMinutes(now()) : 0;

            // Calculate expiry date
            $expiryDate = $module->validity_months
                ? now()->addMonths($module->validity_months)
                : null;

            $induction->update([
                'completed_at' => now(),
                'time_spent_minutes' => $timeSpent,
                'expiry_date' => $expiryDate,
                'status' => 'completed',
                'certificate_issued_at' => now(),
            ]);

            // Update contractor record
            $contractor = $induction->contractor;
            $contractor->update([
                'induction_completed' => true,
                'induction_completion_date' => now(),
                'induction_expiry_date' => $expiryDate,
                'inducted_by' => auth()->id(),
            ]);

            Log::info('Contractor induction completed', [
                'induction_id' => $induction->id,
                'contractor_id' => $contractor->id,
                'certificate_number' => $induction->certificate_number,
                'expiry_date' => $expiryDate,
            ]);

            DB::commit();

            return $induction->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete induction', [
                'induction_id' => $induction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check and expire old inductions
     */
    public function checkExpiredInductions(): array
    {
        try {
            $expiredInductions = ContractorInduction::where('status', 'completed')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', now())
                ->get();

            $updated = [];

            foreach ($expiredInductions as $induction) {
                $induction->update(['status' => 'expired']);

                // Update contractor record if this was their current induction
                $contractor = $induction->contractor;
                if ($contractor->induction_expiry_date <= now()) {
                    $contractor->update([
                        'induction_completed' => false,
                        'site_access_granted' => false,
                    ]);
                }

                $updated[] = [
                    'induction_id' => $induction->id,
                    'contractor' => $contractor->full_name,
                    'expiry_date' => $induction->expiry_date,
                ];
            }

            Log::info('Expired inductions processed', [
                'count' => count($updated),
            ]);

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to check expired inductions', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get induction statistics for module
     */
    public function getModuleStatistics(InductionModule $module): array
    {
        $totalInductions = $module->contractorInductions()->count();
        $completedInductions = $module->contractorInductions()->completed()->count();
        $inProgressInductions = $module->contractorInductions()->where('status', 'in_progress')->count();
        $expiredInductions = $module->contractorInductions()->expired()->count();

        $completionRate = $totalInductions > 0
            ? round(($completedInductions / $totalInductions) * 100, 2)
            : 0;

        $averageTimeSpent = $module->contractorInductions()
            ->completed()
            ->avg('time_spent_minutes');

        return [
            'total_inductions' => $totalInductions,
            'completed' => $completedInductions,
            'in_progress' => $inProgressInductions,
            'expired' => $expiredInductions,
            'completion_rate' => $completionRate,
            'average_time_spent_minutes' => round($averageTimeSpent ?? 0, 2),
            'average_quiz_score' => $module->average_quiz_score,
        ];
    }
}
