<?php

namespace App\Modules\TrainingManagement\Models;

use App\Models\Branch;
use App\Models\User;
use Database\Factories\Modules\TrainingManagement\TrainingRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class TrainingRecord extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TrainingRecordFactory
    {
        return TrainingRecordFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'user_id',
        'training_course_id',
        'assigned_by_user_id',
        'completed_by_user_id',
        'assigned_date',
        'due_date',
        'commenced_date',
        'completed_date',
        'expiry_date',
        'status',
        'completion_percentage',
        'progress_notes',
        'assessment_score',
        'assessment_passed',
        'assessment_feedback',
        'attempts_count',
        'last_attempt_date',
        'certificate_number',
        'certificate_path',
        'evidence_photos',
        'attendance_records',
        'effectiveness_rating',
        'effectiveness_comments',
        'effectiveness_review_date',
        'knowledge_demonstrated',
        'reminder_sent',
        'last_reminder_sent_at',
        'requires_renewal',
        'training_cost',
        'provider',
        'location',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'commenced_date' => 'date',
        'completed_date' => 'date',
        'expiry_date' => 'date',
        'completion_percentage' => 'integer',
        'assessment_score' => 'decimal:2',
        'assessment_passed' => 'boolean',
        'attempts_count' => 'integer',
        'last_attempt_date' => 'date',
        'evidence_photos' => 'array',
        'attendance_records' => 'array',
        'effectiveness_rating' => 'integer',
        'effectiveness_review_date' => 'date',
        'knowledge_demonstrated' => 'boolean',
        'reminder_sent' => 'boolean',
        'last_reminder_sent_at' => 'datetime',
        'requires_renewal' => 'boolean',
        'training_cost' => 'decimal:2',
    ];

    /**
     * Boot the model and add global scopes
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });
    }

    // ============================================================================
    // RELATIONSHIPS
    // ============================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function certification(): HasOne
    {
        return $this->hasOne(Certification::class);
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if training is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'completed' &&
               $this->status !== 'passed' &&
               $this->due_date &&
               $this->due_date->isPast();
    }

    /**
     * Check if training is expiring soon
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days &&
               $this->expiry_date->isFuture();
    }

    /**
     * Check if training is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if assessment is required
     */
    public function requiresAssessment(): bool
    {
        return $this->trainingCourse->requires_assessment;
    }

    /**
     * Check if training passed assessment
     */
    public function passedAssessment(): bool
    {
        return $this->assessment_passed === true;
    }

    /**
     * Check if training failed assessment
     */
    public function failedAssessment(): bool
    {
        return $this->assessment_passed === false;
    }

    /**
     * Get training duration
     */
    public function getTrainingDurationAttribute(): ?int
    {
        if (!$this->commenced_date || !$this->completed_date) {
            return null;
        }

        return $this->commenced_date->diffInDays($this->completed_date);
    }

    /**
     * Check if training is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if training is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'passed']);
    }

    /**
     * Get completion status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'passed' => 'success',
            'completed' => 'success',
            'in_progress' => 'warning',
            'failed' => 'destructive',
            'expired' => 'destructive',
            'overdue' => 'destructive',
            default => 'secondary',
        };
    }

    /**
     * Calculate time to completion (for overdue tracking)
     */
    public function getTimeToCompletionAttribute(): ?int
    {
        if (!$this->assigned_date || !$this->completed_date) {
            return null;
        }

        return $this->assigned_date->diffInDays($this->completed_date);
    }

    /**
     * Check if needs effectiveness review
     */
    public function needsEffectivenessReview(): bool
    {
        return $this->isCompleted() &&
               !$this->effectiveness_rating &&
               $this->completed_date &&
               $this->completed_date->diffInDays(now()) >= 30; // 30 days after completion
    }

    /**
     * Mark as commenced
     */
    public function commence(): bool
    {
        $this->commenced_date = now();
        $this->status = 'in_progress';
        return $this->save();
    }

    /**
     * Mark as completed
     */
    public function complete(User $completedBy, ?float $score = null): bool
    {
        $this->completed_date = now();
        $this->completed_by_user_id = $completedBy->id;
        $this->completion_percentage = 100;

        if ($score !== null) {
            $this->assessment_score = $score;
            $this->assessment_passed = $score >= $this->trainingCourse->pass_score;
            $this->status = $this->assessment_passed ? 'passed' : 'failed';
        } else {
            $this->status = 'completed';
        }

        // Calculate expiry date if applicable
        if ($this->trainingCourse->validity_months) {
            $this->expiry_date = now()->addMonths($this->trainingCourse->validity_months);
        }

        return $this->save();
    }

    /**
     * Update progress
     */
    public function updateProgress(int $percentage): bool
    {
        $this->completion_percentage = min($percentage, 100);

        if ($this->completion_percentage === 100 && $this->status === 'in_progress') {
            $this->status = 'completed';
            $this->completed_date = now();
        }

        return $this->save();
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeForUser(Builder $query, string $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->whereNotIn('status', ['completed', 'passed'])
            ->where('due_date', '<', now());
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): void
    {
        $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    public function scopeExpired(Builder $query): void
    {
        $query->where('expiry_date', '<', now());
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->whereIn('status', ['completed', 'passed']);
    }

    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', 'in_progress');
    }

    public function scopePassed(Builder $query): void
    {
        $query->where('assessment_passed', true);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('assessment_passed', false);
    }

    public function scopeByStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    public function scopeByCourse(Builder $query, string $courseId): void
    {
        $query->where('training_course_id', $courseId);
    }
}
