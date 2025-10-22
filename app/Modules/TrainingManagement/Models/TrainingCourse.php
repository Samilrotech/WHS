<?php

namespace App\Modules\TrainingManagement\Models;

use App\Models\Branch;
use App\Models\User;
use Database\Factories\Modules\TrainingManagement\TrainingCourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class TrainingCourse extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TrainingCourseFactory
    {
        return TrainingCourseFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'created_by_user_id',
        'course_code',
        'course_name',
        'description',
        'category',
        'duration_hours',
        'validity_months',
        'is_cpd_accredited',
        'cpd_points',
        'requires_assessment',
        'pass_score',
        'delivery_method',
        'learning_objectives',
        'prerequisites',
        'trainer_name',
        'trainer_contact',
        'resource_links',
        'is_mandatory',
        'frequency',
        'reminder_days_before',
        'status',
        'available_from',
        'available_until',
        'cost_per_person',
        'provider_name',
        'provider_contact',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'validity_months' => 'integer',
        'is_cpd_accredited' => 'boolean',
        'requires_assessment' => 'boolean',
        'pass_score' => 'decimal:2',
        'learning_objectives' => 'array',
        'prerequisites' => 'array',
        'resource_links' => 'array',
        'is_mandatory' => 'boolean',
        'reminder_days_before' => 'integer',
        'available_from' => 'date',
        'available_until' => 'date',
        'cost_per_person' => 'decimal:2',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function trainingRecords(): HasMany
    {
        return $this->hasMany(TrainingRecord::class);
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if course is currently available
     */
    public function isAvailable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if course requires renewal
     */
    public function requiresRenewal(): bool
    {
        return in_array($this->frequency, ['annual', 'biennial', 'triennial', 'custom']);
    }

    /**
     * Get renewal frequency in months
     */
    public function getRenewalMonthsAttribute(): ?int
    {
        return match($this->frequency) {
            'annual' => 12,
            'biennial' => 24,
            'triennial' => 36,
            default => $this->validity_months,
        };
    }

    /**
     * Check if course is CPD accredited
     */
    public function isCPDAccredited(): bool
    {
        return $this->is_cpd_accredited === true;
    }

    /**
     * Get total enrolled count
     */
    public function getTotalEnrolledAttribute(): int
    {
        return $this->trainingRecords()->whereIn('status', ['assigned', 'in_progress'])->count();
    }

    /**
     * Get total completed count
     */
    public function getTotalCompletedAttribute(): int
    {
        return $this->trainingRecords()->whereIn('status', ['completed', 'passed'])->count();
    }

    /**
     * Get completion rate
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->trainingRecords()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->total_completed;

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Get average assessment score
     */
    public function getAverageScoreAttribute(): ?float
    {
        return $this->trainingRecords()
            ->whereNotNull('assessment_score')
            ->avg('assessment_score');
    }

    /**
     * Get pass rate
     */
    public function getPassRateAttribute(): float
    {
        $attempted = $this->trainingRecords()->whereNotNull('assessment_passed')->count();

        if ($attempted === 0) {
            return 0;
        }

        $passed = $this->trainingRecords()->where('assessment_passed', true)->count();

        return round(($passed / $attempted) * 100, 2);
    }

    /**
     * Check if course is overdue for anyone
     */
    public function hasOverdueRecords(): bool
    {
        return $this->trainingRecords()
            ->where('status', 'overdue')
            ->exists();
    }

    /**
     * Get average effectiveness rating
     */
    public function getAverageEffectivenessAttribute(): ?float
    {
        return $this->trainingRecords()
            ->whereNotNull('effectiveness_rating')
            ->avg('effectiveness_rating');
    }

    /**
     * Get total revenue (if paid course)
     */
    public function getTotalRevenueAttribute(): float
    {
        if (!$this->cost_per_person) {
            return 0;
        }

        return $this->total_completed * $this->cost_per_person;
    }

    /**
     * Check if course is mandatory
     */
    public function isMandatory(): bool
    {
        return $this->is_mandatory === true;
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeMandatory(Builder $query): void
    {
        $query->where('is_mandatory', true);
    }

    public function scopeCPDAccredited(Builder $query): void
    {
        $query->where('is_cpd_accredited', true);
    }

    public function scopeByCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('available_from')
                  ->orWhere('available_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_until')
                  ->orWhere('available_until', '>=', now());
            });
    }

    public function scopeByDeliveryMethod(Builder $query, string $method): void
    {
        $query->where('delivery_method', $method);
    }

    public function scopeRequiringAssessment(Builder $query): void
    {
        $query->where('requires_assessment', true);
    }
}
