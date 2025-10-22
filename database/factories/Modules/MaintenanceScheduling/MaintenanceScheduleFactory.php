<?php

namespace Database\Factories\Modules\MaintenanceScheduling;

use App\Models\Branch;
use App\Models\User;
use App\Modules\MaintenanceScheduling\Models\MaintenanceSchedule;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceScheduleFactory extends Factory
{
    protected $model = MaintenanceSchedule::class;

    public function definition(): array
    {
        $recurrenceType = $this->faker->randomElement([
            'monthly', 'quarterly', 'semi_annual', 'annual', 'odometer_based'
        ]);

        $scheduleType = $this->faker->randomElement(['preventive', 'predictive', 'corrective']);

        return [
            'branch_id' => Branch::factory(),
            'vehicle_id' => Vehicle::factory(),
            'created_by_user_id' => User::factory(),

            'schedule_name' => $this->getScheduleName($scheduleType),
            'description' => $this->faker->sentence(12),
            'schedule_type' => $scheduleType,
            'recurrence_type' => $recurrenceType,
            'recurrence_interval' => $recurrenceType === 'monthly' ? $this->faker->numberBetween(1, 6) : 1,
            'odometer_interval' => $recurrenceType === 'odometer_based' ? $this->faker->randomElement([5000, 10000, 15000, 20000]) : null,
            'engine_hours_interval' => null,

            'start_date' => $startDate = $this->faker->dateTimeBetween('-2 years', '-1 month'),
            'next_due_date' => $this->faker->dateTimeBetween('now', '+3 months'),

            'estimated_cost_per_service' => $this->faker->randomFloat(2, 100, 2000),
            'preferred_vendor' => $this->faker->randomElement(['AutoPro Services', 'Fleet Maintenance Co', 'Quick Service Centre', null]),
            'vendor_contact' => $this->faker->randomElement([$this->faker->phoneNumber, null]),

            'required_parts' => $this->faker->randomElement([
                json_encode(['Oil Filter', 'Air Filter', 'Engine Oil']),
                json_encode(['Brake Pads', 'Brake Fluid']),
                json_encode(['Coolant', 'Radiator Hose']),
                null
            ]),
            'auto_order_parts' => $this->faker->boolean(30),

            'reminder_days_before' => $this->faker->randomElement([7, 14, 30]),
            'email_notifications' => true,
            'sms_notifications' => $this->faker->boolean(40),

            'status' => 'active',
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),

            'completed_count' => 0,
            'last_completed_date' => null,
            'actual_total_cost' => 0.00,

            'notes' => $this->faker->randomElement([$this->faker->sentence(), null]),
        ];
    }

    /**
     * Active schedule state
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_due_date' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
        ]);
    }

    /**
     * Overdue schedule state
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_due_date' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
            'priority' => 'high',
        ]);
    }

    /**
     * Due soon (next 7 days)
     */
    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_due_date' => $this->faker->dateTimeBetween('now', '+7 days'),
            'priority' => $this->faker->randomElement(['medium', 'high']),
        ]);
    }

    /**
     * Paused schedule
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
            'notes' => 'Vehicle temporarily out of service - awaiting parts',
        ]);
    }

    /**
     * Preventive maintenance schedule
     */
    public function preventive(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'preventive',
            'schedule_name' => $this->faker->randomElement([
                'Monthly Oil Change & Service',
                'Quarterly Safety Inspection',
                'Annual Major Service',
                'Brake System Inspection'
            ]),
            'priority' => 'medium',
        ]);
    }

    /**
     * Predictive maintenance schedule
     */
    public function predictive(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'predictive',
            'schedule_name' => $this->faker->randomElement([
                'Engine Performance Monitoring',
                'Vibration Analysis Check',
                'Fluid Analysis Service'
            ]),
            'priority' => 'high',
        ]);
    }

    /**
     * Critical priority schedule
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
            'schedule_name' => 'Critical Safety System Check',
            'required_parts' => json_encode(['Brake Pads', 'Brake Fluid', 'Brake Lines']),
            'auto_order_parts' => true,
        ]);
    }

    /**
     * With completion history
     */
    public function withHistory(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_count' => $count = $this->faker->numberBetween(3, 20),
            'last_completed_date' => $this->faker->dateTimeBetween('-2 months', '-1 week'),
            'actual_total_cost' => $count * $this->faker->randomFloat(2, 150, 800),
        ]);
    }

    /**
     * Odometer-based schedule
     */
    public function odometerBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'recurrence_type' => 'odometer_based',
            'odometer_interval' => $this->faker->randomElement([5000, 10000, 15000, 20000]),
            'schedule_name' => 'Oil Change - Every 10,000km',
            'next_due_date' => null, // Calculated based on odometer
        ]);
    }

    /**
     * Monthly schedule
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'recurrence_type' => 'monthly',
            'recurrence_interval' => 1,
            'schedule_name' => 'Monthly Inspection',
        ]);
    }

    /**
     * Quarterly schedule
     */
    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'recurrence_type' => 'quarterly',
            'recurrence_interval' => 1,
            'schedule_name' => 'Quarterly Major Service',
        ]);
    }

    /**
     * With auto-order parts enabled
     */
    public function autoOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_order_parts' => true,
            'required_parts' => json_encode(['Oil Filter', 'Air Filter', 'Engine Oil', 'Fuel Filter']),
            'preferred_vendor' => 'AutoPro Services',
            'vendor_contact' => $this->faker->phoneNumber,
        ]);
    }

    protected function getScheduleName(string $type): string
    {
        $names = [
            'preventive' => [
                'Regular Oil Change',
                'Brake System Check',
                'Tire Rotation & Balance',
                'Air Filter Replacement',
                'Coolant Flush',
                'Battery Inspection',
            ],
            'predictive' => [
                'Engine Diagnostics',
                'Transmission Fluid Analysis',
                'Vibration Monitoring',
                'Temperature Sensor Check',
            ],
            'corrective' => [
                'Engine Repair Follow-up',
                'Transmission Service',
                'Suspension Repair Check',
                'Electrical System Repair',
            ],
        ];

        return $this->faker->randomElement($names[$type]);
    }
}
