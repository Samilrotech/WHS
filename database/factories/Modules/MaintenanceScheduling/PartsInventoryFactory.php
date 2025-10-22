<?php

namespace Database\Factories\Modules\MaintenanceScheduling;

use App\Models\Branch;
use App\Modules\MaintenanceScheduling\Models\PartsInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartsInventoryFactory extends Factory
{
    protected $model = PartsInventory::class;

    public function definition(): array
    {
        $category = $this->faker->randomElement([
            'filters', 'fluids', 'brakes', 'electrical', 'tires', 'belts_hoses', 'wipers', 'other'
        ]);

        $quantity = $this->faker->numberBetween(0, 100);
        $reorderPoint = $this->faker->numberBetween(5, 20);

        return [
            'branch_id' => Branch::factory(),

            'part_number' => $this->generatePartNumber($category),
            'part_name' => $this->getPartName($category),
            'description' => $this->faker->randomElement([$this->faker->sentence(), null]),
            'part_category' => $category,

            'quantity_on_hand' => $quantity,
            'reorder_point' => $reorderPoint,
            'reorder_quantity' => $this->faker->numberBetween(10, 50),
            'minimum_stock_level' => $this->faker->numberBetween(3, 10),
            'maximum_stock_level' => $this->faker->numberBetween(50, 200),

            'unit_cost' => $this->faker->randomFloat(2, 10, 500),
            'selling_price' => $this->faker->randomFloat(2, 15, 600),
            'currency' => 'AUD',

            'supplier_name' => $this->faker->randomElement(['AutoParts Direct', 'Fleet Supplies Co', 'Pro Parts Australia', null]),
            'supplier_part_number' => $this->faker->randomElement([$this->faker->bothify('SUP-####'), null]),
            'supplier_contact' => $this->faker->randomElement([$this->faker->phoneNumber, null]),
            'lead_time_days' => $this->faker->numberBetween(1, 14),

            'storage_location' => $this->faker->randomElement(['Warehouse A', 'Warehouse B', 'Workshop', null]),
            'storage_bin' => $this->faker->randomElement(['A1', 'B2', 'C3', 'D4', null]),

            'compatible_vehicles' => $this->faker->randomElement([
                json_encode(['Toyota Hilux', 'Ford Ranger']),
                json_encode(['All vehicles']),
                null
            ]),

            'status' => $quantity === 0 ? 'out_of_stock' : ($quantity <= $reorderPoint ? 'pending_order' : 'active'),

            'last_restocked_date' => $this->faker->randomElement([$this->faker->dateTimeBetween('-6 months', 'now'), null]),
            'units_consumed_last_30_days' => $this->faker->numberBetween(0, 50),
            'average_monthly_usage' => $this->faker->randomFloat(2, 1, 20),

            'critical_part' => $this->faker->boolean(20),
            'quality_grade' => $this->faker->randomElement(['OEM', 'Aftermarket', 'Generic', null]),
        ];
    }

    /**
     * In stock state
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $this->faker->numberBetween(50, 200),
            'status' => 'active',
        ]);
    }

    /**
     * Low stock state
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $qty = $this->faker->numberBetween(1, 10),
            'reorder_point' => $this->faker->numberBetween($qty, 15),
            'status' => 'pending_order',
        ]);
    }

    /**
     * Out of stock state
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => 0,
            'status' => 'out_of_stock',
        ]);
    }

    /**
     * Critical part
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'critical_part' => true,
            'minimum_stock_level' => $this->faker->numberBetween(10, 20),
            'reorder_point' => $this->faker->numberBetween(15, 30),
        ]);
    }

    /**
     * High usage part
     */
    public function highUsage(): static
    {
        return $this->state(fn (array $attributes) => [
            'average_monthly_usage' => $this->faker->randomFloat(2, 20, 50),
            'reorder_quantity' => $this->faker->numberBetween(50, 100),
        ]);
    }

    /**
     * Filters category
     */
    public function filters(): static
    {
        return $this->state(fn (array $attributes) => [
            'part_category' => 'filters',
            'part_number' => 'FLT-' . $this->faker->numberBetween(1000, 9999),
            'part_name' => $this->faker->randomElement([
                'Oil Filter - Standard',
                'Air Filter - High Flow',
                'Fuel Filter - Diesel',
                'Cabin Air Filter',
            ]),
        ]);
    }

    /**
     * Fluids category
     */
    public function fluids(): static
    {
        return $this->state(fn (array $attributes) => [
            'part_category' => 'fluids',
            'part_number' => 'FLD-' . $this->faker->numberBetween(1000, 9999),
            'part_name' => $this->faker->randomElement([
                'Engine Oil 5W-30 (5L)',
                'Coolant - Long Life',
                'Brake Fluid DOT 4',
                'Transmission Fluid ATF',
            ]),
        ]);
    }

    /**
     * Brakes category
     */
    public function brakes(): static
    {
        return $this->state(fn (array $attributes) => [
            'part_category' => 'brakes',
            'part_number' => 'BRK-' . $this->faker->numberBetween(1000, 9999),
            'part_name' => $this->faker->randomElement([
                'Brake Pads - Front Set',
                'Brake Pads - Rear Set',
                'Brake Disc Rotor',
                'Brake Caliper Assembly',
            ]),
            'critical_part' => true,
        ]);
    }

    /**
     * Electrical category
     */
    public function electrical(): static
    {
        return $this->state(fn (array $attributes) => [
            'part_category' => 'electrical',
            'part_number' => 'ELC-' . $this->faker->numberBetween(1000, 9999),
            'part_name' => $this->faker->randomElement([
                'Battery 12V 70AH',
                'Alternator Belt',
                'Spark Plug Set',
                'Headlight Bulb H4',
            ]),
        ]);
    }

    /**
     * Tires category
     */
    public function tires(): static
    {
        return $this->state(fn (array $attributes) => [
            'part_category' => 'tires',
            'part_number' => 'TYR-' . $this->faker->numberBetween(1000, 9999),
            'part_name' => $this->faker->randomElement([
                'All Terrain Tire 265/70R17',
                'Highway Tire 245/65R17',
                'Mud Terrain Tire 285/75R16',
            ]),
            'unit_cost' => $this->faker->randomFloat(2, 150, 400),
        ]);
    }

    /**
     * With supplier details
     */
    public function withSupplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier_name' => 'AutoParts Direct',
            'supplier_contact' => $this->faker->phoneNumber,
            'supplier_part_number' => $this->faker->bothify('SUP-####-????'),
            'last_purchase_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'last_purchase_cost' => $this->faker->randomFloat(2, 10, 500),
        ]);
    }

    /**
     * Needs reorder
     */
    public function needsReorder(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_on_hand' => $qty = $this->faker->numberBetween(1, 8),
            'reorder_point' => $this->faker->numberBetween($qty + 1, 15),
            'status' => 'pending_order',
        ]);
    }

    /**
     * Fast moving part
     */
    public function fastMoving(): static
    {
        return $this->state(fn (array $attributes) => [
            'average_monthly_usage' => $this->faker->randomFloat(2, 30, 100),
            'quantity_on_hand' => $this->faker->numberBetween(50, 150),
            'reorder_point' => $this->faker->numberBetween(40, 60),
            'reorder_quantity' => $this->faker->numberBetween(80, 150),
        ]);
    }

    /**
     * Slow moving part
     */
    public function slowMoving(): static
    {
        return $this->state(fn (array $attributes) => [
            'average_monthly_usage' => $this->faker->randomFloat(2, 0.5, 3),
            'quantity_on_hand' => $this->faker->numberBetween(20, 80),
            'reorder_point' => $this->faker->numberBetween(5, 10),
        ]);
    }

    /**
     * Expensive part
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_cost' => $this->faker->randomFloat(2, 500, 2000),
            'minimum_stock_level' => $this->faker->numberBetween(1, 3),
            'maximum_stock_level' => $this->faker->numberBetween(5, 15),
        ]);
    }

    protected function generatePartNumber(string $category): string
    {
        $prefixes = [
            'filters' => 'FLT',
            'fluids' => 'FLD',
            'brakes' => 'BRK',
            'electrical' => 'ELC',
            'tires' => 'TYR',
            'belts_hoses' => 'BLT',
            'wipers' => 'WPR',
            'other' => 'OTH',
        ];

        $prefix = $prefixes[$category] ?? 'OTH';
        return $prefix . '-' . $this->faker->numberBetween(1000, 9999);
    }

    protected function getPartName(string $category): string
    {
        $names = [
            'filters' => ['Oil Filter', 'Air Filter', 'Fuel Filter', 'Cabin Filter'],
            'fluids' => ['Engine Oil 5W-30', 'Coolant', 'Brake Fluid', 'Transmission Fluid'],
            'brakes' => ['Brake Pads Front', 'Brake Pads Rear', 'Brake Rotor', 'Brake Caliper'],
            'electrical' => ['Battery 12V', 'Alternator', 'Spark Plugs', 'Headlight Bulb'],
            'tires' => ['All Terrain Tire', 'Highway Tire', 'Mud Tire'],
            'belts_hoses' => ['Serpentine Belt', 'Timing Belt', 'Radiator Hose', 'Coolant Hose'],
            'wipers' => ['Wiper Blade Front', 'Wiper Blade Rear'],
            'other' => ['Miscellaneous Part'],
        ];

        return $this->faker->randomElement($names[$category]);
    }
}
