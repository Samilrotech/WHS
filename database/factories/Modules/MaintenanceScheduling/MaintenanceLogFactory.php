<?php

namespace Database\Factories\Modules\MaintenanceScheduling;

use App\Models\Branch;
use App\Models\User;
use App\Modules\MaintenanceScheduling\Models\MaintenanceLog;
use App\Modules\MaintenanceScheduling\Models\MaintenanceSchedule;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\InspectionManagement\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceLogFactory extends Factory
{
    protected $model = MaintenanceLog::class;

    public function definition(): array
    {
        $partsCost = $this->faker->randomFloat(2, 50, 1000);
        $laborCost = $this->faker->randomFloat(2, 100, 500);
        $vendorCost = $this->faker->randomElement([0, $this->faker->randomFloat(2, 200, 800)]);

        return [
            'branch_id' => Branch::factory(),
            'vehicle_id' => Vehicle::factory(),
            'maintenance_schedule_id' => null,
            'inspection_id' => null,
            'performed_by_user_id' => User::factory(),
            'approved_by_user_id' => null,

            'work_order_number' => $this->generateWorkOrderNumber(),
            'description' => $this->faker->sentence(10),
            'work_performed' => $this->faker->paragraph(3),

            'maintenance_type' => $this->faker->randomElement(['scheduled', 'unscheduled', 'inspection_followup']),
            'service_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'odometer_reading' => $this->faker->numberBetween(50000, 200000),

            'parts_cost' => $partsCost,
            'labor_cost' => $laborCost,
            'vendor_cost' => $vendorCost,
            'total_cost' => $partsCost + $laborCost + $vendorCost,

            'vendor_name' => $this->faker->randomElement(['AutoPro Services', 'Fleet Maintenance Co', 'Quick Service Centre', null]),
            'parts_used' => $this->getRandomParts(),

            'vehicle_out_of_service_at' => $outTime = $this->faker->dateTimeBetween('-6 months', 'now'),
            'vehicle_back_in_service_at' => $this->faker->dateTimeBetween($outTime, $outTime->format('Y-m-d H:i:s').' +8 hours'),
            'downtime_hours' => $this->faker->numberBetween(1, 8),

            'safety_critical' => $this->faker->boolean(20),
            'quality_rating' => null,
            'warranty_applicable' => $this->faker->boolean(30),
            'warranty_expiry_date' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('+6 months', '+2 years') : null,

            'status' => 'pending',
            'notes' => $this->faker->randomElement([$this->faker->sentence(), null]),
        ];
    }

    /**
     * Pending approval state
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'work_performed' => null,
            'approved_by_user_id' => null,
        ]);
    }

    /**
     * Approved state
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by_user_id' => User::factory(),
        ]);
    }

    /**
     * In progress state
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'approved_by_user_id' => User::factory(),
            'vehicle_out_of_service_at' => now()->subHours(2),
        ]);
    }

    /**
     * Completed state
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'approved_by_user_id' => User::factory(),
            'work_performed' => $this->faker->paragraph(4),
            'vehicle_back_in_service_at' => now(),
            'downtime_hours' => $this->faker->numberBetween(1, 6),
        ]);
    }

    /**
     * Verified state
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'approved_by_user_id' => User::factory(),
            'work_performed' => $this->faker->paragraph(4),
            'quality_rating' => $this->faker->randomElement(['excellent', 'good', 'satisfactory']),
        ]);
    }

    /**
     * Cancelled state
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'notes' => 'Work order cancelled - vehicle disposed',
        ]);
    }

    /**
     * Scheduled maintenance
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_type' => 'scheduled',
            'maintenance_schedule_id' => MaintenanceSchedule::factory(),
            'description' => 'Scheduled preventive maintenance service',
        ]);
    }

    /**
     * Unscheduled maintenance
     */
    public function unscheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_type' => 'unscheduled',
            'description' => $this->faker->randomElement([
                'Emergency brake system repair',
                'Engine overheating issue',
                'Transmission fluid leak',
                'Battery replacement required',
            ]),
            'safety_critical' => $this->faker->boolean(50),
        ]);
    }

    /**
     * Inspection follow-up
     */
    public function inspectionFollowup(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_type' => 'inspection_followup',
            'inspection_id' => Inspection::factory(),
            'description' => 'Corrective maintenance from inspection defects',
            'safety_critical' => $this->faker->boolean(60),
        ]);
    }

    /**
     * Emergency maintenance
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_type' => 'emergency',
            'description' => 'Emergency roadside repair',
            'safety_critical' => true,
            'parts_cost' => $this->faker->randomFloat(2, 200, 1500),
            'labor_cost' => $this->faker->randomFloat(2, 300, 1000),
            'vendor_cost' => $this->faker->randomFloat(2, 500, 1200),
        ]);
    }

    /**
     * Safety critical work
     */
    public function safetyCritical(): static
    {
        return $this->state(fn (array $attributes) => [
            'safety_critical' => true,
            'description' => $this->faker->randomElement([
                'Critical brake system failure repair',
                'Steering system emergency repair',
                'Safety-critical suspension repair',
            ]),
        ]);
    }

    /**
     * With warranty
     */
    public function withWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_applicable' => true,
            'warranty_expiry_date' => $this->faker->dateTimeBetween('+6 months', '+2 years'),
            'vendor_name' => 'AutoPro Services',
        ]);
    }

    /**
     * High cost work order
     */
    public function highCost(): static
    {
        $partsCost = $this->faker->randomFloat(2, 2000, 5000);
        $laborCost = $this->faker->randomFloat(2, 1000, 2000);
        $vendorCost = $this->faker->randomFloat(2, 1000, 3000);

        return $this->state(fn (array $attributes) => [
            'parts_cost' => $partsCost,
            'labor_cost' => $laborCost,
            'vendor_cost' => $vendorCost,
            'total_cost' => $partsCost + $laborCost + $vendorCost,
            'description' => 'Major engine overhaul',
        ]);
    }

    /**
     * With extended downtime
     */
    public function extendedDowntime(): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_out_of_service_at' => $outTime = $this->faker->dateTimeBetween('-1 month', '-1 week'),
            'vehicle_back_in_service_at' => $this->faker->dateTimeBetween($outTime, $outTime->format('Y-m-d H:i:s').' +72 hours'),
            'downtime_hours' => $this->faker->numberBetween(48, 168),
            'notes' => 'Extended downtime due to parts availability',
        ]);
    }

    /**
     * Excellent quality rating
     */
    public function excellentQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'quality_rating' => 'excellent',
            'notes' => 'Work completed to exceptional standard',
        ]);
    }

    /**
     * Poor quality rating
     */
    public function poorQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'quality_rating' => 'poor',
            'notes' => 'Quality issues identified - vendor follow-up required',
        ]);
    }

    protected function generateWorkOrderNumber(): string
    {
        $year = date('Y');
        $sequence = $this->faker->numberBetween(1, 9999);
        return sprintf('WO-%d-%04d', $year, $sequence);
    }

    protected function getRandomParts(): ?string
    {
        $parts = [
            ['part_number' => 'FLT-001', 'name' => 'Oil Filter', 'quantity' => 1],
            ['part_number' => 'OIL-5W30', 'name' => 'Engine Oil 5W-30', 'quantity' => 5],
            ['part_number' => 'BRK-PAD-F', 'name' => 'Front Brake Pads', 'quantity' => 1],
            ['part_number' => 'AIR-FLT', 'name' => 'Air Filter', 'quantity' => 1],
        ];

        $selectedParts = $this->faker->randomElements($parts, $this->faker->numberBetween(1, 3));

        return $this->faker->boolean(80) ? json_encode($selectedParts) : null;
    }
}
