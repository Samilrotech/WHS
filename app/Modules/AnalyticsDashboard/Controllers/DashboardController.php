<?php

namespace App\Modules\AnalyticsDashboard\Controllers;

use App\Modules\AnalyticsDashboard\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the main analytics dashboard
     */
    public function index(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($branchId, $period);

        return view('content.analytics-dashboard.index', array_merge(compact('metrics', 'period'), [
            'mobileNavActive' => 'dashboard',
        ]));
    }

    /**
     * Display detailed incident analytics
     */
    public function incidents(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($branchId, $period);
        $incidentMetrics = $metrics['incidents'];
        $trends = $metrics['trends'];

        return view('content.analytics-dashboard.incidents', compact('incidentMetrics', 'trends', 'period'));
    }

    /**
     * Display risk assessment analytics
     */
    public function risks(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($branchId, $period);
        $riskMetrics = $metrics['risks'];

        return view('content.analytics-dashboard.risks', compact('riskMetrics', 'period'));
    }

    /**
     * Display vehicle fleet analytics
     */
    public function vehicles(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($branchId, $period);
        $vehicleMetrics = $metrics['vehicles'];

        return view('content.analytics-dashboard.vehicles', compact('vehicleMetrics', 'period'));
    }

    /**
     * Display training analytics
     */
    public function training(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($branchId, $period);
        $trainingMetrics = $metrics['training'];

        return view('content.analytics-dashboard.training', compact('trainingMetrics', 'period'));
    }

    /**
     * Display compliance metrics
     */
    public function compliance(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($branchId, $period);
        $complianceMetrics = $metrics['compliance'];

        return view('content.analytics-dashboard.compliance', compact('complianceMetrics', 'period'));
    }

    /**
     * Display KPI dashboard
     */
    public function kpis(Request $request): View
    {
        $branchId = auth()->user()->branch_id;
        $period = $request->input('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($branchId, $period);
        $kpis = $metrics['kpis'];

        return view('content.analytics-dashboard.kpis', compact('kpis', 'period'));
    }
}
