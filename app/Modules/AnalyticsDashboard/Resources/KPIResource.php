<?php

namespace App\Modules\AnalyticsDashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Lost Time Injury Frequency Rate
            'ltifr' => [
                'value' => $this->resource['ltifr'] ?? 0,
                'label' => 'Lost Time Injury Frequency Rate',
                'description' => 'Number of lost-time injuries per million hours worked',
                'benchmark' => 2.0, // Industry benchmark
                'status' => $this->getKPIStatus($this->resource['ltifr'] ?? 0, 2.0),
                'trend' => $this->when(isset($this->resource['ltifr_trend']), $this->resource['ltifr_trend']),
            ],

            // Total Recordable Injury Frequency Rate
            'trifr' => [
                'value' => $this->resource['trifr'] ?? 0,
                'label' => 'Total Recordable Injury Frequency Rate',
                'description' => 'Number of recordable injuries per million hours worked',
                'benchmark' => 5.0, // Industry benchmark
                'status' => $this->getKPIStatus($this->resource['trifr'] ?? 0, 5.0),
                'trend' => $this->when(isset($this->resource['trifr_trend']), $this->resource['trifr_trend']),
            ],

            // Supporting metrics
            'total_incidents' => $this->resource['total_incidents'] ?? 0,
            'lost_time_injuries' => $this->resource['lost_time_injuries'] ?? 0,

            // Calculated ratios
            'lti_percentage' => $this->calculateLTIPercentage(),
        ];
    }

    /**
     * Calculate lost-time injury percentage
     */
    protected function calculateLTIPercentage(): float
    {
        $total = $this->resource['total_incidents'] ?? 0;
        if ($total === 0) {
            return 0;
        }

        $lti = $this->resource['lost_time_injuries'] ?? 0;
        return round(($lti / $total) * 100, 2);
    }

    /**
     * Determine KPI status compared to benchmark
     */
    protected function getKPIStatus(float $value, float $benchmark): string
    {
        if ($value === 0) {
            return 'excellent';
        }

        $ratio = $value / $benchmark;

        if ($ratio <= 0.5) {
            return 'excellent'; // 50% or better than benchmark
        } elseif ($ratio <= 0.8) {
            return 'good'; // 80% or better than benchmark
        } elseif ($ratio <= 1.0) {
            return 'acceptable'; // At or slightly above benchmark
        } elseif ($ratio <= 1.5) {
            return 'concerning'; // 150% of benchmark
        } else {
            return 'critical'; // Significantly above benchmark
        }
    }
}
