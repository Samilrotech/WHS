<?php

namespace App\Modules\ContractorManagement\Services;

use App\Modules\ContractorManagement\Models\ContractorCompany;
use App\Modules\ContractorManagement\Repositories\ContractorCompanyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class ContractorCompanyService
{
    protected ContractorCompanyRepository $repository;

    public function __construct(ContractorCompanyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all contractor companies for current branch
     */
    public function getAllForBranch(string $branchId): Collection
    {
        return $this->repository->findByBranch($branchId);
    }

    /**
     * Get active contractor companies
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get verified contractor companies
     */
    public function getVerified(): Collection
    {
        return $this->repository->getVerified();
    }

    /**
     * Get companies with expiring insurance
     */
    public function getExpiringInsurance(int $days = 30): Collection
    {
        return $this->repository->getExpiringInsurance($days);
    }

    /**
     * Create new contractor company
     */
    public function create(array $data): ContractorCompany
    {
        try {
            DB::beginTransaction();

            // Ensure branch_id is set from authenticated user if not provided
            if (!isset($data['branch_id']) && auth()->check()) {
                $data['branch_id'] = auth()->user()->branch_id;
            }

            $company = $this->repository->create($data);

            // Log company creation
            Log::info('Contractor company created', [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'abn' => $company->abn,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create contractor company', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update contractor company
     */
    public function update(ContractorCompany $company, array $data): ContractorCompany
    {
        try {
            DB::beginTransaction();

            $company = $this->repository->update($company, $data);

            // Log company update
            Log::info('Contractor company updated', [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update contractor company', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify a contractor company
     */
    public function verify(ContractorCompany $company): ContractorCompany
    {
        try {
            DB::beginTransaction();

            $company = $this->repository->update($company, [
                'is_verified' => true,
                'verification_date' => now(),
                'verified_by' => auth()->id(),
            ]);

            Log::info('Contractor company verified', [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'verified_by' => auth()->id(),
            ]);

            DB::commit();

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to verify contractor company', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update performance rating
     */
    public function updatePerformanceRating(ContractorCompany $company, float $rating): ContractorCompany
    {
        if ($rating < 0 || $rating > 5) {
            throw new \InvalidArgumentException('Performance rating must be between 0 and 5');
        }

        try {
            DB::beginTransaction();

            $company = $this->repository->update($company, [
                'performance_rating' => $rating,
            ]);

            Log::info('Contractor company performance rating updated', [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'rating' => $rating,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update performance rating', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Suspend contractor company
     */
    public function suspend(ContractorCompany $company, string $reason): ContractorCompany
    {
        try {
            DB::beginTransaction();

            $company = $this->repository->update($company, [
                'status' => 'suspended',
                'notes' => ($company->notes ?? '') . "\n\nSuspended on " . now()->format('Y-m-d H:i:s') . ": {$reason}",
            ]);

            Log::warning('Contractor company suspended', [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to suspend contractor company', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reactivate contractor company
     */
    public function reactivate(ContractorCompany $company): ContractorCompany
    {
        try {
            DB::beginTransaction();

            $company = $this->repository->update($company, [
                'status' => 'active',
                'notes' => ($company->notes ?? '') . "\n\nReactivated on " . now()->format('Y-m-d H:i:s'),
            ]);

            Log::info('Contractor company reactivated', [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reactivate contractor company', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete contractor company (soft delete)
     */
    public function delete(ContractorCompany $company): bool
    {
        try {
            DB::beginTransaction();

            // Check if company has active contractors
            if ($company->contractors()->active()->count() > 0) {
                throw new \Exception('Cannot delete company with active contractors');
            }

            $deleted = $this->repository->delete($company);

            Log::warning('Contractor company deleted', [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete contractor company', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check and notify about expiring insurance
     */
    public function checkExpiringInsurance(): array
    {
        $expiringCompanies = $this->repository->getExpiringInsurance(30);

        $notifications = [];

        foreach ($expiringCompanies as $company) {
            $notification = [
                'company' => $company,
                'issues' => [],
            ];

            if ($company->isPublicLiabilityExpiringSoon()) {
                $notification['issues'][] = [
                    'type' => 'public_liability',
                    'expiry_date' => $company->public_liability_expiry_date,
                    'days_remaining' => $company->public_liability_expiry_date->diffInDays(now()),
                ];
            }

            if ($company->isWorkersCompExpiringSoon()) {
                $notification['issues'][] = [
                    'type' => 'workers_comp',
                    'expiry_date' => $company->workers_comp_expiry_date,
                    'days_remaining' => $company->workers_comp_expiry_date->diffInDays(now()),
                ];
            }

            $notifications[] = $notification;
        }

        return $notifications;
    }
}
