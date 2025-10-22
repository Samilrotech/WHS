<?php

namespace App\Modules\TrainingManagement\Services;

use App\Models\User;
use App\Modules\TrainingManagement\Models\Certification;
use App\Modules\TrainingManagement\Models\TrainingCourse;
use App\Modules\TrainingManagement\Models\TrainingRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    /**
     * Assign training to a user
     */
    public function assignTraining(
        User $user,
        TrainingCourse $course,
        User $assignedBy,
        ?\DateTime $dueDate = null
    ): TrainingRecord {
        return DB::transaction(function () use ($user, $course, $assignedBy, $dueDate) {
            $record = TrainingRecord::create([
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'training_course_id' => $course->id,
                'assigned_by_user_id' => $assignedBy->id,
                'assigned_date' => now(),
                'due_date' => $dueDate ?? now()->addDays(30),
                'status' => 'assigned',
                'completion_percentage' => 0,
                'requires_renewal' => $course->requiresRenewal(),
            ]);

            // Send notification to user (implement notification logic)

            return $record;
        });
    }

    /**
     * Bulk assign training to multiple users
     */
    public function bulkAssignTraining(
        Collection $users,
        TrainingCourse $course,
        User $assignedBy,
        ?\DateTime $dueDate = null
    ): Collection {
        return DB::transaction(function () use ($users, $course, $assignedBy, $dueDate) {
            $records = collect();

            foreach ($users as $user) {
                $records->push($this->assignTraining($user, $course, $assignedBy, $dueDate));
            }

            return $records;
        });
    }

    /**
     * Start training (commence)
     */
    public function commenceTraining(TrainingRecord $record): TrainingRecord
    {
        $record->commence();

        return $record->fresh();
    }

    /**
     * Update training progress
     */
    public function updateProgress(TrainingRecord $record, int $percentage, ?string $notes = null): TrainingRecord
    {
        $record->updateProgress($percentage);

        if ($notes) {
            $record->progress_notes = $notes;
            $record->save();
        }

        return $record->fresh();
    }

    /**
     * Complete training with assessment
     */
    public function completeTraining(
        TrainingRecord $record,
        User $completedBy,
        ?float $assessmentScore = null,
        ?string $feedback = null,
        ?string $certificateNumber = null
    ): TrainingRecord {
        return DB::transaction(function () use ($record, $completedBy, $assessmentScore, $feedback, $certificateNumber) {
            $record->complete($completedBy, $assessmentScore);

            if ($feedback) {
                $record->assessment_feedback = $feedback;
            }

            if ($certificateNumber) {
                $record->certificate_number = $certificateNumber;
            }

            $record->save();

            // If passed and creates certification, create certification record
            if ($record->passedAssessment() && $this->shouldCreateCertification($record)) {
                $this->createCertification($record);
            }

            return $record->fresh();
        });
    }

    /**
     * Record assessment attempt
     */
    public function recordAssessmentAttempt(
        TrainingRecord $record,
        float $score,
        bool $passed,
        ?string $feedback = null
    ): TrainingRecord {
        $record->assessment_score = $score;
        $record->assessment_passed = $passed;
        $record->assessment_feedback = $feedback;
        $record->attempts_count += 1;
        $record->last_attempt_date = now();

        if ($passed) {
            $record->status = 'passed';
            $record->completed_date = now();
        } else {
            $record->status = 'failed';
        }

        $record->save();

        return $record->fresh();
    }

    /**
     * Determine if training should create certification
     */
    protected function shouldCreateCertification(TrainingRecord $record): bool
    {
        // Create certification for specific course categories
        return in_array($record->trainingCourse->category, [
            'driver_training',
            'vehicle_operation',
            'forklift_operation',
            'first_aid',
            'hazmat_handling',
        ]);
    }

    /**
     * Create certification from training record
     */
    public function createCertification(TrainingRecord $record): Certification
    {
        $certificationType = $this->mapCourseToCertificationType($record->trainingCourse);

        return Certification::create([
            'branch_id' => $record->branch_id,
            'user_id' => $record->user_id,
            'training_record_id' => $record->id,
            'certification_type' => $certificationType,
            'certification_name' => $record->trainingCourse->course_name,
            'issuing_authority' => $record->provider ?? 'Internal Training',
            'issue_date' => $record->completed_date,
            'expiry_date' => $record->expiry_date,
            'verification_status' => 'verified',
            'competencies_covered' => $record->trainingCourse->learning_objectives,
            'proficiency_level' => $this->calculateProficiencyLevel($record->assessment_score),
        ]);
    }

    /**
     * Map course category to certification type
     */
    protected function mapCourseToCertificationType(TrainingCourse $course): string
    {
        return match($course->category) {
            'driver_training' => 'driver_license',
            'forklift_operation' => 'forklift_license',
            'first_aid' => 'first_aid',
            'load_securement' => 'load_securement',
            'fatigue_management' => 'fatigue_management',
            default => 'other',
        };
    }

    /**
     * Calculate proficiency level based on score
     */
    protected function calculateProficiencyLevel(?float $score): int
    {
        if (!$score) {
            return 3; // Default to intermediate
        }

        return match(true) {
            $score >= 95 => 5, // Expert
            $score >= 85 => 4, // Advanced
            $score >= 75 => 3, // Intermediate
            $score >= 65 => 2, // Basic
            default => 1,      // Beginner
        };
    }

    /**
     * Get user competency matrix
     */
    public function getUserCompetencyMatrix(User $user): array
    {
        $completedRecords = TrainingRecord::forUser($user->id)
            ->completed()
            ->with('trainingCourse')
            ->get();

        $certifications = Certification::forUser($user->id)
            ->active()
            ->get();

        $competencies = [];

        foreach ($completedRecords as $record) {
            $category = $record->trainingCourse->category;

            if (!isset($competencies[$category])) {
                $competencies[$category] = [
                    'category' => $category,
                    'courses_completed' => 0,
                    'average_score' => 0,
                    'latest_completion' => null,
                    'certifications' => [],
                ];
            }

            $competencies[$category]['courses_completed'] += 1;
            $competencies[$category]['average_score'] = $this->calculateAverageScore(
                $competencies[$category]['average_score'],
                $record->assessment_score,
                $competencies[$category]['courses_completed']
            );

            if (!$competencies[$category]['latest_completion'] ||
                $record->completed_date > $competencies[$category]['latest_completion']) {
                $competencies[$category]['latest_completion'] = $record->completed_date;
            }
        }

        foreach ($certifications as $cert) {
            $category = $this->mapCertificationTypeToCategory($cert->certification_type);

            if (isset($competencies[$category])) {
                $competencies[$category]['certifications'][] = [
                    'name' => $cert->certification_name,
                    'expiry_date' => $cert->expiry_date,
                    'days_remaining' => $cert->days_remaining,
                    'status' => $cert->verification_status,
                ];
            }
        }

        return array_values($competencies);
    }

    /**
     * Map certification type back to category
     */
    protected function mapCertificationTypeToCategory(string $type): string
    {
        return match($type) {
            'driver_license', 'heavy_vehicle_license' => 'driver_training',
            'forklift_license' => 'forklift_operation',
            'first_aid' => 'first_aid',
            default => 'other',
        };
    }

    /**
     * Calculate running average score
     */
    protected function calculateAverageScore(float $currentAvg, ?float $newScore, int $count): float
    {
        if (!$newScore) {
            return $currentAvg;
        }

        return round((($currentAvg * ($count - 1)) + $newScore) / $count, 2);
    }

    /**
     * Identify skill gaps for user
     */
    public function identifySkillGaps(User $user): array
    {
        // Get all mandatory courses
        $mandatoryCourses = TrainingCourse::active()
            ->mandatory()
            ->get();

        // Get user's completed training
        $completedCourseIds = TrainingRecord::forUser($user->id)
            ->completed()
            ->pluck('training_course_id')
            ->toArray();

        // Identify gaps
        $gaps = [];

        foreach ($mandatoryCourses as $course) {
            if (!in_array($course->id, $completedCourseIds)) {
                $gaps[] = [
                    'course_id' => $course->id,
                    'course_name' => $course->course_name,
                    'category' => $course->category,
                    'priority' => 'high', // All mandatory courses are high priority
                    'reason' => 'Mandatory training not completed',
                ];
            }
        }

        // Check for expired certifications
        $expiredCerts = Certification::forUser($user->id)
            ->expired()
            ->get();

        foreach ($expiredCerts as $cert) {
            $gaps[] = [
                'certification_id' => $cert->id,
                'certification_name' => $cert->certification_name,
                'type' => $cert->certification_type,
                'priority' => 'critical',
                'reason' => 'Certification expired on ' . $cert->expiry_date->format('Y-m-d'),
                'expired_days' => abs($cert->days_remaining),
            ];
        }

        return $gaps;
    }

    /**
     * Get training effectiveness statistics
     */
    public function getTrainingEffectiveness(TrainingCourse $course): array
    {
        $records = TrainingRecord::byCourse($course->id)
            ->completed()
            ->get();

        $totalRecords = $records->count();

        if ($totalRecords === 0) {
            return [
                'total_completed' => 0,
                'average_score' => null,
                'pass_rate' => 0,
                'average_effectiveness' => null,
                'knowledge_demonstration_rate' => 0,
                'average_completion_time' => null,
            ];
        }

        return [
            'total_completed' => $totalRecords,
            'average_score' => $records->avg('assessment_score'),
            'pass_rate' => ($records->where('assessment_passed', true)->count() / $totalRecords) * 100,
            'average_effectiveness' => $records->avg('effectiveness_rating'),
            'knowledge_demonstration_rate' => ($records->where('knowledge_demonstrated', true)->count() / $totalRecords) * 100,
            'average_completion_time' => $records->avg('time_to_completion'),
        ];
    }

    /**
     * Get renewal reminders
     */
    public function getRenewalReminders(int $daysAhead = 30): Collection
    {
        // Get expiring training records
        $expiringRecords = TrainingRecord::expiringSoon($daysAhead)
            ->where('requires_renewal', true)
            ->with(['user', 'trainingCourse'])
            ->get();

        // Get expiring certifications
        $expiringCerts = Certification::expiringSoon($daysAhead)
            ->where('auto_renewal_required', true)
            ->with(['user'])
            ->get();

        return collect([
            'expiring_training' => $expiringRecords,
            'expiring_certifications' => $expiringCerts,
            'total_count' => $expiringRecords->count() + $expiringCerts->count(),
        ]);
    }

    /**
     * Calculate training ROI
     */
    public function calculateTrainingROI(TrainingCourse $course): array
    {
        $totalCost = $course->cost_per_person * $course->total_completed;

        // This is a simplified ROI calculation
        // In production, you'd factor in productivity gains, reduced incidents, etc.

        $records = TrainingRecord::byCourse($course->id)->completed()->get();

        return [
            'total_cost' => $totalCost,
            'participants_trained' => $course->total_completed,
            'average_effectiveness' => $course->average_effectiveness,
            'knowledge_retention_rate' => $records->where('knowledge_demonstrated', true)->count() / max($records->count(), 1) * 100,
            'completion_rate' => $course->completion_rate,
            // Additional metrics could include:
            // - Reduced incident rates
            // - Productivity improvements
            // - Compliance achievement
        ];
    }
}
