<?php

namespace App\Modules\VehicleManagement\Services;

use App\Modules\VehicleManagement\Models\Vehicle;
use Carbon\Carbon;

class DepreciationService
{
    /**
     * Calculate current depreciated value of a vehicle
     */
    public function calculateCurrentValue(Vehicle $vehicle): float
    {
        if (!$vehicle->purchase_price || !$vehicle->purchase_date) {
            return 0;
        }

        $yearsOwned = $this->getYearsOwned($vehicle);

        return match ($vehicle->depreciation_method) {
            'straight_line' => $this->calculateStraightLine($vehicle, $yearsOwned),
            'declining_balance' => $this->calculateDecliningBalance($vehicle, $yearsOwned),
            default => $vehicle->purchase_price,
        };
    }

    /**
     * Calculate straight-line depreciation
     * Formula: Value = Purchase Price - (Purchase Price * Rate * Years)
     */
    private function calculateStraightLine(Vehicle $vehicle, float $years): float
    {
        $rate = $vehicle->depreciation_rate ?? 20; // Default 20% per year
        $rateDecimal = $rate / 100;

        $depreciation = $vehicle->purchase_price * $rateDecimal * $years;
        $currentValue = $vehicle->purchase_price - $depreciation;

        // Ensure minimum value of 10% original purchase price
        $minimumValue = $vehicle->purchase_price * 0.10;

        return max($currentValue, $minimumValue);
    }

    /**
     * Calculate declining balance depreciation
     * Formula: Value = Purchase Price * (1 - Rate)^Years
     */
    private function calculateDecliningBalance(Vehicle $vehicle, float $years): float
    {
        $rate = $vehicle->depreciation_rate ?? 20; // Default 20% per year
        $rateDecimal = $rate / 100;

        $currentValue = $vehicle->purchase_price * pow((1 - $rateDecimal), $years);

        // Ensure minimum value of 5% original purchase price
        $minimumValue = $vehicle->purchase_price * 0.05;

        return max($currentValue, $minimumValue);
    }

    /**
     * Get years owned (including partial years)
     */
    private function getYearsOwned(Vehicle $vehicle): float
    {
        if (!$vehicle->purchase_date) {
            return 0;
        }

        $purchaseDate = Carbon::parse($vehicle->purchase_date);
        $today = Carbon::today();

        // Calculate years with 2 decimal precision
        return round($purchaseDate->floatDiffInYears($today), 2);
    }

    /**
     * Calculate total depreciation to date
     */
    public function calculateTotalDepreciation(Vehicle $vehicle): float
    {
        if (!$vehicle->purchase_price) {
            return 0;
        }

        $currentValue = $this->calculateCurrentValue($vehicle);

        return $vehicle->purchase_price - $currentValue;
    }

    /**
     * Calculate annual depreciation rate (actual)
     */
    public function calculateActualAnnualRate(Vehicle $vehicle): float
    {
        $yearsOwned = $this->getYearsOwned($vehicle);

        if ($yearsOwned <= 0 || !$vehicle->purchase_price) {
            return 0;
        }

        $totalDepreciation = $this->calculateTotalDepreciation($vehicle);
        $annualDepreciation = $totalDepreciation / $yearsOwned;

        return round(($annualDepreciation / $vehicle->purchase_price) * 100, 2);
    }

    /**
     * Calculate depreciation schedule for next 5 years
     */
    public function getDepreciationSchedule(Vehicle $vehicle): array
    {
        if (!$vehicle->purchase_price || !$vehicle->purchase_date) {
            return [];
        }

        $schedule = [];
        $currentYear = Carbon::today()->year;

        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear + $i;
            $yearsFromPurchase = $this->getYearsOwned($vehicle) + $i;

            $value = match ($vehicle->depreciation_method) {
                'straight_line' => $this->calculateStraightLine($vehicle, $yearsFromPurchase),
                'declining_balance' => $this->calculateDecliningBalance($vehicle, $yearsFromPurchase),
                default => $vehicle->purchase_price,
            };

            $schedule[] = [
                'year' => $year,
                'years_owned' => round($yearsFromPurchase, 1),
                'value' => round($value, 2),
                'depreciation' => round($vehicle->purchase_price - $value, 2),
            ];
        }

        return $schedule;
    }

    /**
     * Update vehicle's current_value in database
     */
    public function updateCurrentValue(Vehicle $vehicle): Vehicle
    {
        $currentValue = $this->calculateCurrentValue($vehicle);

        $vehicle->update(['current_value' => $currentValue]);

        return $vehicle->fresh();
    }

    /**
     * Batch update current values for all vehicles in a branch
     */
    public function batchUpdateValues(?string $branchId = null): int
    {
        $query = Vehicle::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $vehicles = $query->whereNotNull('purchase_price')
                         ->whereNotNull('purchase_date')
                         ->get();

        $updated = 0;

        foreach ($vehicles as $vehicle) {
            $this->updateCurrentValue($vehicle);
            $updated++;
        }

        return $updated;
    }
}
