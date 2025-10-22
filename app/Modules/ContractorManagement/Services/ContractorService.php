<?php

namespace App\Modules\ContractorManagement\Services;

use App\Modules\ContractorManagement\Models\Contractor;
use App\Modules\ContractorManagement\Repositories\ContractorRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class ContractorService
{
    protected ContractorRepository $repository;

    public function __construct(ContractorRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all contractors for current branch
     */
    public function getAllForBranch(string $branchId): Collection
    {
        return $this->repository->findByBranch($branchId);
    }

    /**
     * Get active contractors
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get contractors with site access
     */
    public function getWithSiteAccess(): Collection
    {
        return $this->repository->getWithSiteAccess();
    }

    /**
     * Get contractors with valid induction
     */
    public function getWithValidInduction(): Collection
    {
        return $this->repository->getWithValidInduction();
    }

    /**
     * Get contractors with expiring induction
     */
    public function getExpiringInduction(int $days = 30): Collection
    {
        return $this->repository->getExpiringInduction($days);
    }

    /**
     * Create new contractor
     */
    public function create(array $data): Contractor
    {
        try {
            DB::beginTransaction();

            // Ensure branch_id is set from authenticated user if not provided
            if (!isset($data['branch_id']) && auth()->check()) {
                $data['branch_id'] = auth()->user()->branch_id;
            }

            $contractor = $this->repository->create($data);

            // Log contractor creation
            Log::info('Contractor created', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
                'email' => $contractor->email,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $contractor;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create contractor', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update contractor
     */
    public function update(Contractor $contractor, array $data): Contractor
    {
        try {
            DB::beginTransaction();

            $contractor = $this->repository->update($contractor, $data);

            // Log contractor update
            Log::info('Contractor updated', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $contractor;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update contractor', [
                'contractor_id' => $contractor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Complete induction for contractor
     */
    public function completeInduction(Contractor $contractor, ?int $validityMonths = 12): Contractor
    {
        try {
            DB::beginTransaction();

            $contractor = $this->repository->update($contractor, [
                'induction_completed' => true,
                'induction_completion_date' => now(),
                'induction_expiry_date' => now()->addMonths($validityMonths),
                'inducted_by' => auth()->id(),
            ]);

            Log::info('Contractor induction completed', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
                'expiry_date' => $contractor->induction_expiry_date,
                'inducted_by' => auth()->id(),
            ]);

            DB::commit();

            return $contractor;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete contractor induction', [
                'contractor_id' => $contractor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Grant site access to contractor
     */
    public function grantSiteAccess(Contractor $contractor): Contractor
    {
        try {
            DB::beginTransaction();

            // Check if contractor has valid induction
            if (!$contractor->hasValidInduction()) {
                throw new \Exception('Contractor must have valid induction before granting site access');
            }

            $contractor = $this->repository->update($contractor, [
                'site_access_granted' => true,
            ]);

            Log::info('Site access granted to contractor', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $contractor;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to grant site access', [
                'contractor_id' => $contractor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Revoke site access from contractor
     */
    public function revokeSiteAccess(Contractor $contractor, string $reason): Contractor
    {
        try {
            DB::beginTransaction();

            $contractor = $this->repository->update($contractor, [
                'site_access_granted' => false,
                'notes' => ($contractor->notes ?? '') . "\n\nSite access revoked on " . now()->format('Y-m-d H:i:s') . ": {$reason}",
            ]);

            Log::warning('Site access revoked from contractor', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $contractor;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revoke site access', [
                'contractor_id' => $contractor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sign in contractor
     */
    public function signIn(Contractor $contractor, array $signInData): Contractor
    {
        try {
            DB::beginTransaction();

            // Check if contractor has site access
            if (!$contractor->site_access_granted) {
                throw new \Exception('Contractor does not have site access');
            }

            // Check if contractor has valid induction
            if (!$contractor->hasValidInduction()) {
                throw new \Exception('Contractor induction has expired');
            }

            // Check if contractor is already signed in
            if ($contractor->isSignedIn()) {
                throw new \Exception('Contractor is already signed in');
            }

            // Create sign-in log
            $contractor->signInLogs()->create(array_merge($signInData, [
                'branch_id' => $contractor->branch_id,
                'signed_in_at' => now(),
                'status' => 'signed_in',
            ]));

            Log::info('Contractor signed in', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
            ]);

            DB::commit();

            return $contractor->fresh();
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
     * Sign out contractor
     */
    public function signOut(Contractor $contractor): Contractor
    {
        try {
            DB::beginTransaction();

            // Get current sign-in log
            $signInLog = $contractor->signInLogs()
                ->where('status', 'signed_in')
                ->whereNull('signed_out_at')
                ->latest()
                ->first();

            if (!$signInLog) {
                throw new \Exception('No active sign-in found for contractor');
            }

            // Update sign-in log
            $signInLog->update([
                'signed_out_at' => now(),
                'status' => 'signed_out',
            ]);

            Log::info('Contractor signed out', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
                'time_on_site' => $signInLog->time_on_site,
            ]);

            DB::commit();

            return $contractor->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sign out contractor', [
                'contractor_id' => $contractor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete contractor (soft delete)
     */
    public function delete(Contractor $contractor): bool
    {
        try {
            DB::beginTransaction();

            // Check if contractor is currently signed in
            if ($contractor->isSignedIn()) {
                throw new \Exception('Cannot delete contractor who is currently signed in');
            }

            $deleted = $this->repository->delete($contractor);

            Log::warning('Contractor deleted', [
                'contractor_id' => $contractor->id,
                'name' => $contractor->full_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete contractor', [
                'contractor_id' => $contractor->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check and notify about expiring inductions
     */
    public function checkExpiringInductions(): array
    {
        $expiringContractors = $this->repository->getExpiringInduction(30);

        $notifications = [];

        foreach ($expiringContractors as $contractor) {
            $daysRemaining = $contractor->induction_expiry_date->diffInDays(now());

            $notifications[] = [
                'contractor' => $contractor,
                'expiry_date' => $contractor->induction_expiry_date,
                'days_remaining' => $daysRemaining,
                'urgency' => $daysRemaining <= 7 ? 'high' : 'medium',
            ];
        }

        return $notifications;
    }
}
