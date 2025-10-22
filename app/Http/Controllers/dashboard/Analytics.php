<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Modules\IncidentManagement\Models\Incident;
use App\Modules\InspectionManagement\Models\Inspection;
use App\Modules\RiskAssessment\Models\RiskAssessment;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Analytics extends Controller
{
  public function index()
  {
    $user = auth()->user();
    $branchId = $user->branch_id;

    // Overview Statistics with Trends
    $currentMonthStart = now()->startOfMonth();
    $lastMonthStart = now()->subMonth()->startOfMonth();
    $lastMonthEnd = now()->subMonth()->endOfMonth();

    // Current month stats
    $stats = [
      'total_incidents' => Incident::count(),
      'open_incidents' => Incident::where('status', 'open')->count(),
      'high_risk_assessments' => RiskAssessment::highRisk()->count(),
      'active_emergency_alerts' => EmergencyAlert::where('status', 'active')->count(),
      'vehicles_total' => Vehicle::count(),
      'vehicles_active' => Vehicle::where('status', 'active')->count(),
      'inspections_pending' => Inspection::where('status', 'pending')->count(),
      'inspections_overdue' => Inspection::overdue()->count(),
    ];

    // Calculate month-over-month trends
    $lastMonthIncidents = Incident::whereBetween('incident_datetime', [$lastMonthStart, $lastMonthEnd])->count();
    $currentMonthIncidents = Incident::where('incident_datetime', '>=', $currentMonthStart)->count();
    $stats['incidents_trend'] = $lastMonthIncidents > 0
      ? round((($currentMonthIncidents - $lastMonthIncidents) / $lastMonthIncidents) * 100, 1)
      : 0;

    $lastMonthRisks = RiskAssessment::whereBetween('assessment_date', [$lastMonthStart, $lastMonthEnd])->count();
    $currentMonthRisks = RiskAssessment::where('assessment_date', '>=', $currentMonthStart)->count();
    $stats['risks_trend'] = $lastMonthRisks > 0
      ? round((($currentMonthRisks - $lastMonthRisks) / $lastMonthRisks) * 100, 1)
      : 0;

    $lastMonthActiveVehicles = Vehicle::where('status', 'active')
      ->where('updated_at', '<=', $lastMonthEnd)
      ->count();
    $stats['vehicles_trend'] = $lastMonthActiveVehicles > 0
      ? round((($stats['vehicles_active'] - $lastMonthActiveVehicles) / $lastMonthActiveVehicles) * 100, 1)
      : 0;

    $lastMonthPendingInspections = Inspection::where('status', 'pending')
      ->where('created_at', '<=', $lastMonthEnd)
      ->count();
    $stats['inspections_trend'] = $lastMonthPendingInspections > 0
      ? round((($stats['inspections_pending'] - $lastMonthPendingInspections) / $lastMonthPendingInspections) * 100, 1)
      : 0;

    // Monthly Incident Trend (Last 6 months)
    $monthlyIncidents = Incident::select(
      DB::raw('DATE_FORMAT(incident_datetime, "%Y-%m") as month'),
      DB::raw('count(*) as count')
    )
    ->where('incident_datetime', '>=', now()->subMonths(6))
    ->groupBy('month')
    ->orderBy('month', 'asc')
    ->get();

    // Incidents by Severity
    $incidentsBySeverity = Incident::select('severity', DB::raw('count(*) as count'))
      ->groupBy('severity')
      ->get()
      ->pluck('count', 'severity');

    // Risk Assessment Distribution
    $riskDistribution = [
      'low' => RiskAssessment::where('residual_risk_level', 'green')->count(),
      'medium' => RiskAssessment::where('residual_risk_level', 'yellow')->count(),
      'high' => RiskAssessment::where('residual_risk_level', 'orange')->count(),
      'critical' => RiskAssessment::where('residual_risk_level', 'red')->count(),
    ];

    // Recent Incidents (Last 5)
    $recentIncidents = Incident::with(['user', 'branch'])
      ->latest('incident_datetime')
      ->take(5)
      ->get();

    // Vehicle Status Distribution
    $vehicleStatus = Vehicle::select('status', DB::raw('count(*) as count'))
      ->groupBy('status')
      ->get()
      ->pluck('count', 'status');

    return view('content.dashboard.dashboards-analytics', compact(
      'stats',
      'monthlyIncidents',
      'incidentsBySeverity',
      'riskDistribution',
      'recentIncidents',
      'vehicleStatus'
    ));
  }
}
