<?php

namespace Database\Factories\Modules\CAPAManagement;

use App\Models\User;
use App\Models\Branch;
use App\Modules\IncidentManagement\Models\Incident;
use App\Modules\CAPAManagement\Models\CAPA;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\CAPAManagement\Models\CAPA>
 */
class CAPAFactory extends Factory
{
    protected $model = CAPA::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['corrective', 'preventive']);
        $priority = $this->faker->randomElement(['low', 'medium', 'high', 'critical']);

        $targetDate = $this->faker->dateTimeBetween('+1 week', '+3 months');

        return [
            'branch_id' => Branch::factory(),
            'incident_id' => null,
            'raised_by_user_id' => User::factory(),
            'assigned_to_user_id' => $this->faker->boolean(70) ? User::factory() : null,
            'capa_number' => sprintf('CAPA-%d-%04d', now()->year, $this->faker->unique()->numberBetween(1, 9999)),
            'type' => $type,
            'title' => $type === 'corrective'
                ? $this->faker->randomElement([
                    'Implement additional safety barriers',
                    'Enhance incident reporting procedures',
                    'Improve equipment maintenance schedule',
                    'Update emergency response protocols',
                    'Strengthen hazard communication system',
                ])
                : $this->faker->randomElement([
                    'Proactive safety training program',
                    'Preventive equipment inspection regime',
                    'Risk assessment enhancement initiative',
                    'Safety culture improvement campaign',
                    'Predictive maintenance system implementation',
                ]),
            'description' => $this->faker->paragraphs(2, true),
            'problem_statement' => $this->faker->boolean(80) ? $this->faker->paragraph() : null,
            'root_cause_analysis' => $this->faker->boolean(70) ? $this->faker->paragraph() : null,
            'five_whys' => $this->faker->boolean(60) ? $this->generateFiveWhys() : null,
            'contributing_factors' => $this->faker->boolean(60) ? $this->faker->paragraph() : null,
            'proposed_action' => $this->faker->paragraphs(2, true),
            'implementation_steps' => $this->faker->boolean(70) ? [
                'Step 1: Conduct initial assessment',
                'Step 2: Develop action plan',
                'Step 3: Allocate resources',
                'Step 4: Execute implementation',
                'Step 5: Monitor and adjust',
            ] : null,
            'resources_required' => $this->faker->boolean(60) ? $this->faker->paragraph() : null,
            'estimated_cost' => $this->faker->boolean(70) ? $this->faker->randomFloat(2, 1000, 50000) : null,
            'target_completion_date' => $targetDate,
            'actual_completion_date' => null,
            'estimated_hours' => $this->faker->boolean(70) ? $this->faker->numberBetween(10, 200) : null,
            'actual_hours' => null,
            'status' => 'draft',
            'priority' => $priority,
            'verification_date' => null,
            'verified_by_user_id' => null,
            'verification_method' => null,
            'verification_results' => null,
            'effectiveness_confirmed' => false,
            'approved_by_user_id' => null,
            'approval_date' => null,
            'approval_notes' => null,
            'rejection_reason' => null,
            'closed_by_user_id' => null,
            'closure_date' => null,
            'closure_notes' => null,
            'attachment_paths' => null,
            'notes' => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
        ];
    }

    /**
     * Generate Five Whys analysis text
     */
    private function generateFiveWhys(): string
    {
        $problems = [
            'Equipment failed during operation',
            'Procedure was not followed correctly',
            'Worker was injured during task',
            'Quality standards were not met',
            'Production delay occurred',
        ];

        $why1 = $this->faker->randomElement($problems);

        return <<<EOT
        Why did this happen? {$why1}
        Why? Maintenance schedule was not followed
        Why? Resources were allocated to emergency tasks
        Why? Lack of preventive maintenance planning
        Why? Insufficient management oversight and accountability
        Root Cause: Inadequate preventive maintenance system and resource allocation
        EOT;
    }

    /**
     * State for corrective action
     */
    public function corrective(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'corrective',
            'title' => 'Implement corrective action for safety incident',
        ]);
    }

    /**
     * State for preventive action
     */
    public function preventive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'preventive',
            'title' => 'Implement preventive measures to avoid future incidents',
        ]);
    }

    /**
     * State for linked to incident
     */
    public function withIncident(): static
    {
        return $this->state(fn (array $attributes) => [
            'incident_id' => Incident::factory(),
            'type' => 'corrective',
        ]);
    }

    /**
     * State for critical priority
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
            'target_completion_date' => now()->addWeek(),
        ]);
    }

    /**
     * State for high priority
     */
    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'target_completion_date' => now()->addWeeks(2),
        ]);
    }

    /**
     * State for submitted status
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
        ]);
    }

    /**
     * State for approved status
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by_user_id' => User::factory(),
            'approval_date' => now()->subDays(rand(1, 5)),
            'approval_notes' => $this->faker->optional()->sentence(),
        ]);
    }

    /**
     * State for in progress status
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'approved_by_user_id' => User::factory(),
            'approval_date' => now()->subDays(rand(5, 10)),
        ]);
    }

    /**
     * State for completed status
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'approved_by_user_id' => User::factory(),
            'approval_date' => now()->subDays(rand(20, 30)),
            'actual_completion_date' => now()->subDays(rand(1, 5)),
            'actual_hours' => $attributes['estimated_hours'] ?? $this->faker->numberBetween(10, 200),
        ]);
    }

    /**
     * State for verified status
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'approved_by_user_id' => User::factory(),
            'approval_date' => now()->subDays(rand(30, 40)),
            'actual_completion_date' => now()->subDays(rand(10, 15)),
            'verified_by_user_id' => User::factory(),
            'verification_date' => now()->subDays(rand(1, 5)),
            'verification_method' => 'Conducted follow-up inspection and reviewed incident data',
            'verification_results' => 'No recurrence of the original issue. Controls are effective.',
            'effectiveness_confirmed' => true,
        ]);
    }

    /**
     * State for closed status
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'approved_by_user_id' => User::factory(),
            'approval_date' => now()->subDays(rand(40, 50)),
            'actual_completion_date' => now()->subDays(rand(20, 25)),
            'verified_by_user_id' => User::factory(),
            'verification_date' => now()->subDays(rand(10, 15)),
            'verification_method' => 'Conducted follow-up inspection and reviewed incident data',
            'verification_results' => 'No recurrence of the original issue. Controls are effective.',
            'effectiveness_confirmed' => true,
            'closed_by_user_id' => User::factory(),
            'closure_date' => now()->subDays(rand(1, 5)),
            'closure_notes' => $this->faker->optional()->sentence(),
        ]);
    }

    /**
     * State for rejected status
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => $this->faker->randomElement([
                'Proposed action does not address root cause adequately',
                'Cost estimate exceeds budget constraints',
                'Timeline is not realistic for implementation',
                'Requires further analysis before approval',
                'Duplicate of existing CAPA',
            ]),
        ]);
    }

    /**
     * State for overdue CAPA
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $this->faker->randomElement(['approved', 'in_progress']),
            'target_completion_date' => now()->subDays(rand(1, 30)),
            'approved_by_user_id' => User::factory(),
            'approval_date' => now()->subDays(rand(35, 45)),
        ]);
    }

    /**
     * State for CAPA with full root cause analysis
     */
    public function withFullRCA(): static
    {
        return $this->state(fn (array $attributes) => [
            'problem_statement' => 'Worker sustained laceration while operating machinery without proper guard in place.',
            'five_whys' => $this->generateFiveWhys(),
            'root_cause_analysis' => 'Inadequate machine guarding procedures and insufficient safety oversight led to unsafe operating conditions.',
            'contributing_factors' => 'Time pressure to meet production deadlines, inadequate training on machine safety, lack of regular equipment inspections.',
        ]);
    }
}
