<?php

namespace App\Modules\ContractorManagement\Repositories;

use App\Modules\ContractorManagement\Models\InductionModule;
use Illuminate\Database\Eloquent\Collection;

class InductionModuleRepository
{
    /**
     * Find induction module by ID
     */
    public function findById(int $id): ?InductionModule
    {
        return InductionModule::with(['branch', 'contractorInductions'])->find($id);
    }

    /**
     * Find all induction modules for a branch
     */
    public function findByBranch(string $branchId): Collection
    {
        return InductionModule::where('branch_id', $branchId)
            ->ordered()
            ->get();
    }

    /**
     * Get all induction modules with pagination
     */
    public function getAll(int $perPage = 15)
    {
        return InductionModule::with(['branch', 'contractorInductions'])
            ->ordered()
            ->paginate($perPage);
    }

    /**
     * Get active induction modules
     */
    public function getActive(): Collection
    {
        return InductionModule::active()
            ->ordered()
            ->get();
    }

    /**
     * Get mandatory induction modules
     */
    public function getMandatory(): Collection
    {
        return InductionModule::mandatory()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get modules with quiz
     */
    public function getWithQuiz(): Collection
    {
        return InductionModule::where('has_quiz', true)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Find by title
     */
    public function findByTitle(string $title): ?InductionModule
    {
        return InductionModule::where('title', $title)->first();
    }

    /**
     * Create induction module
     */
    public function create(array $data): InductionModule
    {
        return InductionModule::create($data);
    }

    /**
     * Update induction module
     */
    public function update(InductionModule $module, array $data): InductionModule
    {
        $module->update($data);
        return $module->fresh();
    }

    /**
     * Delete induction module (soft delete)
     */
    public function delete(InductionModule $module): bool
    {
        return $module->delete();
    }

    /**
     * Restore soft-deleted induction module
     */
    public function restore(int $id): bool
    {
        $module = InductionModule::withTrashed()->find($id);
        return $module ? $module->restore() : false;
    }

    /**
     * Search induction modules
     */
    public function search(string $query): Collection
    {
        return InductionModule::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->ordered()
        ->get();
    }

    /**
     * Update display order
     */
    public function updateDisplayOrder(array $moduleIdsInOrder): bool
    {
        try {
            foreach ($moduleIdsInOrder as $index => $moduleId) {
                InductionModule::where('id', $moduleId)
                    ->update(['display_order' => $index]);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
