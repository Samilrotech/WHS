<?php

namespace Database\Factories\Modules\TrainingManagement;

use App\Models\Branch;
use App\Models\User;
use App\Modules\TrainingManagement\Models\Certification;
use App\Modules\TrainingManagement\Models\TrainingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    public function definition(): array
    {
        $certificationTypes = [
            'driver_license',
            'forklift_license',
            'heavy_vehicle_license',
            'dangerous_goods_license',
            'work_at_heights',
            'confined_space',
            'first_aid',
            'cpd_certification',
            'load_securement',
            'fatigue_management',
            'other',
        ];

        $issueDate = $this->faker->dateTimeBetween('-5 years', '-1 month');
        $expiryDate = $this->faker->dateTimeBetween($issueDate, '+5 years');

        return [
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'training_record_id' => $this->faker->boolean(60) ? TrainingRecord::factory() : null,
            'certification_type' => $this->faker->randomElement($certificationTypes),
            'certification_number' => strtoupper($this->faker->bothify('CERT-#####-????')),
            'issuing_authority' => $this->faker->randomElement([
                'WorkSafe Victoria',
                'Safe Work Australia',
                'Transport NSW',
                'VicRoads',
                'High Risk Work License',
                'Australian Red Cross',
                'St John Ambulance',
                'TAFE NSW',
                'Industry Training Services',
                'National Heavy Vehicle Regulator',
            ]),
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'verification_status' => $this->faker->randomElement(['verified', 'pending', 'expired', 'suspended', 'revoked']),
            'verified_by_user_id' => $this->faker->boolean(70) ? User::factory() : null,
            'verified_date' => function (array $attributes) {
                return $attributes['verified_by_user_id'] ? $this->faker->dateTimeBetween($attributes['issue_date'], 'now') : null;
            },
            'license_classes' => function (array $attributes) {
                if (in_array($attributes['certification_type'], ['driver_license', 'heavy_vehicle_license'])) {
                    return json_encode($this->faker->randomElements(['C', 'LR', 'MR', 'HR', 'HC', 'MC'], $this->faker->numberBetween(1, 3)));
                }

                return null;
            },
            'restrictions' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
            'conditions' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'attachment_urls' => $this->faker->boolean(50) ? json_encode([
                'storage/certifications/'.$this->faker->uuid().'.pdf',
            ]) : null,
            'verification_notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
        ];
    }

    public function driverLicense(): static
    {
        $issueDate = $this->faker->dateTimeBetween('-10 years', '-1 year');
        $expiryDate = $this->faker->dateTimeBetween($issueDate, '+10 years');

        return $this->state(fn (array $attributes) => [
            'certification_type' => 'driver_license',
            'certification_number' => strtoupper($this->faker->bothify('DL-#######')),
            'issuing_authority' => $this->faker->randomElement(['VicRoads', 'Transport NSW', 'Queensland Transport']),
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'license_classes' => json_encode(['C']),
            'verification_status' => 'verified',
        ]);
    }

    public function heavyVehicleLicense(): static
    {
        $issueDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
        $expiryDate = $this->faker->dateTimeBetween($issueDate, '+5 years');

        return $this->state(fn (array $attributes) => [
            'certification_type' => 'heavy_vehicle_license',
            'certification_number' => strtoupper($this->faker->bothify('HV-#######')),
            'issuing_authority' => 'National Heavy Vehicle Regulator',
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'license_classes' => json_encode($this->faker->randomElements(['HR', 'HC', 'MC'], 2)),
            'verification_status' => 'verified',
        ]);
    }

    public function forkliftLicense(): static
    {
        $issueDate = $this->faker->dateTimeBetween('-3 years', '-1 month');
        $expiryDate = $this->faker->dateTimeBetween($issueDate, '+3 years');

        return $this->state(fn (array $attributes) => [
            'certification_type' => 'forklift_license',
            'certification_number' => strtoupper($this->faker->bothify('FL-######')),
            'issuing_authority' => 'High Risk Work License',
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'verification_status' => 'verified',
        ]);
    }

    public function firstAid(): static
    {
        $issueDate = $this->faker->dateTimeBetween('-2 years', '-1 month');
        $expiryDate = $this->faker->dateTimeBetween($issueDate, '+3 years');

        return $this->state(fn (array $attributes) => [
            'certification_type' => 'first_aid',
            'certification_number' => strtoupper($this->faker->bothify('FA-#######')),
            'issuing_authority' => $this->faker->randomElement(['Australian Red Cross', 'St John Ambulance']),
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'verification_status' => 'verified',
        ]);
    }

    public function workAtHeights(): static
    {
        $issueDate = $this->faker->dateTimeBetween('-4 years', '-1 month');
        $expiryDate = $this->faker->dateTimeBetween($issueDate, '+5 years');

        return $this->state(fn (array $attributes) => [
            'certification_type' => 'work_at_heights',
            'certification_number' => strtoupper($this->faker->bothify('WAH-######')),
            'issuing_authority' => 'WorkSafe Victoria',
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'verification_status' => 'verified',
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'verified',
            'verified_by_user_id' => User::factory(),
            'verified_date' => $this->faker->dateTimeBetween($attributes['issue_date'] ?? '-1 year', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'pending',
            'verified_by_user_id' => null,
            'verified_date' => null,
        ]);
    }

    public function expired(): static
    {
        $issueDate = $this->faker->dateTimeBetween('-6 years', '-4 years');
        $expiryDate = $this->faker->dateTimeBetween($issueDate, '-1 week');

        return $this->state(fn (array $attributes) => [
            'verification_status' => 'expired',
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
        ]);
    }

    public function expiringSoon(): static
    {
        $issueDate = $this->faker->dateTimeBetween('-3 years', '-1 year');
        $expiryDate = $this->faker->dateTimeBetween('now', '+30 days');

        return $this->state(fn (array $attributes) => [
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'verification_status' => 'verified',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'suspended',
            'verification_notes' => 'Suspended due to safety violation',
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'revoked',
            'verification_notes' => 'Certification revoked',
        ]);
    }

    public function withAttachment(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachment_urls' => json_encode([
                'storage/certifications/'.$this->faker->uuid().'.pdf',
                'storage/certifications/'.$this->faker->uuid().'.jpg',
            ]),
        ]);
    }
}
