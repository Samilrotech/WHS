<?php

namespace Database\Factories\Modules\InspectionManagement;

use App\Models\User;
use App\Modules\InspectionManagement\Models\Inspection;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\InspectionManagement\Models\Inspection>
 */
class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inspectionDate = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'branch_id' => 1, // Will be set by tests
            'vehicle_id' => Vehicle::factory(),
            'inspector_user_id' => User::factory(),
            'approved_by_user_id' => null,
            'inspection_number' => sprintf('INS-%d-%04d', now()->year, $this->faker->unique()->numberBetween(1, 9999)),
            'inspection_type' => $this->faker->randomElement([
                'monthly_routine',
                'pre_trip',
                'post_incident',
                'annual_compliance',
                'maintenance_followup',
                'random_spot_check',
            ]),
            'inspection_date' => $inspectionDate,
            'odometer_reading' => $this->faker->numberBetween(5000, 150000),
            'location' => $this->faker->randomElement([
                'Main depot',
                'Workshop',
                'On-site',
                'Branch office',
            ]),
            'inspection_hours' => $this->faker->randomFloat(2, 0.5, 3),
            'status' => 'pending',
            'overall_result' => null,
            'total_items_checked' => 35,
            'items_passed' => 0,
            'items_failed' => 0,
            'critical_defects' => 0,
            'major_defects' => 0,
            'minor_defects' => 0,
            'photo_paths' => null,
            'inspector_notes' => null,
            'defects_summary' => null,
            'recommendations' => null,
            'approved_date' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
            'next_inspection_due' => null,
            'compliance_verified' => false,
            'inspector_signature_path' => null,
            'approver_signature_path' => null,
        ];
    }

    /**
     * State for in progress inspection
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'items_passed' => $this->faker->numberBetween(10, 25),
            'items_failed' => $this->faker->numberBetween(0, 5),
        ]);
    }

    /**
     * State for completed inspection (awaiting approval)
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'overall_result' => 'pass',
            'items_passed' => 33,
            'items_failed' => 2,
            'minor_defects' => 2,
            'inspector_notes' => 'Minor wear noted on tires and wiper blades.',
            'defects_summary' => 'Two minor defects found - not safety critical.',
            'recommendations' => 'Replace wiper blades within 30 days.',
            'next_inspection_due' => now()->addMonth(),
        ]);
    }

    /**
     * State for approved inspection
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'overall_result' => 'pass',
            'items_passed' => 35,
            'items_failed' => 0,
            'approved_by_user_id' => User::factory(),
            'approved_date' => now()->subDays(rand(1, 5)),
            'approval_notes' => 'Inspection approved. Vehicle safe to operate.',
            'compliance_verified' => true,
            'next_inspection_due' => now()->addMonth(),
        ]);
    }

    /**
     * State for rejected inspection
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'overall_result' => 'fail_major',
            'items_passed' => 25,
            'items_failed' => 10,
            'major_defects' => 3,
            'rejection_reason' => 'Critical safety items not properly documented. Re-inspect brake system.',
        ]);
    }

    /**
     * State for failed inspection with critical defects
     */
    public function failedCritical(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'overall_result' => 'fail_critical',
            'items_passed' => 20,
            'items_failed' => 15,
            'critical_defects' => 2,
            'major_defects' => 5,
            'minor_defects' => 8,
            'inspector_notes' => 'CRITICAL: Brake fluid leak detected. Worn brake pads below minimum.',
            'defects_summary' => 'Vehicle CANNOT be operated until critical defects repaired.',
            'recommendations' => 'IMMEDIATE repair required before vehicle can be used.',
        ]);
    }

    /**
     * State for failed inspection with major defects
     */
    public function failedMajor(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'overall_result' => 'fail_major',
            'items_passed' => 27,
            'items_failed' => 8,
            'major_defects' => 3,
            'minor_defects' => 5,
            'inspector_notes' => 'Tire tread below legal limit on front left. Brake lights not functioning.',
            'defects_summary' => 'Vehicle requires repair within 24 hours.',
            'recommendations' => 'Replace tire and repair brake light circuit.',
        ]);
    }

    /**
     * State for passed inspection with minor defects
     */
    public function passedMinor(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'overall_result' => 'pass_minor',
            'items_passed' => 32,
            'items_failed' => 3,
            'minor_defects' => 3,
            'approved_by_user_id' => User::factory(),
            'approved_date' => now()->subDays(rand(1, 3)),
            'inspector_notes' => 'Vehicle operational with minor cosmetic issues.',
            'defects_summary' => 'Three minor defects - safe to operate.',
            'recommendations' => 'Address minor defects during next service.',
            'compliance_verified' => true,
            'next_inspection_due' => now()->addMonth(),
        ]);
    }

    /**
     * State for overdue approval (completed but not approved within 48 hours)
     */
    public function overdueApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'overall_result' => 'pass',
            'items_passed' => 35,
            'items_failed' => 0,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);
    }

    /**
     * State for monthly routine inspection
     */
    public function monthlyRoutine(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'monthly_routine',
        ]);
    }

    /**
     * State for pre-trip inspection
     */
    public function preTrip(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'pre_trip',
            'inspection_date' => now(),
        ]);
    }

    /**
     * State for post-incident inspection
     */
    public function postIncident(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'post_incident',
            'inspector_notes' => 'Post-incident inspection following minor collision.',
        ]);
    }

    /**
     * State for annual compliance inspection
     */
    public function annualCompliance(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'annual_compliance',
            'compliance_verified' => true,
        ]);
    }
}
