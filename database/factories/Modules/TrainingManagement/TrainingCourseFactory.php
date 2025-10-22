<?php

namespace Database\Factories\Modules\TrainingManagement;

use App\Models\Branch;
use App\Models\User;
use App\Modules\TrainingManagement\Models\TrainingCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingCourseFactory extends Factory
{
    protected $model = TrainingCourse::class;

    public function definition(): array
    {
        $categories = [
            'safety_induction',
            'driver_training',
            'vehicle_operation',
            'load_securement',
            'fatigue_management',
            'emergency_response',
            'hazmat_handling',
            'forklift_operation',
            'manual_handling',
            'first_aid',
            'whs_compliance',
            'other',
        ];

        $deliveryMethods = ['online', 'classroom', 'hands_on', 'blended'];
        $frequencies = ['once', 'annual', 'biennial', 'triennial', 'custom'];

        return [
            'branch_id' => Branch::factory(),
            'course_code' => strtoupper($this->faker->bothify('TRN-###??')),
            'course_name' => $this->faker->randomElement([
                'Safety Induction Training',
                'Driver Fatigue Management',
                'Forklift Operation Basics',
                'Heavy Vehicle Operations',
                'Load Securement Certification',
                'Emergency Response Procedures',
                'Hazmat Handling & Transport',
                'Manual Handling Techniques',
                'First Aid & CPR',
                'WHS Compliance Essentials',
            ]),
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->paragraph(3),
            'delivery_method' => $this->faker->randomElement($deliveryMethods),
            'duration_hours' => $this->faker->randomElement([2, 4, 8, 16, 24, 40]),
            'validity_months' => $this->faker->randomElement([12, 24, 36, 60, null]),
            'is_cpd_accredited' => $this->faker->boolean(30),
            'cpd_points' => function (array $attributes) {
                return $attributes['is_cpd_accredited'] ? $this->faker->numberBetween(5, 20) : null;
            },
            'is_mandatory' => $this->faker->boolean(40),
            'pass_score' => $this->faker->randomElement([70, 75, 80, 85, null]),
            'assessment_format' => $this->faker->randomElement(['multiple_choice', 'practical', 'written', 'combination', null]),
            'frequency' => $this->faker->randomElement($frequencies),
            'recurrence_interval_months' => function (array $attributes) {
                if ($attributes['frequency'] === 'annual') {
                    return 12;
                }
                if ($attributes['frequency'] === 'biennial') {
                    return 24;
                }
                if ($attributes['frequency'] === 'triennial') {
                    return 36;
                }

                return null;
            },
            'cost_per_person' => $this->faker->randomFloat(2, 50, 500),
            'max_participants' => $this->faker->randomElement([10, 15, 20, 25, null]),
            'prerequisites' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
            'learning_outcomes' => $this->faker->boolean(80) ? json_encode([
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence(),
            ]) : null,
            'status' => $this->faker->randomElement(['active', 'draft', 'archived']),
            'created_by_user_id' => User::factory(),
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

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function mandatory(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandatory' => true,
        ]);
    }

    public function cpdAccredited(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cpd_accredited' => true,
            'cpd_points' => $this->faker->numberBetween(5, 20),
        ]);
    }

    public function safetyInduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_code' => 'TRN-INDUCT',
            'course_name' => 'Safety Induction Training',
            'category' => 'safety_induction',
            'delivery_method' => 'classroom',
            'duration_hours' => 4,
            'is_mandatory' => true,
            'validity_months' => 12,
        ]);
    }

    public function driverTraining(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_code' => 'TRN-DRIVER',
            'course_name' => 'Heavy Vehicle Driver Training',
            'category' => 'driver_training',
            'delivery_method' => 'blended',
            'duration_hours' => 40,
            'is_mandatory' => true,
            'validity_months' => 24,
            'pass_score' => 80,
        ]);
    }

    public function forkliftOperation(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_code' => 'TRN-FORK',
            'course_name' => 'Forklift Operation & Safety',
            'category' => 'forklift_operation',
            'delivery_method' => 'hands_on',
            'duration_hours' => 16,
            'is_mandatory' => true,
            'validity_months' => 36,
            'pass_score' => 85,
        ]);
    }

    public function firstAid(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_code' => 'TRN-FA',
            'course_name' => 'First Aid & CPR Certification',
            'category' => 'first_aid',
            'delivery_method' => 'hands_on',
            'duration_hours' => 8,
            'is_cpd_accredited' => true,
            'cpd_points' => 10,
            'validity_months' => 36,
            'pass_score' => 80,
        ]);
    }

    public function fatigueManagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_code' => 'TRN-FATIGUE',
            'course_name' => 'Fatigue Management for Drivers',
            'category' => 'fatigue_management',
            'delivery_method' => 'online',
            'duration_hours' => 4,
            'is_mandatory' => true,
            'validity_months' => 12,
            'pass_score' => 75,
        ]);
    }
}
