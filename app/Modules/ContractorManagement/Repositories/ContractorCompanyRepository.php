<?php

namespace App\Modules\ContractorManagement\Repositories;

use App\Modules\ContractorManagement\Models\ContractorCompany;
use Illuminate\Database\Eloquent\Collection;

class ContractorCompanyRepository
{
    /**
     * Find contractor company by ID
     */
    public function findById(int $id): ?ContractorCompany
    {
        return ContractorCompany::with(['branch', 'verifier', 'contractors'])->find($id);
    }

    /**
     * Find all contractor companies for a branch
     */
    public function findByBranch(string $branchId): Collection
    {
        return ContractorCompany::where('branch_id', $branchId)
            ->with(['contractors'])
            ->latest()
            ->get();
    }

    /**
     * Get all contractor companies with pagination
     */
    public function getAll(int $perPage = 15)
    {
        return ContractorCompany::with(['branch', 'contractors'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get active contractor companies
     */
    public function getActive(): Collection
    {
        return ContractorCompany::active()
            ->with(['contractors'])
            ->latest()
            ->get();
    }

    /**
     * Get verified contractor companies
     */
    public function getVerified(): Collection
    {
        return ContractorCompany::verified()
            ->with(['verifier'])
            ->latest()
            ->get();
    }

    /**
     * Get companies with expiring insurance
     */
    public function getExpiringInsurance(int $days = 30): Collection
    {
        return ContractorCompany::expiringInsurance()
            ->with(['contractors'])
            ->get();
    }

    /**
     * Find by ABN
     */
    public function findByAbn(string $abn): ?ContractorCompany
    {
        return ContractorCompany::where('abn', $abn)->first();
    }

    /**
     * Find by company name
     */
    public function findByName(string $name): Collection
    {
        return ContractorCompany::where('company_name', 'like', "%{$name}%")
            ->with(['contractors'])
            ->get();
    }

    /**
     * Create contractor company
     */
    public function create(array $data): ContractorCompany
    {
        return ContractorCompany::create($data);
    }

    /**
     * Update contractor company
     */
    public function update(ContractorCompany $company, array $data): ContractorCompany
    {
        $company->update($data);
        return $company->fresh();
    }

    /**
     * Delete contractor company (soft delete)
     */
    public function delete(ContractorCompany $company): bool
    {
        return $company->delete();
    }

    /**
     * Restore soft-deleted contractor company
     */
    public function restore(int $id): bool
    {
        $company = ContractorCompany::withTrashed()->find($id);
        return $company ? $company->restore() : false;
    }

    /**
     * Search contractor companies
     */
    public function search(string $query): Collection
    {
        return ContractorCompany::where(function ($q) use ($query) {
            $q->where('company_name', 'like', "%{$query}%")
              ->orWhere('abn', 'like', "%{$query}%")
              ->orWhere('trading_name', 'like', "%{$query}%")
              ->orWhere('primary_contact_name', 'like', "%{$query}%")
              ->orWhere('primary_contact_email', 'like', "%{$query}%");
        })
        ->with(['contractors'])
        ->get();
    }
}
