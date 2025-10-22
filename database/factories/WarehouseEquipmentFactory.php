<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseEquipmentFactory extends Factory
{
    protected $model = WarehouseEquipment::class;

    public function definition(): array
    {
        $type = fake()->randomElement([
            'forklift', 'pallet_jack', 'scissor_lift', 'reach_truck',
            'order_picker', 'hand_tools', 'power_tools', 'safety_equipment',
            'racking', 'conveyor', 'loading_dock', 'other'
        ]);

        $status = fake()->randomElement(['available', 'in_use', 'maintenance', 'out_of_service', 'retired']);

        $manufacturersByType = [
            'forklift' => ['Toyota', 'Crown', 'Hyster', 'Yale', 'Raymond'],
            'pallet_jack' => ['Crown', 'Yale', 'Jungheinrich', 'Big Joe'],
            'scissor_lift' => ['Genie', 'JLG', 'Skyjack', 'Haulotte'],
            'reach_truck' => ['Crown', 'Raymond', 'Yale', 'Jungheinrich'],
            'order_picker' => ['Crown', 'Raymond', 'Yale'],
            'hand_tools' => ['Milwaukee', 'DeWalt', 'Makita', 'Bosch'],
            'power_tools' => ['Milwaukee', 'DeWalt', 'Makita', 'Bosch', 'Ryobi'],
            'safety_equipment' => ['3M', 'Honeywell', 'MSA', 'Uvex'],
            'racking' => ['Dexion', 'Hannibal', 'Colby', 'Schaefer'],
            'conveyor' => ['Interroll', 'Siemens', 'Dematic', 'Vanderlande'],
            'loading_dock' => ['Rite-Hite', 'Kelley', 'Serco', 'Blue Giant'],
            'other' => ['Various', 'Generic'],
        ];

        $manufacturer = fake()->randomElement($manufacturersByType[$type] ?? ['Generic']);

        $ppeByType = [
            'forklift' => ['high_vis_vest', 'steel_cap_boots', 'hard_hat'],
            'pallet_jack' => ['high_vis_vest', 'steel_cap_boots'],
            'scissor_lift' => ['high_vis_vest', 'hard_hat', 'harness', 'steel_cap_boots'],
            'reach_truck' => ['high_vis_vest', 'steel_cap_boots', 'hard_hat'],
            'order_picker' => ['high_vis_vest', 'harness', 'steel_cap_boots'],
            'hand_tools' => ['safety_glasses', 'gloves'],
            'power_tools' => ['safety_glasses', 'gloves', 'hearing_protection'],
            'safety_equipment' => [],
            'racking' => ['hard_hat', 'steel_cap_boots', 'high_vis_vest'],
            'conveyor' => ['safety_glasses', 'gloves'],
            'loading_dock' => ['high_vis_vest', 'steel_cap_boots'],
            'other' => ['high_vis_vest'],
        ];

        $requiresLicense = in_array($type, ['forklift', 'scissor_lift', 'reach_truck', 'order_picker']);

        $licenseTypes = [
            'forklift' => 'LF - Forklift License',
            'scissor_lift' => 'WP - Working at Heights',
            'reach_truck' => 'LO - Order Picking License',
            'order_picker' => 'LO - Order Picking License',
        ];

        return [
            'branch_id' => Branch::factory(),
            'equipment_code' => strtoupper(fake()->bothify('EQ-####-??##')),
            'equipment_name' => $this->generateEquipmentName($type, $manufacturer),
            'equipment_type' => $type,
            'manufacturer' => $manufacturer,
            'model' => fake()->bothify('Model-###?'),
            'serial_number' => fake()->bothify('SN-########'),
            'status' => $status,
            'purchase_date' => fake()->dateTimeBetween('-5 years', '-6 months'),
            'purchase_price' => fake()->randomFloat(2, 500, 50000),
            'current_value' => fake()->randomFloat(2, 300, 40000),
            'location' => fake()->randomElement([
                'Main Warehouse', 'Workshop', 'Loading Bay 1', 'Loading Bay 2',
                'Storage Area A', 'Storage Area B', 'Maintenance Shop', 'Yard'
            ]),
            'load_rating' => in_array($type, ['forklift', 'pallet_jack', 'reach_truck', 'order_picker'])
                ? fake()->randomElement([1000, 1500, 2000, 2500, 3000, 5000])
                : null,
            'requires_license' => $requiresLicense,
            'license_type' => $requiresLicense ? $licenseTypes[$type] : null,
            'required_ppe_types' => json_encode($ppeByType[$type] ?? []),
            'last_inspection_date' => $status !== 'retired' ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'next_inspection_due' => $status !== 'retired' ? fake()->dateTimeBetween('-1 week', '+2 months') : null,
            'inspection_frequency_days' => fake()->randomElement([7, 14, 30, 90, 365]),
            'maintenance_due_date' => $status !== 'retired' ? fake()->optional(0.6)->dateTimeBetween('now', '+6 months') : null,
            'qr_code_path' => fake()->optional(0.5)->filePath(),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }

    protected function generateEquipmentName(string $type, string $manufacturer): string
    {
        $typeNames = [
            'forklift' => ['Counterbalance Forklift', 'Electric Forklift', 'Gas Forklift', 'Diesel Forklift'],
            'pallet_jack' => ['Electric Pallet Jack', 'Manual Pallet Jack', 'Powered Pallet Jack'],
            'scissor_lift' => ['Electric Scissor Lift', 'Rough Terrain Scissor Lift', 'Compact Scissor Lift'],
            'reach_truck' => ['Stand-Up Reach Truck', 'Double Deep Reach Truck', 'Narrow Aisle Reach'],
            'order_picker' => ['Low-Level Order Picker', 'Medium-Level Order Picker', 'High-Level Order Picker'],
            'hand_tools' => ['Hand Tool Set', 'Wrench Set', 'Screwdriver Set', 'Pliers Set'],
            'power_tools' => ['Cordless Drill', 'Impact Driver', 'Angle Grinder', 'Circular Saw'],
            'safety_equipment' => ['Safety Harness', 'Hard Hat', 'Safety Glasses', 'Ear Protection'],
            'racking' => ['Pallet Racking', 'Cantilever Racking', 'Drive-In Racking', 'Mezzanine Racking'],
            'conveyor' => ['Belt Conveyor', 'Roller Conveyor', 'Chain Conveyor', 'Gravity Conveyor'],
            'loading_dock' => ['Dock Leveler', 'Dock Shelter', 'Vehicle Restraint', 'Dock Light'],
            'other' => ['Warehouse Equipment', 'Material Handling Equipment'],
        ];

        $name = fake()->randomElement($typeNames[$type] ?? ['Equipment']);
        return "{$manufacturer} {$name}";
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'last_inspection_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'next_inspection_due' => fake()->dateTimeBetween('+1 week', '+2 months'),
        ]);
    }

    public function inUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_use',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'notes' => 'Currently undergoing scheduled maintenance',
        ]);
    }

    public function outOfService(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'out_of_service',
            'notes' => 'Out of service due to defects',
        ]);
    }

    public function retired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'retired',
            'last_inspection_date' => null,
            'next_inspection_due' => null,
            'maintenance_due_date' => null,
            'notes' => 'Equipment retired from service',
        ]);
    }

    public function inspectionOverdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_inspection_due' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
        ]);
    }

    public function maintenanceDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_due_date' => fake()->dateTimeBetween('-1 week', '+1 week'),
        ]);
    }

    public function forklift(): static
    {
        return $this->state(fn (array $attributes) => [
            'equipment_type' => 'forklift',
            'equipment_name' => $this->generateEquipmentName('forklift', $attributes['manufacturer']),
            'load_rating' => fake()->randomElement([1500, 2000, 2500, 3000, 5000]),
            'requires_license' => true,
            'license_type' => 'LF - Forklift License',
            'required_ppe_types' => json_encode(['high_vis_vest', 'steel_cap_boots', 'hard_hat']),
            'purchase_price' => fake()->randomFloat(2, 15000, 50000),
        ]);
    }

    public function powerTool(): static
    {
        return $this->state(fn (array $attributes) => [
            'equipment_type' => 'power_tools',
            'equipment_name' => $this->generateEquipmentName('power_tools', $attributes['manufacturer']),
            'load_rating' => null,
            'requires_license' => false,
            'license_type' => null,
            'required_ppe_types' => json_encode(['safety_glasses', 'gloves', 'hearing_protection']),
            'purchase_price' => fake()->randomFloat(2, 100, 2000),
        ]);
    }

    public function withQrCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'qr_code_path' => 'qr-codes/equipment-' . $attributes['equipment_code'] . '.png',
        ]);
    }
}
