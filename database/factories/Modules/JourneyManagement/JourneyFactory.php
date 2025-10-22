<?php

namespace Database\Factories\Modules\JourneyManagement;

use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class JourneyFactory extends Factory
{
    protected $model = \App\Modules\JourneyManagement\Models\Journey::class;

    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+7 days');
        $durationMinutes = $this->faker->numberBetween(60, 480);
        $endTime = (clone $startTime)->modify("+{$durationMinutes} minutes");

        return [
            'branch_id' => 1, // Will be overridden by tests
            'user_id' => User::factory(),
            'vehicle_id' => $this->faker->boolean(70) ? Vehicle::factory() : null,
            'title' => $this->faker->randomElement([
                'Site Visit - ' . $this->faker->city(),
                'Client Meeting - ' . $this->faker->company(),
                'Inspection - ' . $this->faker->streetName(),
                'Delivery - ' . $this->faker->city(),
                'Emergency Callout - ' . $this->faker->streetName(),
            ]),
            'purpose' => $this->faker->optional()->sentence(),
            'destination' => $this->faker->randomElement([
                $this->faker->city() . ' Construction Site',
                $this->faker->company() . ' Office',
                $this->faker->streetName() . ' Project',
                $this->faker->city() . ' Warehouse',
            ]),
            'destination_address' => $this->faker->address(),
            'destination_latitude' => $this->faker->latitude(-43, -28), // NSW/VIC region
            'destination_longitude' => $this->faker->longitude(141, 154), // NSW/VIC region
            'planned_route' => null,
            'estimated_distance_km' => $this->faker->numberBetween(5, 300),
            'estimated_duration_minutes' => $durationMinutes,
            'planned_start_time' => $startTime,
            'planned_end_time' => $endTime,
            'actual_start_time' => null,
            'actual_end_time' => null,
            'checkin_interval_minutes' => $this->faker->randomElement([15, 30, 60, 120, 240]),
            'last_checkin_time' => null,
            'next_checkin_due' => null,
            'checkin_overdue' => false,
            'emergency_contact_name' => $this->faker->optional()->name(),
            'emergency_contact_phone' => $this->faker->optional()->phoneNumber(),
            'hazards_identified' => $this->faker->optional()->paragraph(),
            'control_measures' => $this->faker->optional()->paragraph(),
            'status' => 'planned',
            'notes' => $this->faker->optional()->paragraph(),
            'completion_notes' => null,
        ];
    }

    /**
     * Journey in planned status
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planned',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'last_checkin_time' => null,
            'next_checkin_due' => null,
        ]);
    }

    /**
     * Journey in active status
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = now()->subHours(2);
            $nextCheckin = $startTime->copy()->addMinutes($attributes['checkin_interval_minutes']);

            return [
                'status' => 'active',
                'actual_start_time' => $startTime,
                'actual_end_time' => null,
                'last_checkin_time' => $startTime,
                'next_checkin_due' => $nextCheckin,
                'checkin_overdue' => false,
            ];
        });
    }

    /**
     * Journey with overdue check-in
     */
    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = now()->subHours(4);
            $lastCheckin = now()->subHours(2);
            $nextCheckin = $lastCheckin->copy()->addMinutes($attributes['checkin_interval_minutes']);

            return [
                'status' => 'active',
                'actual_start_time' => $startTime,
                'last_checkin_time' => $lastCheckin,
                'next_checkin_due' => $nextCheckin,
                'checkin_overdue' => true,
            ];
        });
    }

    /**
     * Journey in emergency status
     */
    public function emergency(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = now()->subHours(1);

            return [
                'status' => 'emergency',
                'actual_start_time' => $startTime,
                'last_checkin_time' => now()->subMinutes(15),
                'next_checkin_due' => now()->addMinutes($attributes['checkin_interval_minutes']),
                'checkin_overdue' => false,
            ];
        });
    }

    /**
     * Completed journey
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = now()->subHours(6);
            $endTime = now()->subHours(1);

            return [
                'status' => 'completed',
                'actual_start_time' => $startTime,
                'actual_end_time' => $endTime,
                'last_checkin_time' => $endTime,
                'next_checkin_due' => null,
                'checkin_overdue' => false,
                'completion_notes' => $this->faker->paragraph(),
            ];
        });
    }

    /**
     * Cancelled journey
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'last_checkin_time' => null,
            'next_checkin_due' => null,
        ]);
    }

    /**
     * Journey with hazards identified
     */
    public function withHazards(): static
    {
        return $this->state(fn (array $attributes) => [
            'hazards_identified' => implode("\n", [
                '- Remote location with limited cell coverage',
                '- Rough terrain and uneven ground',
                '- Extreme weather conditions possible',
                '- Working at heights',
            ]),
            'control_measures' => implode("\n", [
                '- Satellite phone for emergency communication',
                '- Full PPE including safety harness',
                '- Weather monitoring equipment',
                '- Emergency first aid kit',
                '- Check-in every 30 minutes',
            ]),
            'checkin_interval_minutes' => 30,
        ]);
    }

    /**
     * Journey with emergency contact
     */
    public function withEmergencyContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
        ]);
    }
}
