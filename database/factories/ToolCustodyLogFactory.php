<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use App\Modules\WarehouseEquipment\Models\ToolCustodyLog;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ToolCustodyLogFactory extends Factory
{
    protected $model = ToolCustodyLog::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['checked_out', 'returned', 'overdue', 'lost']);
        $checkedOutAt = fake()->dateTimeBetween('-2 months', 'now');
        $expectedReturn = fake()->dateTimeBetween($checkedOutAt, '+2 weeks');

        $isReturned = $status === 'returned';
        $isOverdue = $status === 'overdue';
        $daysOverdue = $isOverdue ? fake()->numberBetween(1, 30) : 0;

        $checkoutCondition = fake()->randomElement(['excellent', 'good', 'fair', 'poor', 'damaged']);

        return [
            'branch_id' => Branch::factory(),
            'equipment_id' => WarehouseEquipment::factory(),
            'custodian_user_id' => User::factory(),
            'checked_out_at' => $checkedOutAt,
            'expected_return_date' => $expectedReturn,
            'checked_in_at' => $isReturned
                ? fake()->dateTimeBetween($checkedOutAt, $expectedReturn)
                : null,
            'condition_on_checkout' => $checkoutCondition,
            'condition_on_checkin' => $isReturned
                ? fake()->randomElement(['excellent', 'good', 'fair', 'poor', 'damaged'])
                : null,
            'status' => $status,
            'is_overdue' => $isOverdue,
            'days_overdue' => $daysOverdue,
            'purpose' => fake()->optional(0.7)->sentence(),
            'checkout_notes' => fake()->optional(0.3)->sentence(),
            'checkin_notes' => $isReturned ? fake()->optional(0.4)->sentence() : null,
            'damage_reported' => $isReturned ? fake()->boolean(10) : false,
            'damage_description' => $isReturned && fake()->boolean(10)
                ? fake()->sentence()
                : null,
        ];
    }

    public function checkedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'checked_out',
            'checked_out_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'expected_return_date' => fake()->dateTimeBetween('now', '+1 week'),
            'checked_in_at' => null,
            'condition_on_checkin' => null,
            'is_overdue' => false,
            'days_overdue' => 0,
            'damage_reported' => false,
        ]);
    }

    public function returned(): static
    {
        $checkedOutAt = fake()->dateTimeBetween('-1 month', '-1 week');
        $expectedReturn = fake()->dateTimeBetween($checkedOutAt, 'now');
        $checkedInAt = fake()->dateTimeBetween($checkedOutAt, $expectedReturn);

        return $this->state(fn (array $attributes) => [
            'status' => 'returned',
            'checked_out_at' => $checkedOutAt,
            'expected_return_date' => $expectedReturn,
            'checked_in_at' => $checkedInAt,
            'condition_on_checkin' => fake()->randomElement(['excellent', 'good', 'fair']),
            'is_overdue' => false,
            'days_overdue' => 0,
            'damage_reported' => false,
            'checkin_notes' => fake()->optional(0.5)->sentence(),
        ]);
    }

    public function overdue(): static
    {
        $checkedOutAt = fake()->dateTimeBetween('-2 months', '-2 weeks');
        $expectedReturn = fake()->dateTimeBetween($checkedOutAt, '-1 week');
        $daysOverdue = now()->diffInDays($expectedReturn);

        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'checked_out_at' => $checkedOutAt,
            'expected_return_date' => $expectedReturn,
            'checked_in_at' => null,
            'condition_on_checkin' => null,
            'is_overdue' => true,
            'days_overdue' => $daysOverdue,
            'damage_reported' => false,
        ]);
    }

    public function lost(): static
    {
        $checkedOutAt = fake()->dateTimeBetween('-6 months', '-3 months');
        $expectedReturn = fake()->dateTimeBetween($checkedOutAt, '-2 months');

        return $this->state(fn (array $attributes) => [
            'status' => 'lost',
            'checked_out_at' => $checkedOutAt,
            'expected_return_date' => $expectedReturn,
            'checked_in_at' => null,
            'condition_on_checkin' => null,
            'is_overdue' => true,
            'days_overdue' => now()->diffInDays($expectedReturn),
            'checkin_notes' => 'Equipment reported as lost',
        ]);
    }

    public function excellentCondition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_on_checkout' => 'excellent',
            'condition_on_checkin' => $attributes['checked_in_at'] ? 'excellent' : null,
        ]);
    }

    public function goodCondition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_on_checkout' => 'good',
            'condition_on_checkin' => $attributes['checked_in_at'] ? 'good' : null,
        ]);
    }

    public function fairCondition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_on_checkout' => 'fair',
            'condition_on_checkin' => $attributes['checked_in_at']
                ? fake()->randomElement(['fair', 'poor'])
                : null,
        ]);
    }

    public function poorCondition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_on_checkout' => 'poor',
            'condition_on_checkin' => $attributes['checked_in_at']
                ? fake()->randomElement(['poor', 'damaged'])
                : null,
            'checkout_notes' => 'Equipment condition noted as poor during checkout',
        ]);
    }

    public function damagedCondition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_on_checkout' => 'damaged',
            'checkout_notes' => 'Pre-existing damage noted during checkout',
        ]);
    }

    public function withDamage(): static
    {
        return $this->state(fn (array $attributes) => [
            'damage_reported' => true,
            'damage_description' => fake()->sentence(),
            'condition_on_checkin' => 'damaged',
            'checkin_notes' => 'Damage found during checkin inspection',
        ]);
    }

    public function overdueByDays(int $days): static
    {
        $checkedOutAt = now()->subDays($days + 7);
        $expectedReturn = now()->subDays($days);

        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'checked_out_at' => $checkedOutAt,
            'expected_return_date' => $expectedReturn,
            'checked_in_at' => null,
            'is_overdue' => true,
            'days_overdue' => $days,
        ]);
    }

    public function returnedLate(): static
    {
        $checkedOutAt = fake()->dateTimeBetween('-1 month', '-2 weeks');
        $expectedReturn = fake()->dateTimeBetween($checkedOutAt, '-1 week');
        $checkedInAt = fake()->dateTimeBetween($expectedReturn, 'now');

        return $this->state(fn (array $attributes) => [
            'status' => 'returned',
            'checked_out_at' => $checkedOutAt,
            'expected_return_date' => $expectedReturn,
            'checked_in_at' => $checkedInAt,
            'condition_on_checkin' => fake()->randomElement(['excellent', 'good', 'fair']),
            'is_overdue' => false,
            'days_overdue' => 0,
            'checkin_notes' => 'Returned late but no penalties applied',
        ]);
    }

    public function withPurpose(string $purpose): static
    {
        return $this->state(fn (array $attributes) => [
            'purpose' => $purpose,
        ]);
    }

    public function forkliftCheckout(): static
    {
        return $this->state(fn (array $attributes) => [
            'equipment_id' => WarehouseEquipment::factory()->forklift(),
            'purpose' => 'Loading/unloading shipment',
            'checkout_notes' => 'Forklift license verified',
        ]);
    }

    public function powerToolCheckout(): static
    {
        return $this->state(fn (array $attributes) => [
            'equipment_id' => WarehouseEquipment::factory()->powerTool(),
            'purpose' => 'Maintenance and repairs',
            'expected_return_date' => now()->addDays(fake()->numberBetween(1, 3)),
        ]);
    }
}
