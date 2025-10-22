<?php

namespace Database\Factories\Modules\JourneyManagement;

use App\Modules\JourneyManagement\Models\Journey;
use Illuminate\Database\Eloquent\Factories\Factory;

class JourneyCheckpointFactory extends Factory
{
    protected $model = \App\Modules\JourneyManagement\Models\JourneyCheckpoint::class;

    public function definition(): array
    {
        return [
            'journey_id' => Journey::factory(),
            'checkin_time' => now(),
            'latitude' => $this->faker->latitude(-43, -28), // NSW/VIC region
            'longitude' => $this->faker->longitude(141, 154), // NSW/VIC region
            'location_name' => $this->faker->optional()->randomElement([
                $this->faker->city() . ' Rest Stop',
                $this->faker->streetName() . ' Junction',
                $this->faker->company() . ' Site',
                $this->faker->city() . ' Checkpoint',
            ]),
            'type' => 'manual',
            'status' => 'ok',
            'notes' => $this->faker->optional()->sentence(),
            'issues_reported' => null,
            'photo_paths' => null,
        ];
    }

    /**
     * Scheduled checkpoint
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'scheduled',
            'status' => 'ok',
        ]);
    }

    /**
     * Manual checkpoint
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'manual',
            'status' => 'ok',
        ]);
    }

    /**
     * Automatic checkpoint
     */
    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'automatic',
            'status' => 'ok',
            'notes' => 'Automatic GPS check-in',
        ]);
    }

    /**
     * Missed checkpoint
     */
    public function missed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'missed',
            'status' => 'ok',
            'notes' => 'System-detected missed check-in',
        ]);
    }

    /**
     * Emergency checkpoint
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'emergency',
            'status' => 'emergency',
            'notes' => 'Emergency assistance requested',
            'issues_reported' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Checkpoint requiring assistance
     */
    public function assistanceNeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assistance_needed',
            'issues_reported' => $this->faker->randomElement([
                'Vehicle breakdown - need tow truck',
                'Flat tire - need assistance',
                'Delayed arrival - traffic accident',
                'Weather conditions deteriorating',
            ]),
        ]);
    }

    /**
     * Checkpoint with photos
     */
    public function withPhotos(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo_paths' => [
                'journeys/checkpoints/' . $this->faker->uuid() . '.jpg',
                'journeys/checkpoints/' . $this->faker->uuid() . '.jpg',
            ],
        ]);
    }
}
