<?php

namespace App\Modules\AnalyticsDashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentMetricsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total' => $this->resource['total'] ?? 0,
            'by_severity' => [
                'critical' => $this->resource['by_severity']['critical'] ?? 0,
                'high' => $this->resource['by_severity']['high'] ?? 0,
                'medium' => $this->resource['by_severity']['medium'] ?? 0,
                'low' => $this->resource['by_severity']['low'] ?? 0,
            ],
            'by_type' => $this->resource['by_type'] ?? [],
            'resolved' => $this->resource['resolved'] ?? 0,
            'resolution_rate' => $this->resource['resolution_rate'] ?? 0,

            // Additional computed metrics
            'critical_percentage' => $this->calculatePercentage('critical'),
            'high_percentage' => $this->calculatePercentage('high'),
            'medium_percentage' => $this->calculatePercentage('medium'),
            'low_percentage' => $this->calculatePercentage('low'),
            'unresolved' => ($this->resource['total'] ?? 0) - ($this->resource['resolved'] ?? 0),
        ];
    }

    /**
     * Calculate percentage for a severity level
     */
    protected function calculatePercentage(string $severity): float
    {
        $total = $this->resource['total'] ?? 0;
        if ($total === 0) {
            return 0;
        }

        $count = $this->resource['by_severity'][$severity] ?? 0;
        return round(($count / $total) * 100, 2);
    }
}
