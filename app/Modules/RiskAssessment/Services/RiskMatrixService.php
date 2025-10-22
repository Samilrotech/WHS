<?php

namespace App\Modules\RiskAssessment\Services;

use App\Modules\RiskAssessment\Models\RiskAssessment;

class RiskMatrixService
{
    /**
     * Calculate risk score and level from likelihood and consequence
     *
     * @param int $likelihood 1-5
     * @param int $consequence 1-5
     * @return array ['score' => int, 'level' => string, 'likelihood' => int, 'consequence' => int]
     */
    public function calculateRiskScore(int $likelihood, int $consequence): array
    {
        $score = $likelihood * $consequence;

        $level = match (true) {
            $score <= 5 => 'green',
            $score <= 11 => 'yellow',
            $score <= 19 => 'orange',
            default => 'red',
        };

        return [
            'score' => $score,
            'level' => $level,
            'likelihood' => $likelihood,
            'consequence' => $consequence,
        ];
    }

    /**
     * Get risk matrix data with counts for each cell (5x5 matrix)
     *
     * @param string $branchId
     * @return array Array of 25 cells with risk data
     */
    public function getMatrixData(string $branchId): array
    {
        $matrix = [];

        for ($likelihood = 1; $likelihood <= 5; $likelihood++) {
            for ($consequence = 1; $consequence <= 5; $consequence++) {
                $riskCalc = $this->calculateRiskScore($likelihood, $consequence);

                $risks = RiskAssessment::where('branch_id', $branchId)
                    ->where('residual_likelihood', $likelihood)
                    ->where('residual_consequence', $consequence)
                    ->with(['hazards', 'user'])
                    ->get();

                $matrix[] = [
                    'likelihood' => $likelihood,
                    'consequence' => $consequence,
                    'score' => $riskCalc['score'],
                    'level' => $riskCalc['level'],
                    'count' => $risks->count(),
                    'risks' => $risks,
                ];
            }
        }

        return $matrix;
    }

    /**
     * Get color class for risk level
     *
     * @param string $level green|yellow|orange|red
     * @return string Tailwind CSS classes
     */
    public function getColorClass(string $level): string
    {
        return match ($level) {
            'green' => 'bg-green-500 hover:bg-green-600 text-white',
            'yellow' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
            'orange' => 'bg-orange-500 hover:bg-orange-600 text-white',
            'red' => 'bg-red-500 hover:bg-red-600 text-white',
            default => 'bg-gray-500 hover:bg-gray-600 text-white',
        };
    }

    /**
     * Get text color class for risk level (for badges)
     *
     * @param string $level green|yellow|orange|red
     * @return string Tailwind CSS classes
     */
    public function getBadgeClass(string $level): string
    {
        return match ($level) {
            'green' => 'bg-green-100 text-green-800 border-green-200',
            'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'orange' => 'bg-orange-100 text-orange-800 border-orange-200',
            'red' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * Get statistics for risk assessments by risk level
     *
     * @param string $branchId
     * @return array
     */
    public function getStatistics(string $branchId): array
    {
        return [
            'total' => RiskAssessment::where('branch_id', $branchId)->count(),
            'green' => RiskAssessment::where('branch_id', $branchId)->where('residual_risk_level', 'green')->count(),
            'yellow' => RiskAssessment::where('branch_id', $branchId)->where('residual_risk_level', 'yellow')->count(),
            'orange' => RiskAssessment::where('branch_id', $branchId)->where('residual_risk_level', 'orange')->count(),
            'red' => RiskAssessment::where('branch_id', $branchId)->where('residual_risk_level', 'red')->count(),
            'high_risk' => RiskAssessment::where('branch_id', $branchId)
                ->whereIn('residual_risk_level', ['orange', 'red'])
                ->count(),
        ];
    }

    /**
     * Check if risk level requires approval
     *
     * @param string $level
     * @return bool
     */
    public function requiresApproval(string $level): bool
    {
        return in_array($level, ['orange', 'red']);
    }
}
