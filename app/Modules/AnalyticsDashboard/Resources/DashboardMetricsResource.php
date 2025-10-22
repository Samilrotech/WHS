<?php

namespace App\Modules\AnalyticsDashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardMetricsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'incidents' => [
                'total' => $this->resource['incidents']['total'] ?? 0,
                'by_severity' => $this->resource['incidents']['by_severity'] ?? [],
                'by_type' => $this->resource['incidents']['by_type'] ?? [],
                'resolved' => $this->resource['incidents']['resolved'] ?? 0,
                'resolution_rate' => $this->resource['incidents']['resolution_rate'] ?? 0,
            ],
            'risks' => [
                'total' => $this->resource['risks']['total'] ?? 0,
                'by_level' => $this->resource['risks']['by_level'] ?? [],
                'high_risks' => $this->resource['risks']['high_risks'] ?? 0,
                'assessments_completed' => $this->resource['risks']['assessments_completed'] ?? 0,
            ],
            'vehicles' => [
                'total_vehicles' => $this->resource['vehicles']['total_vehicles'] ?? 0,
                'inspections_due' => $this->resource['vehicles']['inspections_due'] ?? 0,
                'maintenance_due' => $this->resource['vehicles']['maintenance_due'] ?? 0,
                'inspections_completed' => $this->resource['vehicles']['inspections_completed'] ?? 0,
            ],
            'training' => [
                'total_sessions' => $this->resource['training']['total_sessions'] ?? 0,
                'total_attendees' => $this->resource['training']['total_attendees'] ?? 0,
                'certifications_expiring' => $this->resource['training']['certifications_expiring'] ?? 0,
                'completion_rate' => $this->resource['training']['completion_rate'] ?? 0,
            ],
            'compliance' => [
                'total_requirements' => $this->resource['compliance']['total_requirements'] ?? 0,
                'compliant' => $this->resource['compliance']['compliant'] ?? 0,
                'non_compliant' => $this->resource['compliance']['non_compliant'] ?? 0,
                'compliance_rate' => $this->resource['compliance']['compliance_rate'] ?? 0,
            ],
            'trends' => [
                'incidents_over_time' => $this->resource['trends']['incidents_over_time'] ?? [],
            ],
            'kpis' => [
                'ltifr' => $this->resource['kpis']['ltifr'] ?? 0,
                'trifr' => $this->resource['kpis']['trifr'] ?? 0,
                'total_incidents' => $this->resource['kpis']['total_incidents'] ?? 0,
                'lost_time_injuries' => $this->resource['kpis']['lost_time_injuries'] ?? 0,
            ],
        ];
    }
}
