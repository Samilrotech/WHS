<?php

namespace Database\Factories\Modules\SafetyInspections;

use App\Models\Branch;
use App\Models\User;
use App\Modules\SafetyInspections\Models\SafetyInspectionTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class SafetyInspectionTemplateFactory extends Factory
{
    protected $model = SafetyInspectionTemplate::class;

    public function definition(): array
    {
        $categories = [
            'workplace_safety', 'equipment_safety', 'contractor_induction',
            'pre_start_checklist', 'safety_audit', 'adhoc_inspection',
            'warehouse_safety', 'office_safety', 'vehicle_safety',
        ];

        return [
            'branch_id' => Branch::factory(),
            'template_name' => $this->faker->randomElement([
                'Daily Pre-Start Checklist',
                'Workplace Safety Inspection',
                'Equipment Safety Audit',
                'Warehouse Safety Check',
                'Vehicle Safety Inspection',
                'Contractor Induction Checklist',
            ]),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement($categories),
            'checklist_items' => $this->generateChecklistItems(),
            'is_scored' => true,
            'pass_threshold' => $this->faker->randomElement([75, 80, 85, 90]),
            'scoring_method' => 'percentage',
            'is_mandatory' => $this->faker->boolean(60),
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'annual', 'adhoc']),
            'status' => 'active',
            'created_by_user_id' => User::factory(),
        ];
    }

    protected function generateChecklistItems(): array
    {
        return [
            [
                'code' => 'PPE-01',
                'category' => 'Personal Protective Equipment',
                'question' => 'Are all workers wearing appropriate PPE?',
                'type' => 'checkbox',
                'critical' => true,
                'weight' => 2,
            ],
            [
                'code' => 'EXT-01',
                'category' => 'Fire Safety',
                'question' => 'Are fire extinguishers accessible and serviced?',
                'type' => 'checkbox',
                'critical' => true,
                'weight' => 2,
            ],
            [
                'code' => 'EXIT-01',
                'category' => 'Emergency Exits',
                'question' => 'Are emergency exits clear and marked?',
                'type' => 'checkbox',
                'critical' => true,
                'weight' => 2,
            ],
            [
                'code' => 'HOUSE-01',
                'category' => 'Housekeeping',
                'question' => 'Is the work area clean and tidy?',
                'type' => 'rating',
                'critical' => false,
                'weight' => 1,
            ],
            [
                'code' => 'EQUIP-01',
                'category' => 'Equipment',
                'question' => 'Is all equipment in good working condition?',
                'type' => 'checkbox',
                'critical' => true,
                'weight' => 2,
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function mandatory(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandatory' => true,
        ]);
    }

    public function workplace(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_name' => 'Workplace Safety Inspection',
            'category' => 'workplace_safety',
        ]);
    }

    public function preStart(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_name' => 'Daily Pre-Start Checklist',
            'category' => 'pre_start_checklist',
            'frequency' => 'daily',
            'is_mandatory' => true,
        ]);
    }
}
