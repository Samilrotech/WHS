<?php

namespace Database\Factories\Modules\VehicleManagement;

use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'branch_id' => null, // Must be set when using factory
            'registration_number' => strtoupper($this->faker->bothify('???###')),
            'registration_state' => $this->faker->randomElement(array_keys(config('vehicles.registration_states'))),
            'make' => $this->faker->randomElement(['Toyota', 'Ford', 'Nissan', 'Holden', 'Isuzu', 'Mazda']),
            'model' => $this->faker->randomElement(['Hilux', 'Ranger', 'Navara', 'Colorado', 'BT-50', 'Amarok']),
            'year' => $this->faker->numberBetween(2015, 2024),
            'vin_number' => strtoupper($this->faker->bothify('##???############')),
            'color' => $this->faker->randomElement(['White', 'Black', 'Silver', 'Blue', 'Red', 'Grey']),
            'fuel_type' => $this->faker->randomElement(['Diesel', 'Petrol', 'Hybrid', 'Electric']),
            'odometer_reading' => $this->faker->numberBetween(0, 200000),
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'purchase_price' => $this->faker->numberBetween(30000, 80000),
            'current_value' => null, // Calculated by service
            'depreciation_method' => $this->faker->randomElement(['straight_line', 'declining_balance']),
            'depreciation_rate' => $this->faker->randomElement([15, 20, 25]),
            'insurance_company' => $this->faker->randomElement(['NRMA', 'AAMI', 'Allianz', 'Budget Direct', 'RAC']),
            'insurance_policy_number' => 'POL' . $this->faker->numerify('######'),
            'insurance_expiry_date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'insurance_premium' => $this->faker->numberBetween(800, 2000),
            'rego_expiry_date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'inspection_due_date' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'inspection_frequency' => 'monthly',
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
            'qr_code_path' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function maintenance(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function sold(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sold',
        ]);
    }

    public function regoExpiringSoon(): self
    {
        return $this->state(fn (array $attributes) => [
            'rego_expiry_date' => now()->addDays(15)->format('Y-m-d'),
        ]);
    }

    public function insuranceExpiringSoon(): self
    {
        return $this->state(fn (array $attributes) => [
            'insurance_expiry_date' => now()->addDays(15)->format('Y-m-d'),
        ]);
    }

    public function inspectionDueSoon(): self
    {
        return $this->state(fn (array $attributes) => [
            'inspection_due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);
    }

    public function withFinancials(): self
    {
        return $this->state(fn (array $attributes) => [
            'purchase_date' => now()->subYears(2)->format('Y-m-d'),
            'purchase_price' => 50000,
            'depreciation_method' => 'straight_line',
            'depreciation_rate' => 20,
        ]);
    }
}
