<?php

namespace App\Modules\ContractorManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContractorManagement\Models\Contractor;
use App\Modules\ContractorManagement\Services\ContractorService;
use App\Modules\ContractorManagement\Services\ContractorCompanyService;
use App\Modules\ContractorManagement\Requests\StoreContractorRequest;
use App\Modules\ContractorManagement\Requests\UpdateContractorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContractorController extends Controller
{
    protected ContractorService $service;
    protected ContractorCompanyService $companyService;

    public function __construct(ContractorService $service, ContractorCompanyService $companyService)
    {
        $this->service = $service;
        $this->companyService = $companyService;
    }

    /**
     * Display a listing of contractors
     */
    public function index(): View
    {
        $contractors = $this->service->getAllForBranch(auth()->user()->branch_id);
        $expiringInductions = $this->service->getExpiringInduction(30);

        return view('content.ContractorManagement.Index', compact('contractors', 'expiringInductions'));
    }

    /**
     * Show the form for creating a new contractor
     */
    public function create(): View
    {
        $companies = $this->companyService->getActive();

        return view('content.ContractorManagement.Create', compact('companies'));
    }

    /**
     * Store a newly created contractor
     */
    public function store(StoreContractorRequest $request): RedirectResponse
    {
        try {
            $contractor = $this->service->create($request->validated());

            return redirect()
                ->route('contractors.show', $contractor)
                ->with('success', 'Contractor created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create contractor: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified contractor
     */
    public function show(Contractor $contractor): View
    {
        $contractor->load([
            'company',
            'inductor',
            'inductions.inductionModule',
            'certifications',
            'signInLogs' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        return view('content.ContractorManagement.Show', [
            'contractor' => $contractor,
        ]);
    }

    /**
     * Show the form for editing the specified contractor
     */
    public function edit(Contractor $contractor): View
    {
        $companies = $this->companyService->getActive();

        return view('content.ContractorManagement.Edit', [
            'contractor' => $contractor,
            'companies' => $companies,
        ]);
    }

    /**
     * Update the specified contractor
     */
    public function update(UpdateContractorRequest $request, Contractor $contractor): RedirectResponse
    {
        try {
            $contractor = $this->service->update($contractor, $request->validated());

            return redirect()
                ->route('contractors.show', $contractor)
                ->with('success', 'Contractor updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update contractor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified contractor
     */
    public function destroy(Contractor $contractor): RedirectResponse
    {
        try {
            $this->service->delete($contractor);

            return redirect()
                ->route('contractors.index')
                ->with('success', 'Contractor deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete contractor: ' . $e->getMessage());
        }
    }

    /**
     * Complete induction for contractor
     */
    public function completeInduction(Contractor $contractor): RedirectResponse
    {
        try {
            $validityMonths = request('validity_months', 12);
            $this->service->completeInduction($contractor, $validityMonths);

            return redirect()
                ->route('contractors.show', $contractor)
                ->with('success', 'Contractor induction completed successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to complete induction: ' . $e->getMessage());
        }
    }

    /**
     * Grant site access to contractor
     */
    public function grantSiteAccess(Contractor $contractor): RedirectResponse
    {
        try {
            $this->service->grantSiteAccess($contractor);

            return redirect()
                ->route('contractors.show', $contractor)
                ->with('success', 'Site access granted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to grant site access: ' . $e->getMessage());
        }
    }

    /**
     * Revoke site access from contractor
     */
    public function revokeSiteAccess(Contractor $contractor): RedirectResponse
    {
        try {
            $reason = request('reason', 'No reason provided');
            $this->service->revokeSiteAccess($contractor, $reason);

            return redirect()
                ->route('contractors.show', $contractor)
                ->with('success', 'Site access revoked successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to revoke site access: ' . $e->getMessage());
        }
    }

    /**
     * Sign in contractor
     */
    public function signIn(Contractor $contractor): RedirectResponse
    {
        try {
            $signInData = request()->only([
                'location',
                'purpose',
                'work_description',
                'areas_accessed',
                'ppe_acknowledged',
                'emergency_procedures_acknowledged',
                'ppe_items',
                'entry_method',
            ]);

            $this->service->signIn($contractor, $signInData);

            return redirect()
                ->route('contractors.show', $contractor)
                ->with('success', 'Contractor signed in successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to sign in contractor: ' . $e->getMessage());
        }
    }

    /**
     * Sign out contractor
     */
    public function signOut(Contractor $contractor): RedirectResponse
    {
        try {
            $this->service->signOut($contractor);

            return redirect()
                ->route('contractors.show', $contractor)
                ->with('success', 'Contractor signed out successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to sign out contractor: ' . $e->getMessage());
        }
    }
}
