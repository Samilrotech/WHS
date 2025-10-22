<?php

namespace App\Modules\ComplianceReporting\Controllers;

use App\Modules\ComplianceReporting\Models\ComplianceRequirement;
use App\Modules\ComplianceReporting\Services\ComplianceRequirementService;
use App\Modules\ComplianceReporting\Requests\StoreComplianceRequirementRequest;
use App\Modules\ComplianceReporting\Requests\UpdateComplianceRequirementRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceRequirementController
{
    protected ComplianceRequirementService $service;

    public function __construct(ComplianceRequirementService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of compliance requirements
     */
    public function index(Request $request): View
    {
        $branchId = auth()->user()->branch_id;

        $filters = [
            'category' => $request->input('category'),
            'status' => $request->input('status'),
            'risk_level' => $request->input('risk_level'),
            'owner_id' => $request->input('owner_id'),
            'search' => $request->input('search'),
        ];

        $requirements = $this->service->getPaginated($branchId, $filters, 15);
        $metrics = $this->service->getDashboardMetrics($branchId);

        return view('content.compliance-reporting.requirements.index', compact('requirements', 'metrics', 'filters'));
    }

    /**
     * Show the form for creating a new requirement
     */
    public function create(): View
    {
        return view('content.compliance-reporting.requirements.create');
    }

    /**
     * Store a newly created requirement
     */
    public function store(StoreComplianceRequirementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['branch_id'] = auth()->user()->branch_id;

        try {
            $requirement = $this->service->create($data);

            return redirect()->route('compliance-requirements.show', $requirement)
                ->with('success', 'Compliance requirement created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create compliance requirement: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified requirement
     */
    public function show(ComplianceRequirement $requirement): View
    {
        return view('content.compliance-reporting.requirements.show', compact('requirement'));
    }

    /**
     * Show the form for editing the specified requirement
     */
    public function edit(ComplianceRequirement $requirement): View
    {
        return view('content.compliance-reporting.requirements.edit', compact('requirement'));
    }

    /**
     * Update the specified requirement
     */
    public function update(UpdateComplianceRequirementRequest $request, ComplianceRequirement $requirement): RedirectResponse
    {
        $data = $request->validated();

        try {
            $requirement = $this->service->update($requirement, $data);

            return redirect()->route('compliance-requirements.show', $requirement)
                ->with('success', 'Compliance requirement updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update compliance requirement: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified requirement
     */
    public function destroy(ComplianceRequirement $requirement): RedirectResponse
    {
        try {
            $this->service->delete($requirement);

            return redirect()->route('compliance-requirements.index')
                ->with('success', 'Compliance requirement deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete compliance requirement: ' . $e->getMessage());
        }
    }
}
