<?php

namespace App\Modules\VehicleReportingDashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleDashboardMetricsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'fleet_overview' => [
                'total_vehicles' => $this->resource['fleet_overview']['total_vehicles'] ?? 0,
                'active' => $this->resource['fleet_overview']['active'] ?? 0,
                'in_maintenance' => $this->resource['fleet_overview']['in_maintenance'] ?? 0,
                'out_of_service' => $this->resource['fleet_overview']['out_of_service'] ?? 0,
                'by_type' => $this->resource['fleet_overview']['by_type'] ?? [],
                'availability_rate' => $this->resource['fleet_overview']['availability_rate'] ?? 0,
            ],
            'inspection_metrics' => [
                'due_within_7_days' => $this->resource['inspection_metrics']['due_within_7_days'] ?? 0,
                'overdue' => $this->resource['inspection_metrics']['overdue'] ?? 0,
                'completed' => $this->resource['inspection_metrics']['completed'] ?? 0,
                'passed' => $this->resource['inspection_metrics']['passed'] ?? 0,
                'failed' => $this->resource['inspection_metrics']['failed'] ?? 0,
                'pass_rate' => $this->resource['inspection_metrics']['pass_rate'] ?? 0,
                'defects_found' => $this->resource['inspection_metrics']['defects_found'] ?? 0,
            ],
            'maintenance_metrics' => [
                'due_within_14_days' => $this->resource['maintenance_metrics']['due_within_14_days'] ?? 0,
                'overdue' => $this->resource['maintenance_metrics']['overdue'] ?? 0,
                'completed' => $this->resource['maintenance_metrics']['completed'] ?? 0,
                'preventive' => $this->resource['maintenance_metrics']['preventive'] ?? 0,
                'corrective' => $this->resource['maintenance_metrics']['corrective'] ?? 0,
                'preventive_ratio' => $this->resource['maintenance_metrics']['preventive_ratio'] ?? 0,
            ],
            'cost_analysis' => [
                'total_costs' => $this->resource['cost_analysis']['total_costs'] ?? 0,
                'maintenance_costs' => $this->resource['cost_analysis']['maintenance_costs'] ?? 0,
                'fuel_costs' => $this->resource['cost_analysis']['fuel_costs'] ?? 0,
                'cost_per_vehicle' => $this->resource['cost_analysis']['cost_per_vehicle'] ?? 0,
                'top_cost_vehicles' => $this->resource['cost_analysis']['top_cost_vehicles'] ?? [],
            ],
            'utilization' => [
                'total_distance_km' => $this->resource['utilization']['total_distance_km'] ?? 0,
                'total_hours' => $this->resource['utilization']['total_hours'] ?? 0,
                'avg_distance_per_vehicle' => $this->resource['utilization']['avg_distance_per_vehicle'] ?? 0,
                'avg_hours_per_vehicle' => $this->resource['utilization']['avg_hours_per_vehicle'] ?? 0,
            ],
            'compliance' => [
                'total_vehicles' => $this->resource['compliance']['total_vehicles'] ?? 0,
                'compliant' => $this->resource['compliance']['compliant'] ?? 0,
                'registration_expiring_soon' => $this->resource['compliance']['registration_expiring_soon'] ?? 0,
                'registration_expired' => $this->resource['compliance']['registration_expired'] ?? 0,
                'insurance_expiring_soon' => $this->resource['compliance']['insurance_expiring_soon'] ?? 0,
                'insurance_expired' => $this->resource['compliance']['insurance_expired'] ?? 0,
                'compliance_rate' => $this->resource['compliance']['compliance_rate'] ?? 0,
            ],
            'trends' => [
                'maintenance_over_time' => $this->resource['trends']['maintenance_over_time'] ?? [],
                'inspections_over_time' => $this->resource['trends']['inspections_over_time'] ?? [],
            ],
        ];
    }
}
