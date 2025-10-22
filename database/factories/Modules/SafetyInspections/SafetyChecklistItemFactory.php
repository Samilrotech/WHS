<?php

namespace Database\Factories\Modules\SafetyInspections;

use App\Modules\SafetyInspections\Models\SafetyChecklistItem;
use App\Modules\SafetyInspections\Models\SafetyInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class SafetyChecklistItemFactory extends Factory
{
    protected $model = SafetyChecklistItem::class;

    public function definition(): array
    {
        $results = ['pass', 'fail', 'na', 'pending'];
        $result = $this->faker->randomElement($results);
        $nonCompliant = $result === 'fail' && $this->faker->boolean(70);

        return [
            'inspection_id' => SafetyInspection::factory(),
            'sequence_order' => $this->faker->numberBetween(1, 20),
            'item_code' => strtoupper($this->faker->bothify('CHK-##')),
            'category' => $this->faker->randomElement(['PPE', 'Fire Safety', 'Emergency Exits', 'Housekeeping', 'Equipment']),
            'question' => $this->faker->sentence().'?',
            'item_type' => $this->faker->randomElement(['checkbox', 'rating', 'photo_required', 'text_input']),
            'result' => $result,
            'response_value' => $result !== 'pending' ? $this->faker->word() : null,
            'response_notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'responded_at' => $result !== 'pending' ? $this->faker->dateTimeThisMonth() : null,
            'is_critical' => $this->faker->boolean(30),
            'score_weight' => $this->faker->randomElement([1, 2]),
            'non_compliant' => $nonCompliant,
            'severity' => $nonCompliant ? $this->faker->randomElement(['low', 'medium', 'high', 'critical']) : null,
            'non_compliance_notes' => $nonCompliant ? $this->faker->sentence() : null,
            'corrective_action_required' => $nonCompliant ? $this->faker->sentence() : null,
            'correction_due_date' => $nonCompliant ? $this->faker->dateTimeBetween('now', '+30 days') : null,
        ];
    }

    public function pass(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'pass',
            'non_compliant' => false,
            'severity' => null,
            'responded_at' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    public function fail(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'fail',
            'non_compliant' => true,
            'severity' => $this->faker->randomElement(['medium', 'high', 'critical']),
            'non_compliance_notes' => 'Issue identified requiring attention',
            'corrective_action_required' => 'Immediate corrective action needed',
            'responded_at' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    public function critical(): static
    {
        return $this->fail()->state(fn (array $attributes) => [
            'is_critical' => true,
            'severity' => 'critical',
            'non_compliance_notes' => 'Critical safety violation identified',
            'corrective_action_required' => 'Immediate shutdown and remediation required',
            'correction_due_date' => now()->addDays(1),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'pending',
            'response_value' => null,
            'responded_at' => null,
            'non_compliant' => false,
        ]);
    }
}
