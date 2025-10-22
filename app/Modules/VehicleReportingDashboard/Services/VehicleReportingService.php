<?php

namespace App\Modules\VehicleReportingDashboard\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehicleReportingService
{
    /**
     * Check if a table exists in the database
     */
    protected function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * Get comprehensive vehicle dashboard metrics for a branch
     */
    public function getDashboardMetrics(string $branchId, string $period = 'month'): array
    {
        $dates = $this->getPeriodDates($period);

        return [
            'fleet_overview' => $this->getFleetOverview($branchId),
            'inspection_metrics' => $this->getInspectionMetrics($branchId, $dates),
            'maintenance_metrics' => $this->getMaintenanceMetrics($branchId, $dates),
            'cost_analysis' => $this->getCostAnalysis($branchId, $dates),
            'utilization' => $this->getUtilizationMetrics($branchId, $dates),
            'compliance' => $this->getComplianceMetrics($branchId),
            'trends' => $this->getTrendData($branchId, $dates),
        ];
    }

    /**
     * Get fleet overview statistics
     */
    protected function getFleetOverview(string $branchId): array
    {
        $totalVehicles = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $activeVehicles = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->count();

        $inMaintenance = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('status', 'maintenance')
            ->whereNull('deleted_at')
            ->count();

        $outOfService = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('status', 'out-of-service')
            ->whereNull('deleted_at')
            ->count();

        // Note: 'type' column removed - not present in vehicles table schema
        // Can be added later if vehicle type categorization is needed

        return [
            'total_vehicles' => $totalVehicles,
            'active' => $activeVehicles,
            'in_maintenance' => $inMaintenance,
            'out_of_service' => $outOfService,
            'availability_rate' => $totalVehicles > 0 ? round(($activeVehicles / $totalVehicles) * 100, 2) : 0,
        ];
    }

