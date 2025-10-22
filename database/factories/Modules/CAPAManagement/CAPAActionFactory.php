<?php

namespace Database\Factories\Modules\CAPAManagement;

use App\Models\User;
use App\Modules\CAPAManagement\Models\CAPA;
use App\Modules\CAPAManagement\Models\CAPAAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\CAPAManagement\Models\CAPAAction>
 */
class CAPAActionFactory extends Factory
{
    protected $model = CAPAAction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'capa_id' => CAPA::factory(),
            'assigned_to_user_id' => $this->faker->boolean(70) ? User::factory() : null,
            'title' => $this->faker->randomElement([
                'Conduct safety assessment',
                'Procure safety equipment',
                'Update operating procedures',
                'Train personnel on new procedures',
                'Install additional safeguards',
                'Review and update documentation',
                'Perform equipment inspection',
                'Implement monitoring system',
                'Communicate changes to team',
                'Verify effectiveness of controls',
            ]),
            'description' => $this->faker->boolean(60) ? $this->faker->sentence() : null,
            'sequence_order' => $this->faker->numberBetween(1, 10),
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
            'completed_date' => null,
            'status' => 'pending',
            'is_completed' => false,
            'completed_by_user_id' => null,
            'completion_notes' => null,
            'evidence_paths' => null,
        ];
    }

    /**
     * State for completed action
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'is_completed' => true,
            'completed_date' => now()->subDays(rand(1, 10)),
            'completed_by_user_id' => User::factory(),
            'completion_notes' => $this->faker->optional()->sentence(),
            'evidence_paths' => $this->faker->optional()->randomElements([
                '/storage/evidence/photo1.jpg',
                '/storage/evidence/checklist.pdf',
                '/storage/evidence/inspection_report.pdf',
            ], rand(1, 2)),
        ]);
    }

    /**
     * State for in progress action
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'is_completed' => false,
        ]);
    }

    /**
     * State for overdue action
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'is_completed' => false,
            'due_date' => now()->subDays(rand(1, 30)),
        ]);
    }

    /**
     * State for action with evidence
     */
    public function withEvidence(): static
    {
        return $this->state(fn (array $attributes) => [
            'evidence_paths' => [
                '/storage/evidence/before_photo.jpg',
                '/storage/evidence/after_photo.jpg',
                '/storage/evidence/completion_certificate.pdf',
            ],
        ]);
    }

    /**
     * State for first action in sequence
     */
    public function first(): static
    {
        return $this->state(fn (array $attributes) => [
            'sequence_order' => 1,
            'title' => 'Initial assessment and planning',
        ]);
    }

    /**
     * State for final action in sequence
     */
    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Final verification and documentation',
            'sequence_order' => 10,
        ]);
    }
}
