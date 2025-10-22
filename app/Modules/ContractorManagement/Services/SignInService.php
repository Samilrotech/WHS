<?php

namespace App\Modules\ContractorManagement\Services;

use App\Modules\ContractorManagement\Models\SignInLog;
use App\Modules\ContractorManagement\Models\Contractor;
use App\Modules\ContractorManagement\Models\Visitor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class SignInService
{
    /**
     * Get all sign-in logs for current branch
     */
    public function getAllForBranch(string $branchId): Collection
    {
        return SignInLog::where('branch_id', $branchId)
            ->with(['signable'])
            ->latest('signed_in_at')
            ->get();
    }

    /**
     * Get currently signed in
     */
    public function getSignedIn(): Collection
    {
        return SignInLog::signedIn()
            ->with(['signable'])
            ->get();
    }

    /**
     * Get overdue sign-outs
     */
    public function getOverdue(): Collection
    {
        return SignInLog::overdue()
            ->with(['signable'])
            ->get();
    }

    /**
     * Get today's sign-in logs
     */
    public function getToday(): Collection
    {
        return SignInLog::today()
            ->with(['signable'])
            ->get();
    }

    /**
     * Sign in contractor
     */
    public function signInContractor(Contractor $contractor, array $data): SignInLog
    {
        try {
            DB::beginTransaction();

            // Validate contractor can sign in
            if (!$contractor->site_access_granted) {
                throw new \Exception('Contractor does not have site access');
            }

            if (!$contractor->hasValidInduction()) {
                throw new \Exception('Contractor induction has expired');
            }

            if ($contractor->isSignedIn()) {
                throw new \Exception('Contractor is already signed in');
            }

            // Create sign-in log
            $signInLog = SignInLog::create(array_merge($data, [
                'branch_id' => $contractor->branch_id,
                'signable_type' => Contractor::class,
                'signable_id' => $contractor->id,
                'signed_in_at' => now(),
                'status' => 'signed_in',
            ]));

            Log::info('Contractor signed in', [
                'sign_in_log_id' => $signInLog->id,
                'contractor_id' => $contractor->id,
                'contractor_name' => $contractor->full_name,
                'location' => $data['location'] ?? null,
            ]);

            DB::commit();

            return $signInLog;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sign in contractor', [
                'contractor_id' => $contractor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sign in visitor
     */
    public function signInVisitor(Visitor $visitor, array $data): SignInLog
    {
        try {
            DB::beginTransaction();

            // Validate visitor can sign in
            if (!$visitor->hasSafetyBriefing()) {
                throw new \Exception('Visitor must complete safety briefing before signing in');
            }

            if (!$visitor->isOnSite()) {
                throw new \Exception('Visitor arrival must be recorded before signing in');
            }

            if ($visitor->isSignedIn()) {
                throw new \Exception('Visitor is already signed in');
            }

            // Create sign-in log
            $signInLog = SignInLog::create(array_merge($data, [
                'branch_id' => $visitor->branch_id,
                'signable_type' => Visitor::class,
                'signable_id' => $visitor->id,
                'signed_in_at' => now(),
                'status' => 'signed_in',
            ]));

            Log::info('Visitor signed in', [
                'sign_in_log_id' => $signInLog->id,
                'visitor_id' => $visitor->id,
                'visitor_name' => $visitor->full_name,
                'location' => $data['location'] ?? null,
            ]);

            DB::commit();

            return $signInLog;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sign in visitor', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sign out
     */
    public function signOut(SignInLog $signInLog): SignInLog
    {
        try {
            DB::beginTransaction();

            // Validate sign-in log
            if ($signInLog->status !== 'signed_in') {
                throw new \Exception('Sign-in log is not in signed_in status');
            }

            if ($signInLog->signed_out_at) {
                throw new \Exception('Already signed out');
            }

            // Update sign-in log
            $signInLog->update([
                'signed_out_at' => now(),
                'status' => 'signed_out',
            ]);

            $personName = $signInLog->person_name ?? 'Unknown';

            Log::info('Signed out', [
                'sign_in_log_id' => $signInLog->id,
                'person_name' => $personName,
                'time_on_site' => $signInLog->formatted_time_on_site,
            ]);

            DB::commit();

            return $signInLog->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sign out', [
                'sign_in_log_id' => $signInLog->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update areas accessed
     */
    public function updateAreasAccessed(SignInLog $signInLog, array $areas): SignInLog
    {
        try {
            DB::beginTransaction();

            $signInLog->update([
                'areas_accessed' => array_unique(array_merge($signInLog->areas_accessed ?? [], $areas)),
            ]);

            DB::commit();

            return $signInLog->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update areas accessed', [
                'sign_in_log_id' => $signInLog->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check safety compliance
     */
    public function checkSafetyCompliance(SignInLog $signInLog): bool
    {
        return $signInLog->hasSafetyCompliance();
    }

    /**
     * Auto sign-out overdue logs
     */
    public function autoSignOutOverdue(): array
    {
        try {
            $overdueLogs = $this->getOverdue();

            $signedOut = [];

            foreach ($overdueLogs as $log) {
                try {
                    $this->signOut($log);

                    $signedOut[] = [
                        'sign_in_log_id' => $log->id,
                        'person_name' => $log->person_name,
                        'time_on_site' => $log->formatted_time_on_site,
                    ];
                } catch (\Exception $e) {
                    Log::error('Failed to auto sign-out', [
                        'sign_in_log_id' => $log->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Auto sign-out overdue completed', [
                'count' => count($signedOut),
            ]);

            return $signedOut;
        } catch (\Exception $e) {
            Log::error('Failed to auto sign-out overdue', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get sign-in statistics
     */
    public function getStatistics(?string $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = SignInLog::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->today();
        }

        $total = $query->count();
        $signedIn = (clone $query)->signedIn()->count();
        $signedOut = (clone $query)->signedOut()->count();
        $overdue = (clone $query)->overdue()->count();
        $contractors = (clone $query)->contractors()->count();
        $visitors = (clone $query)->visitors()->count();

        $averageTimeOnSite = (clone $query)
            ->signedOut()
            ->avg('time_spent_minutes') ?? 0;

        return [
            'total_sign_ins' => $total,
            'currently_signed_in' => $signedIn,
            'signed_out' => $signedOut,
            'overdue' => $overdue,
            'contractors' => $contractors,
            'visitors' => $visitors,
            'average_time_on_site_minutes' => round($averageTimeOnSite, 2),
        ];
    }

    /**
     * Get sign-in logs by date range
     */
    public function getByDateRange(string $startDate, string $endDate, ?string $branchId = null): Collection
    {
        $query = SignInLog::dateRange($startDate, $endDate)
            ->with(['signable'])
            ->latest('signed_in_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    /**
     * Get sign-in logs by entry method
     */
    public function getByEntryMethod(string $method, ?string $branchId = null): Collection
    {
        $query = SignInLog::byEntryMethod($method)
            ->with(['signable']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }
}
