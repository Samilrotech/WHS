<?php

namespace Database\Factories\Modules\TrainingManagement;

use App\Models\Branch;
use App\Models\User;
use App\Modules\TrainingManagement\Models\TrainingCourse;
use App\Modules\TrainingManagement\Models\TrainingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingRecordFactory extends Factory
{
    protected $model = TrainingRecord::class;

    public function definition(): array
    {
        $assignedDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $hasStarted = $this->faker->boolean(70);
        $commencedDate = $hasStarted ? $this->faker->dateTimeBetween($assignedDate, 'now') : null;
        $hasCompleted = $hasStarted && $this->faker->boolean(60);
        $completedDate = $hasCompleted ? $this->faker->dateTimeBetween($commencedDate ?? $assignedDate, 'now') : null;

        $status = $this->determineStatus($hasStarted, $hasCompleted, $completedDate);
        $completionPercentage = $this->determineCompletionPercentage($status);

        $assessmentScore = null;
        $assessmentPassed = null;
        if ($hasCompleted) {
            $assessmentScore = $this->faker->randomFloat(2, 40, 100);
            $assessmentPassed = $assessmentScore >= 70;
        }

        return [
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'course_id' => TrainingCourse::factory(),
            'status' => $status,
            'assigned_by_user_id' => User::factory(),
            'assigned_date' => $assignedDate,
            'due_date' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween($assignedDate, '+3 months') : null,
            'commenced_date' => $commencedDate,
            'completed_date' => $completedDate,
            'completion_percentage' => $completionPercentage,
            'assessment_score' => $assessmentScore,
            'assessment_passed' => $assessmentPassed,
            'assessment_attempts' => $hasCompleted ? $this->faker->numberBetween(1, 3) : null,
            'time_spent_minutes' => $hasStarted ? $this->faker->numberBetween(30, 480) : null,
            'effectiveness_rating' => $hasCompleted ? $this->faker->numberBetween(1, 5) : null,
            'participant_feedback' => $hasCompleted && $this->faker->boolean(60) ? $this->faker->paragraph() : null,
            'knowledge_demonstrated' => $hasCompleted ? $this->faker->boolean(80) : null,
            'supervisor_comments' => $hasCompleted && $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'certificate_number' => $hasCompleted && $assessmentPassed ? strtoupper($this->faker->bothify('CERT-####-????')) : null,
            'certificate_issued_date' => $hasCompleted && $assessmentPassed ? $completedDate : null,
        ];
    }

    protected function determineStatus(bool $hasStarted, bool $hasCompleted, $completedDate): string
    {
        if ($hasCompleted) {
            // Randomly assign passed/failed based on assessment score
            return $this->faker->randomElement(['passed', 'failed', 'completed']);
        }
        if ($hasStarted) {
            return 'in_progress';
        }

        return 'assigned';
    }

    protected function determineCompletionPercentage(string $status): int
    {
        return match ($status) {
            'passed', 'completed' => 100,
            'failed' => $this->faker->numberBetween(50, 99),
            'in_progress' => $this->faker->numberBetween(10, 90),
            default => 0,
        };
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
            'commenced_date' => null,
            'completed_date' => null,
            'completion_percentage' => 0,
            'assessment_score' => null,
            'assessment_passed' => null,
        ]);
    }

    public function inProgress(): static
    {
        $commencedDate = $this->faker->dateTimeBetween('-2 weeks', 'now');

        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'commenced_date' => $commencedDate,
            'completed_date' => null,
            'completion_percentage' => $this->faker->numberBetween(10, 90),
            'time_spent_minutes' => $this->faker->numberBetween(30, 300),
            'assessment_score' => null,
            'assessment_passed' => null,
        ]);
    }

    public function completed(): static
    {
        $assignedDate = $this->faker->dateTimeBetween('-2 months', '-1 month');
        $commencedDate = $this->faker->dateTimeBetween($assignedDate, '-3 weeks');
        $completedDate = $this->faker->dateTimeBetween($commencedDate, 'now');

        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'assigned_date' => $assignedDate,
            'commenced_date' => $commencedDate,
            'completed_date' => $completedDate,
            'completion_percentage' => 100,
            'time_spent_minutes' => $this->faker->numberBetween(120, 480),
            'assessment_score' => null,
            'assessment_passed' => null,
        ]);
    }

    public function passed(): static
    {
        $assignedDate = $this->faker->dateTimeBetween('-2 months', '-1 month');
        $commencedDate = $this->faker->dateTimeBetween($assignedDate, '-3 weeks');
        $completedDate = $this->faker->dateTimeBetween($commencedDate, 'now');
        $assessmentScore = $this->faker->randomFloat(2, 70, 100);

        return $this->state(fn (array $attributes) => [
            'status' => 'passed',
            'assigned_date' => $assignedDate,
            'commenced_date' => $commencedDate,
            'completed_date' => $completedDate,
            'completion_percentage' => 100,
            'assessment_score' => $assessmentScore,
            'assessment_passed' => true,
            'time_spent_minutes' => $this->faker->numberBetween(120, 480),
            'effectiveness_rating' => $this->faker->numberBetween(3, 5),
            'knowledge_demonstrated' => true,
            'certificate_number' => strtoupper($this->faker->bothify('CERT-####-????')),
            'certificate_issued_date' => $completedDate,
        ]);
    }

    public function failed(): static
    {
        $assignedDate = $this->faker->dateTimeBetween('-2 months', '-1 month');
        $commencedDate = $this->faker->dateTimeBetween($assignedDate, '-3 weeks');
        $completedDate = $this->faker->dateTimeBetween($commencedDate, 'now');
        $assessmentScore = $this->faker->randomFloat(2, 40, 69);

        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'assigned_date' => $assignedDate,
            'commenced_date' => $commencedDate,
            'completed_date' => $completedDate,
            'completion_percentage' => $this->faker->numberBetween(80, 100),
            'assessment_score' => $assessmentScore,
            'assessment_passed' => false,
            'assessment_attempts' => $this->faker->numberBetween(1, 3),
            'time_spent_minutes' => $this->faker->numberBetween(120, 480),
            'certificate_number' => null,
        ]);
    }

    public function overdue(): static
    {
        $assignedDate = $this->faker->dateTimeBetween('-3 months', '-2 months');
        $dueDate = $this->faker->dateTimeBetween($assignedDate, '-1 week');

        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'assigned_date' => $assignedDate,
            'due_date' => $dueDate,
            'commenced_date' => null,
            'completed_date' => null,
            'completion_percentage' => 0,
        ]);
    }

    public function expired(): static
    {
        $completedDate = $this->faker->dateTimeBetween('-4 years', '-3 years');
        $certificateNumber = strtoupper($this->faker->bothify('CERT-####-????'));

        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'completed_date' => $completedDate,
            'completion_percentage' => 100,
            'assessment_passed' => true,
            'certificate_number' => $certificateNumber,
            'certificate_issued_date' => $completedDate,
        ]);
    }

    public function highPerformer(): static
    {
        return $this->passed()->state(fn (array $attributes) => [
            'assessment_score' => $this->faker->randomFloat(2, 90, 100),
            'effectiveness_rating' => 5,
            'knowledge_demonstrated' => true,
            'assessment_attempts' => 1,
        ]);
    }

    public function needsRetake(): static
    {
        return $this->failed()->state(fn (array $attributes) => [
            'assessment_attempts' => 3,
            'assessment_score' => $this->faker->randomFloat(2, 40, 60),
        ]);
    }
}
