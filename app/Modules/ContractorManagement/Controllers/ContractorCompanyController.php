<?php

namespace App\Modules\ContractorManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContractorManagement\Models\ContractorCompany;
use App\Modules\ContractorManagement\Services\ContractorCompanyService;
use App\Modules\ContractorManagement\Requests\StoreContractorCompanyRequest;
use App\Modules\ContractorManagement\Requests\UpdateContractorCompanyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContractorCompanyController extends Controller
{
    protected ContractorCompanyService $service;

    public function __construct(ContractorCompanyService $service)
    {
        $this->service = $service;
        $this->middleware('auth');
        $this->middleware('permission:view_contractor_companies')->only(['index', 'show']);
        $this->middleware('permission:create_contractor_companies')->only(['create', 'store']);
        $this->middleware('permission:edit_contractor_companies')->only(['edit', 'update']);
        $this->middleware('permission:delete_contractor_companies')->only(['destroy']);
    }

    /**
     * Display a listing of contractor companies
     */
    public function index(): View
    {
        $companies = $this->service->getAllForBranch(auth()->user()->branch_id);
        $expiringInsurance = $this->service->getExpiringInsurance(30);

        return view('content.contractor-management.companies.index', compact('companies', 'expiringInsurance'));
    }

    /**
     * Show the form for creating a new contractor company
     */
    public function create(): View
    {
        return view('content.contractor-management.companies.create');
    }

    /**
     * Store a newly created contractor company
     */
    public function store(StoreContractorCompanyRequest $request): RedirectResponse
    {
        try {
            $company = $this->service->create($request->validated());

            return redirect()
                ->route('contractor-companies.show', $company)
                ->with('success', 'Contractor company created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create contractor company: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified contractor company
     */
    public function show(ContractorCompany $contractorCompany): View
    {
        $contractorCompany->load(['contractors', 'verifier']);

        return view('content.contractor-management.companies.show', [
            'company' => $contractorCompany,
        ]);
    }

    /**
     * Show the form for editing the specified contractor company
     */
    public function edit(ContractorCompany $contractorCompany): View
    {
        return view('content.contractor-management.companies.edit', [
            'company' => $contractorCompany,
        ]);
    }

    /**
     * Update the specified contractor company
     */
    public function update(UpdateContractorCompanyRequest $request, ContractorCompany $contractorCompany): RedirectResponse
    {
        try {
            $company = $this->service->update($contractorCompany, $request->validated());

            return redirect()
                ->route('contractor-companies.show', $company)
                ->with('success', 'Contractor company updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update contractor company: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified contractor company
     */
    public function destroy(ContractorCompany $contractorCompany): RedirectResponse
    {
        try {
            $this->service->delete($contractorCompany);

            return redirect()
                ->route('contractor-companies.index')
                ->with('success', 'Contractor company deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete contractor company: ' . $e->getMessage());
        }
    }

    /**
     * Verify a contractor company
     */
    public function verify(ContractorCompany $contractorCompany): RedirectResponse
    {
        try {
            $this->service->verify($contractorCompany);

            return redirect()
                ->route('contractor-companies.show', $contractorCompany)
                ->with('success', 'Contractor company verified successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to verify contractor company: ' . $e->getMessage());
        }
    }

    /**
     * Suspend a contractor company
     */
    public function suspend(ContractorCompany $contractorCompany): RedirectResponse
    {
        try {
            $reason = request('reason', 'No reason provided');
            $this->service->suspend($contractorCompany, $reason);

            return redirect()
                ->route('contractor-companies.show', $contractorCompany)
                ->with('success', 'Contractor company suspended successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to suspend contractor company: ' . $e->getMessage());
        }
    }

    /**
     * Reactivate a contractor company
     */
    public function reactivate(ContractorCompany $contractorCompany): RedirectResponse
    {
        try {
            $this->service->reactivate($contractorCompany);

            return redirect()
                ->route('contractor-companies.show', $contractorCompany)
                ->with('success', 'Contractor company reactivated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to reactivate contractor company: ' . $e->getMessage());
        }
    }

    /**
     * Update performance rating
     */
    public function updateRating(ContractorCompany $contractorCompany): RedirectResponse
    {
        try {
            $rating = request('rating');
            $this->service->updatePerformanceRating($contractorCompany, $rating);

            return redirect()
                ->route('contractor-companies.show', $contractorCompany)
                ->with('success', 'Performance rating updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update performance rating: ' . $e->getMessage());
        }
    }
}
