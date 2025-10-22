<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use App\Modules\WarehouseEquipment\Models\EquipmentInspection;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipmentInspectionFactory extends Factory
{
    protected $model = EquipmentInspection::class;

    public function definition(): array
    {
        $status = fake()->randomElement([
            'scheduled', 'in_progress', 'completed', 'submitted', 'approved', 'rejected', 'cancelled'
        ]);

        $inspectionType = fake()->randomElement([
            'pre_start', 'scheduled_inspection', 'post_incident',
            'defect_repair_verification', 'compliance_audit', 'annual_inspection'
        ]);

        $scheduledDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $isCompleted = in_array($status, ['completed', 'submitted', 'approved', 'rejected']);

        return [
            'branch_id' => Branch::factory(),
            'equipment_id' => WarehouseEquipment::factory(),
            'inspection_number' => $this->generateInspectionNumber(),
            'inspection_type' => $inspectionType,
            'inspection_date' => $scheduledDate,
            'scheduled_date' => $scheduledDate,
            'inspector_user_id' => $isCompleted ? User::factory() : null,
            'started_at' => $isCompleted ? fake()->dateTimeBetween($scheduledDate, 'now') : null,
            'completed_at' => in_array($status, ['completed', 'submitted', 'approved'])
                ? fake()->dateTimeBetween($scheduledDate, 'now')
                : null,
            'status' => $status,
            'inspection_score' => $isCompleted ? fake()->randomFloat(2, 50, 100) : null,
            'passed' => $isCompleted ? fake()->boolean(80) : null,
            'defects_found' => $isCompleted ? fake()->boolean(30) : false,
            'severity' => $isCompleted && fake()->boolean(30)
                ? fake()->randomElement(['minor', 'moderate', 'major', 'critical'])
                : 'none',
            'escalation_required' => $isCompleted && fake()->boolean(10),
            'inspector_notes' => $isCompleted ? fake()->optional(0.7)->paragraph() : null,
            'reviewer_user_id' => in_array($status, ['approved', 'rejected']) ? User::factory() : null,
            'reviewed_at' => in_array($status, ['approved', 'rejected'])
                ? fake()->dateTimeBetween($scheduledDate, 'now')
                : null,
            'reviewer_comments' => in_array($status, ['approved', 'rejected'])
                ? fake()->optional(0.6)->sentence()
                : null,
        ];
    }

    protected function generateInspectionNumber(): string
    {
        $year = now()->year;
        $number = fake()->numberBetween(1, 9999);
        return sprintf('EQI-%d-%04d', $year, $number);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_date' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'inspector_user_id' => null,
            'started_at' => null,
            'completed_at' => null,
            'inspection_score' => null,
            'passed' => null,
            'defects_found' => false,
            'severity' => 'none',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'inspector_user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-2 hours', 'now'),
            'completed_at' => null,
            'inspection_score' => null,
            'passed' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'inspector_user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-1 week', '-2 days'),
            'completed_at' => fake()->dateTimeBetween('-2 days', 'now'),
            'inspection_score' => fake()->randomFloat(2, 70, 100),
            'passed' => true,
            'defects_found' => fake()->boolean(20),
            'severity' => fake()->randomElement(['none', 'minor', 'moderate']),
            'inspector_notes' => fake()->paragraph(),
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'inspector_user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-1 week', '-3 days'),
            'completed_at' => fake()->dateTimeBetween('-3 days', '-1 day'),
            'inspection_score' => fake()->randomFloat(2, 60, 100),
            'passed' => fake()->boolean(85),
            'inspector_notes' => fake()->paragraph(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'inspector_user_id' => User::factory(),
            'reviewer_user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-2 weeks', '-5 days'),
            'completed_at' => fake()->dateTimeBetween('-5 days', '-2 days'),
            'reviewed_at' => fake()->dateTimeBetween('-2 days', 'now'),
            'inspection_score' => fake()->randomFloat(2, 80, 100),
            'passed' => true,
            'defects_found' => fake()->boolean(15),
            'severity' => fake()->randomElement(['none', 'minor']),
            'inspector_notes' => fake()->paragraph(),
            'reviewer_comments' => fake()->optional(0.5)->sentence(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'inspector_user_id' => User::factory(),
            'reviewer_user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-2 weeks', '-5 days'),
            'completed_at' => fake()->dateTimeBetween('-5 days', '-2 days'),
            'reviewed_at' => fake()->dateTimeBetween('-2 days', 'now'),
            'inspection_score' => fake()->randomFloat(2, 40, 70),
            'passed' => false,
            'defects_found' => true,
            'severity' => fake()->randomElement(['moderate', 'major', 'critical']),
            'inspector_notes' => fake()->paragraph(),
            'reviewer_comments' => 'Inspection requires re-evaluation. ' . fake()->sentence(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'inspector_user_id' => null,
            'started_at' => null,
            'completed_at' => null,
            'inspection_score' => null,
            'passed' => null,
            'inspector_notes' => 'Inspection cancelled - equipment unavailable',
        ]);
    }

    public function preStart(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'pre_start',
            'scheduled_date' => fake()->dateTimeBetween('-1 day', 'now'),
            'inspection_date' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function scheduledInspection(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'scheduled_inspection',
        ]);
    }

    public function postIncident(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'post_incident',
            'defects_found' => true,
            'severity' => fake()->randomElement(['moderate', 'major', 'critical']),
            'escalation_required' => true,
        ]);
    }

    public function annualInspection(): static
    {
        return $this->state(fn (array $attributes) => [
            'inspection_type' => 'annual_inspection',
            'inspector_notes' => 'Annual comprehensive inspection completed',
        ]);
    }

    public function withMinorDefects(): static
    {
        return $this->state(fn (array $attributes) => [
            'defects_found' => true,
            'severity' => 'minor',
            'passed' => true,
            'inspection_score' => fake()->randomFloat(2, 80, 95),
            'escalation_required' => false,
        ]);
    }

    public function withModerateDefects(): static
    {
        return $this->state(fn (array $attributes) => [
            'defects_found' => true,
            'severity' => 'moderate',
            'passed' => fake()->boolean(60),
            'inspection_score' => fake()->randomFloat(2, 65, 85),
            'escalation_required' => fake()->boolean(30),
        ]);
    }

    public function withMajorDefects(): static
    {
        return $this->state(fn (array $attributes) => [
            'defects_found' => true,
            'severity' => 'major',
            'passed' => false,
            'inspection_score' => fake()->randomFloat(2, 40, 70),
            'escalation_required' => true,
        ]);
    }

    public function withCriticalDefects(): static
    {
        return $this->state(fn (array $attributes) => [
            'defects_found' => true,
            'severity' => 'critical',
            'passed' => false,
            'inspection_score' => fake()->randomFloat(2, 20, 50),
            'escalation_required' => true,
            'status' => 'rejected',
        ]);
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'passed' => true,
            'inspection_score' => fake()->randomFloat(2, 80, 100),
            'defects_found' => fake()->boolean(20),
            'severity' => fake()->randomElement(['none', 'minor']),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'passed' => false,
            'inspection_score' => fake()->randomFloat(2, 30, 75),
            'defects_found' => true,
            'severity' => fake()->randomElement(['moderate', 'major', 'critical']),
            'escalation_required' => true,
        ]);
    }
}
