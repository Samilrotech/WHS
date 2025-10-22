<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Brisbane Branch',
                'Gold Coast Branch',
                'Sunshine Coast Branch',
                'Toowoomba Branch',
            ]),
            'code' => strtoupper($this->faker->unique()->lexify('BR-???')),
            'state' => $this->faker->randomElement(['QLD', 'NSW', 'VIC', 'SA', 'WA']),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postcode' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->companyEmail,
            'manager_name' => $this->faker->name,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
