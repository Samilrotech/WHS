<?php

namespace Database\Factories\Modules\VehicleManagement;

use App\Modules\VehicleManagement\Models\VehicleAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VehicleAssignmentFactory extends Factory
{
    protected $model = VehicleAssignment::class;

    public function definition(): array
    {
        $assignedDate = $this->faker->dateTimeBetween('-1 month', 'now');

        return [
            'id' => Str::uuid(),
            'vehicle_id' => null,
            'user_id' => null,
            'assigned_date' => $assignedDate->format('Y-m-d'),
            'returned_date' => null,
            'odometer_start' => $this->faker->numberBetween(10000, 250000),
            'odometer_end' => null,
            'purpose' => $this->faker->sentence(6),
            'notes' => $this->faker->optional()->sentence(8),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'returned_date' => null,
        ]);
    }

    public function returned(): static
    {
        return $this->state(function () {
            $endDate = $this->faker->dateTimeBetween('-5 days', 'now');
            $start = $this->faker->dateTimeBetween('-2 months', $endDate);

            return [
                'assigned_date' => $start->format('Y-m-d'),
                'returned_date' => $endDate->format('Y-m-d'),
                'odometer_end' => $this->faker->numberBetween(20000, 300000),
            ];
        });
    }
}
