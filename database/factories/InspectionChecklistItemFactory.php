<?php

namespace Database\Factories;

use App\Modules\WarehouseEquipment\Models\InspectionChecklistItem;
use App\Modules\WarehouseEquipment\Models\EquipmentInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionChecklistItemFactory extends Factory
{
    protected $model = InspectionChecklistItem::class;

    public function definition(): array
    {
        return [
            'inspection_id' => EquipmentInspection::factory(),
            'sequence_order' => fake()->numberBetween(1, 20),
            'item_code' => fake()->bothify('CHK-###'),
            'category' => fake()->randomElement(['General', 'Safety', 'Operational', 'Maintenance']),
            'question' => fake()->sentence(),
            'item_type' => fake()->randomElement(['yes_no', 'pass_fail', 'numeric', 'text']),
            'is_critical' => fake()->boolean(20),
            'result' => 'pending',
            'defect_identified' => false,
            'defect_severity' => null,
            'defect_description' => null,
            'corrective_action_required' => null,
            'inspector_comments' => null,
            'responded_at' => null,
        ];
    }
}
