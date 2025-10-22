<?php

namespace App\Modules\AnalyticsDashboard\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get comprehensive dashboard metrics for a branch
     */
    public function getDashboardMetrics(string $branchId, string $period = 'month'): array
    {
        $dates = $this->getPeriodDates($period);

        return [
            'incidents' => $this->getIncidentMetrics($branchId, $dates),
            'risks' => $this->getRiskMetrics($branchId, $dates),
            'vehicles' => $this->getVehicleMetrics($branchId, $dates),
            'training' => $this->getTrainingMetrics($branchId, $dates),
            'compliance' => $this->getComplianceMetrics($branchId, $dates),
            'trends' => $this->getTrendData($branchId, $dates),
            'kpis' => $this->getKPIs($branchId, $dates),
        ];
    }

    /**
     * Get incident metrics
     */
    protected function getIncidentMetrics(string $branchId, array $dates): array
    {
        $total = DB::table('incidents')
            ->where('branch_id', $branchId)
            ->whereBetween('incident_datetime', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        $bySeverity = DB::table('incidents')
            ->select('severity', DB::raw('count(*) as count'))
            ->where('branch_id', $branchId)
            ->whereBetween('incident_datetime', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        $byType = DB::table('incidents')
            ->select('type', DB::raw('count(*) as count'))
            ->where('branch_id', $branchId)
            ->whereBetween('incident_datetime', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $resolved = DB::table('incidents')
            ->where('branch_id', $branchId)
            ->where('status', 'resolved')
            ->whereBetween('incident_datetime', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        return [
            'total' => $total,
            'by_severity' => $bySeverity,
            'by_type' => $byType,
            'resolved' => $resolved,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get risk assessment metrics
     */
    protected function getRiskMetrics(string $branchId, array $dates): array
    {
        $total = DB::table('risk_assessments')
            ->where('branch_id', $branchId)
            ->whereBetween('assessment_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        $byLevel = DB::table('risk_assessments')
            ->select('risk_level', DB::raw('count(*) as count'))
            ->where('branch_id', $branchId)
            ->whereBetween('assessment_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level')
            ->toArray();

        $highRisks = DB::table('risk_assessments')
            ->where('branch_id', $branchId)
            ->where('risk_score', '>=', 15) // High/Critical risk threshold
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->count();

        return [
            'total' => $total,
            'by_level' => $byLevel,
            'high_risks' => $highRisks,
            'assessments_completed' => $total,
        ];
    }

    /**
     * Get vehicle metrics
     */
    protected function getVehicleMetrics(string $branchId, array $dates): array
    {
        $totalVehicles = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $inspectionsDue = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('next_inspection_date', '<=', now()->addDays(7))
            ->whereNull('deleted_at')
            ->count();

        $maintenanceDue = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('next_service_date', '<=', now()->addDays(14))
            ->whereNull('deleted_at')
            ->count();

        $inspectionsCompleted = DB::table('vehicle_inspections')
            ->where('branch_id', $branchId)
            ->whereBetween('inspection_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        return [
            'total_vehicles' => $totalVehicles,
            'inspections_due' => $inspectionsDue,
            'maintenance_due' => $maintenanceDue,
            'inspections_completed' => $inspectionsCompleted,
        ];
    }

    /**
     * Get training metrics
     */
    protected function getTrainingMetrics(string $branchId, array $dates): array
    {
        $totalSessions = DB::table('training_sessions')
            ->where('branch_id', $branchId)
            ->whereBetween('session_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        $attendees = DB::table('training_attendees')
            ->join('training_sessions', 'training_attendees.session_id', '=', 'training_sessions.id')
            ->where('training_sessions.branch_id', $branchId)
            ->whereBetween('training_sessions.session_date', [$dates['start'], $dates['end']])
            ->count();

        $certificationsExpiring = DB::table('user_certifications')
            ->join('users', 'user_certifications.user_id', '=', 'users.id')
            ->where('users.branch_id', $branchId)
            ->where('user_certifications.expiry_date', '<=', now()->addDays(30))
            ->whereNull('user_certifications.deleted_at')
            ->count();

        return [
            'total_sessions' => $totalSessions,
            'total_attendees' => $attendees,
            'certifications_expiring' => $certificationsExpiring,
            'completion_rate' => $totalSessions > 0 ? round(($attendees / ($totalSessions * 10)) * 100, 2) : 0,
        ];
    }

    /**
     * Get compliance metrics
     */
    protected function getComplianceMetrics(string $branchId, array $dates): array
    {
        $totalRequirements = DB::table('compliance_requirements')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $compliant = DB::table('compliance_requirements')
            ->where('branch_id', $branchId)
            ->where('status', 'compliant')
            ->whereNull('deleted_at')
            ->count();

        $nonCompliant = DB::table('compliance_requirements')
            ->where('branch_id', $branchId)
            ->where('status', 'non-compliant')
            ->whereNull('deleted_at')
            ->count();

        return [
            'total_requirements' => $totalRequirements,
            'compliant' => $compliant,
            'non_compliant' => $nonCompliant,
            'compliance_rate' => $totalRequirements > 0 ? round(($compliant / $totalRequirements) * 100, 2) : 0,
        ];
    }

    /**
     * Get trend data for charts
     */
    protected function getTrendData(string $branchId, array $dates): array
    {
        $incidentTrend = DB::table('incidents')
            ->select(DB::raw('DATE(incident_datetime) as date'), DB::raw('count(*) as count'))
            ->where('branch_id', $branchId)
            ->whereBetween('incident_datetime', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return [
            'incidents_over_time' => $incidentTrend,
        ];
    }

    /**
     * Get Key Performance Indicators
     */
    protected function getKPIs(string $branchId, array $dates): array
    {
        // Lost Time Injury Frequency Rate (LTIFR)
        $ltiCount = DB::table('incidents')
            ->where('branch_id', $branchId)
            ->where('type', 'injury')
            ->where('lost_time_days', '>', 0)
            ->whereBetween('incident_datetime', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        // Total Recordable Injury Frequency Rate (TRIFR)
        $triCount = DB::table('incidents')
            ->where('branch_id', $branchId)
            ->whereIn('type', ['injury', 'near-miss'])
            ->whereBetween('incident_datetime', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        // Assume 200,000 hours as standard work hours for calculation
        $workHours = 200000;

        return [
            'ltifr' => round(($ltiCount / $workHours) * 1000000, 2),
            'trifr' => round(($triCount / $workHours) * 1000000, 2),
            'total_incidents' => $triCount,
            'lost_time_injuries' => $ltiCount,
        ];
    }

    /**
     * Get period dates based on period type
     */
    protected function getPeriodDates(string $period): array
    {
        return match($period) {
            'day' => [
                'start' => Carbon::today(),
                'end' => Carbon::today()->endOfDay(),
            ],
            'week' => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek(),
            ],
            'month' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
            ],
            'quarter' => [
                'start' => Carbon::now()->startOfQuarter(),
                'end' => Carbon::now()->endOfQuarter(),
            ],
            'year' => [
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now()->endOfYear(),
            ],
            default => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
            ],
        };
    }
}
