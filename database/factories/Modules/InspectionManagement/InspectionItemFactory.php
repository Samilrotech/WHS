<?php

namespace Database\Factories\Modules\InspectionManagement;

use App\Models\User;
use App\Modules\InspectionManagement\Models\Inspection;
use App\Modules\InspectionManagement\Models\InspectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\InspectionManagement\Models\InspectionItem>
 */
class InspectionItemFactory extends Factory
{
    protected $model = InspectionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = $this->faker->randomElement(['Engine', 'Tires', 'Brakes', 'Lights', 'Steering', 'Interior', 'Exterior']);

        return [
            'inspection_id' => Inspection::factory(),
            'item_category' => $category,
            'item_name' => $this->getItemNameForCategory($category),
            'item_description' => $this->faker->optional()->sentence(),
            'sequence_order' => $this->faker->numberBetween(1, 35),
            'result' => 'pending',
            'defect_severity' => null,
            'measurement_value' => null,
            'expected_range' => null,
            'within_tolerance' => null,
            'defect_notes' => null,
            'repair_recommendation' => null,
            'urgency' => null,
            'photo_paths' => null,
            'annotations' => null,
            'repair_required' => false,
            'repair_due_date' => null,
            'repair_completed' => false,
            'repaired_by_user_id' => null,
            'repair_completion_date' => null,
            'repair_cost' => null,
            'repair_notes' => null,
            'safety_critical' => $this->faker->boolean(30),
            'compliance_item' => $this->faker->boolean(20),
            'compliance_standard' => $this->faker->optional(20)->randomElement(['ADR 13/00', 'ADR 42/05', 'ADR 69/00']),
        ];
    }

    /**
     * Get realistic item names based on category
     */
    protected function getItemNameForCategory(string $category): string
    {
        return match ($category) {
            'Engine' => $this->faker->randomElement([
                'Engine oil level',
                'Coolant level',
                'Engine leaks',
                'Air filter',
                'Battery condition',
            ]),
            'Tires' => $this->faker->randomElement([
                'Front left tire tread depth',
                'Front right tire tread depth',
                'Rear left tire tread depth',
                'Rear right tire tread depth',
                'Tire pressure (all)',
            ]),
            'Brakes' => $this->faker->randomElement([
                'Brake fluid level',
                'Brake pad wear',
                'Brake performance',
                'Handbrake operation',
            ]),
            'Lights' => $this->faker->randomElement([
                'Headlights (high beam)',
                'Headlights (low beam)',
                'Tail lights',
                'Brake lights',
                'Turn signals',
            ]),
            'Steering' => $this->faker->randomElement([
                'Steering play',
                'Power steering fluid',
                'Suspension noise',
                'Shock absorbers',
            ]),
            'Interior' => $this->faker->randomElement([
                'Seatbelts',
                'Horn',
                'Windscreen wipers',
                'Mirrors',
                'First aid kit',
            ]),
            'Exterior' => $this->faker->randomElement([
                'Windscreen condition',
                'Body damage',
                'Doors and locks',
                'Fuel cap',
            ]),
            default => 'General item',
        };
    }

    /**
     * State for passed item
     */
    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'pass',
            'defect_severity' => 'none',
            'within_tolerance' => true,
        ]);
    }

    /**
     * State for failed item with minor defect
     */
    public function failedMinor(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'fail',
            'defect_severity' => 'minor',
            'defect_notes' => 'Minor wear observed. Functional but needs monitoring.',
            'repair_recommendation' => 'Replace during next scheduled service.',
            'urgency' => 'low',
            'repair_required' => true,
            'repair_due_date' => now()->addMonth(),
        ]);
    }

    /**
     * State for failed item with major defect
     */
    public function failedMajor(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'fail',
            'defect_severity' => 'major',
            'defect_notes' => 'Significant defect affecting safety. Requires prompt repair.',
            'repair_recommendation' => 'Repair within 24 hours before further use.',
            'urgency' => 'urgent',
            'repair_required' => true,
            'repair_due_date' => now()->addDay(),
        ]);
    }

    /**
     * State for failed item with critical defect
     */
    public function failedCritical(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'fail',
            'defect_severity' => 'critical',
            'defect_notes' => 'CRITICAL SAFETY ISSUE. Vehicle cannot be operated.',
            'repair_recommendation' => 'IMMEDIATE repair required. Do not use vehicle.',
            'urgency' => 'immediate',
            'repair_required' => true,
            'repair_due_date' => now(),
            'safety_critical' => true,
        ]);
    }

    /**
     * State for not applicable item
     */
    public function notApplicable(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'na',
        ]);
    }

    /**
     * State for repair completed
     */
    public function repairCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'fail',
            'defect_severity' => 'major',
            'repair_required' => true,
            'repair_completed' => true,
            'repaired_by_user_id' => User::factory(),
            'repair_completion_date' => now()->subDays(rand(1, 5)),
            'repair_cost' => $this->faker->randomFloat(2, 50, 500),
            'repair_notes' => 'Repair completed successfully. Item now operational.',
        ]);
    }

    /**
     * State for overdue repair
     */
    public function repairOverdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'fail',
            'defect_severity' => 'major',
            'repair_required' => true,
            'repair_completed' => false,
            'repair_due_date' => now()->subDays(rand(1, 10)),
        ]);
    }

    /**
     * State for safety critical item
     */
    public function safetyCritical(): static
    {
        return $this->state(fn (array $attributes) => [
            'safety_critical' => true,
            'compliance_item' => true,
            'compliance_standard' => $this->faker->randomElement(['ADR 13/00', 'ADR 42/05', 'ADR 69/00']),
        ]);
    }

    /**
     * State for tire tread measurement
     */
    public function tireTread(): static
    {
        $treadDepth = $this->faker->randomFloat(1, 1.0, 8.0);

        return $this->state(fn (array $attributes) => [
            'item_category' => 'Tires',
            'item_name' => 'Tire tread depth',
            'measurement_value' => "{$treadDepth}mm",
            'expected_range' => 'Min 1.5mm',
            'within_tolerance' => $treadDepth >= 1.5,
            'result' => $treadDepth >= 1.5 ? 'pass' : 'fail',
            'defect_severity' => $treadDepth < 1.5 ? 'critical' : 'none',
            'safety_critical' => true,
            'compliance_item' => true,
            'compliance_standard' => 'ADR 42/05',
        ]);
    }
}
