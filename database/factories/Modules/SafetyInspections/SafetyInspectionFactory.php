<?php

namespace Database\Factories\Modules\SafetyInspections;

use App\Models\Branch;
use App\Models\User;
use App\Modules\SafetyInspections\Models\SafetyInspection;
use App\Modules\SafetyInspections\Models\SafetyInspectionTemplate;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class SafetyInspectionFactory extends Factory
{
    protected $model = SafetyInspection::class;

    public function definition(): array
    {
        $statuses = ['scheduled', 'in_progress', 'completed', 'submitted', 'approved', 'rejected', 'cancelled'];
        $status = $this->faker->randomElement($statuses);

        $scheduledDate = $this->faker->dateTimeBetween('-2 months', '+1 month');
        $startedAt = in_array($status, ['in_progress', 'completed', 'submitted', 'approved', 'rejected'])
            ? $this->faker->dateTimeBetween($scheduledDate, 'now')
            : null;
        $completedAt = in_array($status, ['completed', 'submitted', 'approved', 'rejected'])
            ? $this->faker->dateTimeBetween($startedAt ?? $scheduledDate, 'now')
            : null;

        return [
            'branch_id' => Branch::factory(),
            'template_id' => SafetyInspectionTemplate::factory(),
            'inspector_user_id' => User::factory(),
            'status' => $status,
            'scheduled_date' => $scheduledDate,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'location' => $this->faker->randomElement(['Warehouse A', 'Office Block', 'Loading Dock', 'Workshop', 'Storage Area']),
            'area' => $this->faker->randomElement(['Zone 1', 'Zone 2', 'Ground Floor', 'Level 2', null]),
            'asset_tag' => $this->faker->boolean(40) ? strtoupper($this->faker->bothify('AST-####')) : null,
            'vehicle_id' => $this->faker->boolean(30) ? Vehicle::factory() : null,
            'total_items' => 10,
            'completed_items' => $this->faker->numberBetween(0, 10),
            'inspection_score' => $completedAt ? $this->faker->randomFloat(2, 60, 100) : null,
            'passed' => $completedAt ? $this->faker->boolean(80) : null,
            'has_non_compliance' => $this->faker->boolean(30),
            'non_compliance_severity' => function (array $attributes) {
                return $attributes['has_non_compliance']
                    ? $this->faker->randomElement(['low', 'medium', 'high', 'critical'])
                    : 'none';
            },
            'escalation_required' => function (array $attributes) {
                return in_array($attributes['non_compliance_severity'], ['high', 'critical']);
            },
            'inspector_notes' => $completedAt && $this->faker->boolean(60) ? $this->faker->paragraph() : null,
            'inspector_signature_path' => $completedAt ? 'storage/signatures/'.$this->faker->uuid().'.png' : null,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'started_at' => null,
            'completed_at' => null,
            'inspection_score' => null,
            'passed' => null,
        ]);
    }

    public function inProgress(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-1 week', 'now');

        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'started_at' => $startedAt,
            'completed_at' => null,
            'completed_items' => $this->faker->numberBetween(3, 7),
            'inspection_score' => null,
            'passed' => null,
        ]);
    }

    public function completed(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-2 weeks', '-1 day');
        $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');
        $score = $this->faker->randomFloat(2, 70, 100);

        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'completed_items' => 10,
            'inspection_score' => $score,
            'passed' => $score >= 80,
            'inspector_notes' => $this->faker->paragraph(),
            'inspector_signature_path' => 'storage/signatures/'.$this->faker->uuid().'.png',
        ]);
    }

    public function passed(): static
    {
        return $this->completed()->state(fn (array $attributes) => [
            'inspection_score' => $this->faker->randomFloat(2, 85, 100),
            'passed' => true,
            'has_non_compliance' => false,
            'non_compliance_severity' => 'none',
        ]);
    }

    public function failed(): static
    {
        return $this->completed()->state(fn (array $attributes) => [
            'inspection_score' => $this->faker->randomFloat(2, 40, 75),
            'passed' => false,
            'has_non_compliance' => true,
            'non_compliance_severity' => $this->faker->randomElement(['medium', 'high', 'critical']),
        ]);
    }

    public function withCriticalIssues(): static
    {
        return $this->failed()->state(fn (array $attributes) => [
            'has_non_compliance' => true,
            'non_compliance_severity' => 'critical',
            'escalation_required' => true,
            'inspection_score' => $this->faker->randomFloat(2, 30, 60),
        ]);
    }

    public function approved(): static
    {
        return $this->passed()->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewer_user_id' => User::factory(),
            'reviewed_at' => $this->faker->dateTimeBetween($attributes['completed_at'], 'now'),
            'reviewer_comments' => 'Inspection approved. All standards met.',
        ]);
    }

    public function rejected(): static
    {
        return $this->failed()->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reviewer_user_id' => User::factory(),
            'reviewed_at' => $this->faker->dateTimeBetween($attributes['completed_at'], 'now'),
            'rejection_reason' => 'Multiple critical non-compliance items require immediate attention.',
        ]);
    }
}