    /**
     * Get inspection metrics
     */
    protected function getInspectionMetrics(string $branchId, array $dates): array
    {
        // Check if vehicle_inspections table exists
        if (!$this->tableExists('vehicle_inspections')) {
            // Return safe defaults - only vehicle-based data
            $inspectionsDue = DB::table('vehicles')
                ->where('branch_id', $branchId)
                ->where('inspection_due_date', '<=', now()->addDays(7))
                ->whereNull('deleted_at')
                ->count();

            $inspectionsOverdue = DB::table('vehicles')
                ->where('branch_id', $branchId)
                ->where('inspection_due_date', '<', now())
                ->whereNull('deleted_at')
                ->count();

            return [
                'due_within_7_days' => $inspectionsDue,
                'overdue' => $inspectionsOverdue,
                'completed' => 0,
                'passed' => 0,
                'failed' => 0,
                'pass_rate' => 0,
                'defects_found' => 0,
            ];
        }

        $inspectionsDue = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('inspection_due_date', '<=', now()->addDays(7))
            ->whereNull('deleted_at')
            ->count();

        $inspectionsOverdue = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('inspection_due_date', '<', now())
            ->whereNull('deleted_at')
            ->count();

        $inspectionsCompleted = DB::table('vehicle_inspections')
            ->where('branch_id', $branchId)
            ->whereBetween('inspection_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        $inspectionsPassed = DB::table('vehicle_inspections')
            ->where('branch_id', $branchId)
            ->where('result', 'pass')
            ->whereBetween('inspection_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        $inspectionsFailed = DB::table('vehicle_inspections')
            ->where('branch_id', $branchId)
            ->where('result', 'fail')
            ->whereBetween('inspection_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count();

        $defectsFound = DB::table('vehicle_inspections')
            ->where('branch_id', $branchId)
            ->whereBetween('inspection_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->sum('defects_count');

        return [
            'due_within_7_days' => $inspectionsDue,
            'overdue' => $inspectionsOverdue,
            'completed' => $inspectionsCompleted,
            'passed' => $inspectionsPassed,
            'failed' => $inspectionsFailed,
            'pass_rate' => $inspectionsCompleted > 0 ? round(($inspectionsPassed / $inspectionsCompleted) * 100, 2) : 0,
            'defects_found' => $defectsFound,
        ];
    }

    /**
     * Get maintenance metrics
     */
    protected function getMaintenanceMetrics(string $branchId, array $dates): array
    {
        // Check if vehicle_maintenance table exists
        $hasMaintenanceTable = $this->tableExists('vehicle_maintenance');
        $hasSchedulesTable = $this->tableExists('maintenance_schedules');

        $maintenanceDue = $hasSchedulesTable ? DB::table('maintenance_schedules')
            ->where('branch_id', $branchId)
            ->where('next_due_date', '<=', now()->addDays(14))
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->count() : 0;

        $maintenanceOverdue = $hasSchedulesTable ? DB::table('maintenance_schedules')
            ->where('branch_id', $branchId)
            ->where('next_due_date', '<', now())
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->count() : 0;

        $maintenanceCompleted = $hasMaintenanceTable ? DB::table('vehicle_maintenance')
            ->where('branch_id', $branchId)
            ->whereBetween('service_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count() : 0;

        $preventiveMaintenance = $hasMaintenanceTable ? DB::table('vehicle_maintenance')
            ->where('branch_id', $branchId)
            ->where('maintenance_type', 'preventive')
            ->whereBetween('service_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count() : 0;

        $correctiveMaintenance = $hasMaintenanceTable ? DB::table('vehicle_maintenance')
            ->where('branch_id', $branchId)
            ->where('maintenance_type', 'corrective')
            ->whereBetween('service_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->count() : 0;

        return [
            'due_within_14_days' => $maintenanceDue,
            'overdue' => $maintenanceOverdue,
            'completed' => $maintenanceCompleted,
            'preventive' => $preventiveMaintenance,
            'corrective' => $correctiveMaintenance,
            'preventive_ratio' => $maintenanceCompleted > 0 ? round(($preventiveMaintenance / $maintenanceCompleted) * 100, 2) : 0,
        ];
    }

    /**
     * Get cost analysis
     */
    protected function getCostAnalysis(string $branchId, array $dates): array
    {
        // Check if tables exist
        $hasMaintenanceTable = $this->tableExists('vehicle_maintenance');
        $hasFuelLogsTable = $this->tableExists('fuel_logs');

        $maintenanceCosts = $hasMaintenanceTable ? DB::table('vehicle_maintenance')
            ->where('branch_id', $branchId)
            ->whereBetween('service_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->sum('cost') : 0;

        $fuelCosts = $hasFuelLogsTable ? DB::table('fuel_logs')
            ->where('branch_id', $branchId)
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->sum('cost') : 0;

        $costByVehicle = $hasMaintenanceTable ? DB::table('vehicle_maintenance')
            ->select('vehicle_id', DB::raw('sum(cost) as total_cost'))
            ->where('branch_id', $branchId)
            ->whereBetween('service_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->groupBy('vehicle_id')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get()
            ->toArray() : [];

        $totalVehicles = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $totalCosts = $maintenanceCosts + $fuelCosts;
        $costPerVehicle = $totalVehicles > 0 ? round($totalCosts / $totalVehicles, 2) : 0;

        return [
            'total_costs' => $totalCosts,
            'maintenance_costs' => $maintenanceCosts,
            'fuel_costs' => $fuelCosts,
            'cost_per_vehicle' => $costPerVehicle,
            'top_cost_vehicles' => $costByVehicle,
        ];
    }

    /**
     * Get utilization metrics
     */
    protected function getUtilizationMetrics(string $branchId, array $dates): array
    {
        // Check if journey_logs table exists
        $hasJourneyLogsTable = $this->tableExists('journey_logs');

        $totalDistance = $hasJourneyLogsTable ? DB::table('journey_logs')
            ->where('branch_id', $branchId)
            ->whereBetween('start_time', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->sum('distance_km') : 0;

        $totalHours = $hasJourneyLogsTable ? DB::table('journey_logs')
            ->where('branch_id', $branchId)
            ->whereBetween('start_time', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->sum('duration_hours') : 0;

        $totalVehicles = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $avgDistancePerVehicle = $totalVehicles > 0 ? round($totalDistance / $totalVehicles, 2) : 0;
        $avgHoursPerVehicle = $totalVehicles > 0 ? round($totalHours / $totalVehicles, 2) : 0;

        return [
            'total_distance_km' => $totalDistance,
            'total_hours' => $totalHours,
            'avg_distance_per_vehicle' => $avgDistancePerVehicle,
            'avg_hours_per_vehicle' => $avgHoursPerVehicle,
        ];
    }

    /**
     * Get compliance metrics
     */
    protected function getComplianceMetrics(string $branchId): array
    {
        $totalVehicles = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->count();

        $registrationExpiringSoon = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('rego_expiry_date', '<=', now()->addDays(30))
            ->where('rego_expiry_date', '>=', now())
            ->whereNull('deleted_at')
            ->count();

        $registrationExpired = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('rego_expiry_date', '<', now())
            ->whereNull('deleted_at')
            ->count();

        $insuranceExpiringSoon = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('insurance_expiry_date', '<=', now()->addDays(30))
            ->where('insurance_expiry_date', '>=', now())
            ->whereNull('deleted_at')
            ->count();

        $insuranceExpired = DB::table('vehicles')
            ->where('branch_id', $branchId)
            ->where('insurance_expiry_date', '<', now())
            ->whereNull('deleted_at')
            ->count();

        $compliantVehicles = $totalVehicles - ($registrationExpired + $insuranceExpired);
        $complianceRate = $totalVehicles > 0 ? round(($compliantVehicles / $totalVehicles) * 100, 2) : 0;

        return [
            'total_vehicles' => $totalVehicles,
            'compliant' => $compliantVehicles,
            'registration_expiring_soon' => $registrationExpiringSoon,
            'registration_expired' => $registrationExpired,
            'insurance_expiring_soon' => $insuranceExpiringSoon,
            'insurance_expired' => $insuranceExpired,
            'compliance_rate' => $complianceRate,
        ];
    }

    /**
     * Get trend data for charts
     */
    protected function getTrendData(string $branchId, array $dates): array
    {
        // Check if tables exist
        $hasMaintenanceTable = $this->tableExists('vehicle_maintenance');
        $hasInspectionsTable = $this->tableExists('vehicle_inspections');

        $maintenanceTrend = $hasMaintenanceTable ? DB::table('vehicle_maintenance')
            ->select(DB::raw('DATE(service_date) as date'), DB::raw('count(*) as count'), DB::raw('sum(cost) as cost'))
            ->where('branch_id', $branchId)
            ->whereBetween('service_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray() : [];

        $inspectionTrend = $hasInspectionsTable ? DB::table('vehicle_inspections')
            ->select(DB::raw('DATE(inspection_date) as date'), DB::raw('count(*) as count'))
            ->where('branch_id', $branchId)
            ->whereBetween('inspection_date', [$dates['start'], $dates['end']])
            ->whereNull('deleted_at')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray() : [];

        return [
            'maintenance_over_time' => $maintenanceTrend,
            'inspections_over_time' => $inspectionTrend,
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
