<?php

namespace App\Modules\VehicleReportingDashboard\Controllers;

use App\Modules\VehicleReportingDashboard\Services\VehicleReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleReportingController
{
    protected VehicleReportingService $reportingService;

    public function __construct(VehicleReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Display the main vehicle reporting dashboard
     */
    public function index(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->reportingService->getDashboardMetrics($branchId, $period);

        return view('content.VehicleReportingDashboard.Index', compact('metrics', 'period'));
    }

    /**
     * Display fleet overview
     */
    public function fleet(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->reportingService->getDashboardMetrics($branchId, $period);
        $fleetMetrics = $metrics['fleet_overview'];
        $utilization = $metrics['utilization'];

        return view('content.VehicleReportingDashboard.Fleet', compact('fleetMetrics', 'utilization', 'period'));
    }

    /**
     * Display inspection analytics
     */
    public function inspections(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->reportingService->getDashboardMetrics($branchId, $period);
        $inspectionMetrics = $metrics['inspection_metrics'];
        $trends = $metrics['trends'];

        return view('content.VehicleReportingDashboard.Inspections', compact('inspectionMetrics', 'trends', 'period'));
    }

    /**
     * Display maintenance analytics
     */
    public function maintenance(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->reportingService->getDashboardMetrics($branchId, $period);
        $maintenanceMetrics = $metrics['maintenance_metrics'];
        $trends = $metrics['trends'];

        return view('content.VehicleReportingDashboard.Maintenance', compact('maintenanceMetrics', 'trends', 'period'));
    }

    /**
     * Display cost analysis
     */
    public function costs(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->reportingService->getDashboardMetrics($branchId, $period);
        $costMetrics = $metrics['cost_analysis'];

        return view('content.VehicleReportingDashboard.Costs', compact('costMetrics', 'period'));
    }

    /**
     * Display compliance metrics
     */
    public function compliance(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->reportingService->getDashboardMetrics($branchId, $period);
        $complianceMetrics = $metrics['compliance'];

        return view('content.VehicleReportingDashboard.Compliance', compact('complianceMetrics', 'period'));
    }
}
