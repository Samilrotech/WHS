<?php

namespace App\Modules\ContractorManagement\Repositories;

use App\Modules\ContractorManagement\Models\Contractor;
use Illuminate\Database\Eloquent\Collection;

class ContractorRepository
{
    /**
     * Find contractor by ID
     */
    public function findById(int $id): ?Contractor
    {
        return Contractor::with([
            'branch',
            'company',
            'inductor',
            'inductions',
            'certifications',
            'signInLogs',
        ])->find($id);
    }

    /**
     * Find all contractors for a branch
     */
    public function findByBranch(string $branchId): Collection
    {
        return Contractor::where('branch_id', $branchId)
            ->with(['company', 'inductions', 'certifications'])
            ->latest()
            ->get();
    }

    /**
     * Get all contractors with pagination
     */
    public function getAll(int $perPage = 15)
    {
        return Contractor::with(['branch', 'company', 'inductions'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get active contractors
     */
    public function getActive(): Collection
    {
        return Contractor::active()
            ->with(['company', 'inductions'])
            ->latest()
            ->get();
    }

    /**
     * Get contractors with site access
     */
    public function getWithSiteAccess(): Collection
    {
        return Contractor::withSiteAccess()
            ->with(['company', 'inductions'])
            ->latest()
            ->get();
    }

    /**
     * Get contractors with valid induction
     */
    public function getWithValidInduction(): Collection
    {
        return Contractor::inductionValid()
            ->with(['company', 'inductions'])
            ->latest()
            ->get();
    }

    /**
     * Get contractors with expiring induction
     */
    public function getExpiringInduction(int $days = 30): Collection
    {
        return Contractor::expiringInduction()
            ->with(['company', 'inductor'])
            ->get();
    }

    /**
     * Find by email
     */
    public function findByEmail(string $email): ?Contractor
    {
        return Contractor::where('email', $email)->first();
    }

    /**
     * Find by company
     */
    public function findByCompany(int $companyId): Collection
    {
        return Contractor::where('contractor_company_id', $companyId)
            ->with(['inductions', 'certifications'])
            ->latest()
            ->get();
    }

    /**
     * Get currently signed in contractors
     */
    public function getSignedIn(): Collection
    {
        return Contractor::whereHas('signInLogs', function ($query) {
            $query->where('status', 'signed_in')
                  ->whereNull('signed_out_at');
        })
        ->with(['company', 'signInLogs' => function ($query) {
            $query->where('status', 'signed_in')
                  ->whereNull('signed_out_at')
                  ->latest();
        }])
        ->get();
    }

    /**
     * Create contractor
     */
    public function create(array $data): Contractor
    {
        return Contractor::create($data);
    }

    /**
     * Update contractor
     */
    public function update(Contractor $contractor, array $data): Contractor
    {
        $contractor->update($data);
        return $contractor->fresh();
    }

    /**
     * Delete contractor (soft delete)
     */
    public function delete(Contractor $contractor): bool
    {
        return $contractor->delete();
    }

    /**
     * Restore soft-deleted contractor
     */
    public function restore(int $id): bool
    {
        $contractor = Contractor::withTrashed()->find($id);
        return $contractor ? $contractor->restore() : false;
    }

    /**
     * Search contractors
     */
    public function search(string $query): Collection
    {
        return Contractor::where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhereHas('company', function ($cq) use ($query) {
                  $cq->where('company_name', 'like', "%{$query}%");
              });
        })
        ->with(['company', 'inductions'])
        ->get();
    }
}
