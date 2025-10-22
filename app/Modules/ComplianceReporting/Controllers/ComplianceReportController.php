<?php

namespace App\Modules\ComplianceReporting\Controllers;

use App\Modules\ComplianceReporting\Models\ComplianceReport;
use App\Modules\ComplianceReporting\Services\ComplianceReportService;
use App\Modules\ComplianceReporting\Requests\StoreComplianceReportRequest;
use App\Modules\ComplianceReporting\Requests\UpdateComplianceReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceReportController
{
    protected ComplianceReportService $service;

    public function __construct(ComplianceReportService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of compliance reports
     */
    public function index(Request $request): View
    {
        $branchId = auth()->user()->branch_id;

        $filters = [
            'report_type' => $request->input('report_type'),
            'status' => $request->input('status'),
            'period' => $request->input('period'),
            'search' => $request->input('search'),
        ];

        $reports = $this->service->getPaginated($branchId, $filters, 15);

        return view('content.ComplianceReporting.Index', compact('reports', 'filters'));
    }

    /**
     * Show the form for creating a new report
     */
    public function create(): View
    {
        return view('content.compliance-reporting.reports.create');
    }

    /**
     * Store a newly created report
     */
    public function store(StoreComplianceReportRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $branchId = auth()->user()->branch_id;

        try {
            if ($request->boolean('auto_generate_metrics')) {
                $report = $this->service->generateReport($branchId, $data);
            } else {
                $data['branch_id'] = $branchId;
                $report = $this->service->create($data);
            }

            return redirect()->route('compliance-reports.show', $report)
                ->with('success', 'Compliance report created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create compliance report: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified report
     */
    public function show(ComplianceReport $report): View
    {
        return view('content.compliance-reporting.reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified report
     */
    public function edit(ComplianceReport $report): View
    {
        return view('content.compliance-reporting.reports.edit', compact('report'));
    }

    /**
     * Update the specified report
     */
    public function update(UpdateComplianceReportRequest $request, ComplianceReport $report): RedirectResponse
    {
        $data = $request->validated();

        try {
            $report = $this->service->update($report, $data);

            return redirect()->route('compliance-reports.show', $report)
                ->with('success', 'Compliance report updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update compliance report: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified report
     */
    public function destroy(ComplianceReport $report): RedirectResponse
    {
        try {
            $this->service->delete($report);

            return redirect()->route('compliance-reports.index')
                ->with('success', 'Compliance report deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete compliance report: ' . $e->getMessage());
        }
    }

    /**
     * Submit report for review
     */
    public function submitForReview(ComplianceReport $report): RedirectResponse
    {
        try {
            $this->service->submitForReview($report);

            return redirect()->route('compliance-reports.show', $report)
                ->with('success', 'Report submitted for review successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit report: ' . $e->getMessage());
        }
    }

    /**
     * Approve report
     */
    public function approve(ComplianceReport $report): RedirectResponse
    {
        try {
            $this->service->approve($report);

            return redirect()->route('compliance-reports.show', $report)
                ->with('success', 'Report approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve report: ' . $e->getMessage());
        }
    }

    /**
     * Publish report
     */
    public function publish(ComplianceReport $report): RedirectResponse
    {
        try {
            $this->service->publish($report);

            return redirect()->route('compliance-reports.show', $report)
                ->with('success', 'Report published successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to publish report: ' . $e->getMessage());
        }
    }

    /**
     * Download report file
     */
    public function download(ComplianceReport $report)
    {
        try {
            return $this->service->downloadFile($report);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download report: ' . $e->getMessage());
        }
    }
}
