<?php

namespace App\Modules\ComplianceReporting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceRequirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'requirement_number',
        'title',
        'description',
        'category',
        'frequency',
        'due_date',
        'last_review_date',
        'next_review_date',
        'owner_id',
        'reviewer_id',
        'status',
        'compliance_score',
        'evidence_required',
        'evidence_files',
        'notes',
        'risk_level',
        'non_compliance_impact',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'last_review_date' => 'date',
        'next_review_date' => 'date',
        'evidence_files' => 'array',
        'compliance_score' => 'integer',
    ];

    /**
     * Apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('compliance_requirements.branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-generate requirement number
        static::creating(function ($requirement) {
            if (!$requirement->requirement_number) {
                $requirement->requirement_number = 'CR-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
            }
        });

        // Update next review date based on frequency
        static::creating(function ($requirement) {
            if (!$requirement->next_review_date) {
                $requirement->next_review_date = $requirement->calculateNextReviewDate();
            }
        });
    }

    /**
     * Get the branch that owns the requirement
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the owner of the requirement
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the reviewer of the requirement
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the creator of the requirement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the requirement
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all checks for this requirement
     */
    public function checks(): HasMany
    {
        return $this->hasMany(ComplianceCheck::class, 'requirement_id');
    }

    /**
     * Get all actions for this requirement
     */
    public function actions(): HasMany
    {
        return $this->hasMany(ComplianceAction::class, 'requirement_id');
    }

    /**
     * Check if requirement is compliant
     */
    public function isCompliant(): bool
    {
        return $this->status === 'compliant';
    }

    /**
     * Check if requirement is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return $this->due_date->isPast() && $this->status !== 'compliant';
    }

    /**
     * Check if review is due soon
     */
    public function isReviewDueSoon(int $days = 30): bool
    {
        if (!$this->next_review_date) {
            return false;
        }
        return $this->next_review_date->isFuture() &&
               $this->next_review_date->diffInDays(now()) <= $days;
    }

    /**
     * Calculate next review date based on frequency
     */
    public function calculateNextReviewDate(): ?\Carbon\Carbon
    {
        if (!$this->last_review_date) {
            $baseDate = now();
        } else {
            $baseDate = $this->last_review_date;
        }

        return match($this->frequency) {
            'daily' => $baseDate->copy()->addDay(),
            'weekly' => $baseDate->copy()->addWeek(),
            'monthly' => $baseDate->copy()->addMonth(),
            'quarterly' => $baseDate->copy()->addMonths(3),
            'yearly' => $baseDate->copy()->addYear(),
            'once' => null,
            default => $baseDate->copy()->addMonth(),
        };
    }

    /**
     * Get the latest check
     */
    public function latestCheck(): ?ComplianceCheck
    {
        return $this->checks()->latest('check_date')->first();
    }

    /**
     * Get compliance percentage
     */
    public function getCompliancePercentage(): int
    {
        return $this->compliance_score ?? 0;
    }

    /**
     * Get risk badge color
     */
    public function getRiskBadgeColor(): string
    {
        return match($this->risk_level) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'compliant' => 'success',
            'non-compliant' => 'danger',
            'partial' => 'warning',
            'not-applicable' => 'secondary',
            'under-review' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Scope: Compliant requirements
     */
    public function scopeCompliant(Builder $query): Builder
    {
        return $query->where('status', 'compliant');
    }

    /**
     * Scope: Non-compliant requirements
     */
    public function scopeNonCompliant(Builder $query): Builder
    {
        return $query->where('status', 'non-compliant');
    }

    /**
     * Scope: Overdue requirements
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'compliant');
    }

    /**
     * Scope: Review due soon
     */
    public function scopeReviewDueSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('next_review_date')
            ->where('next_review_date', '<=', now()->addDays($days))
            ->where('next_review_date', '>=', now());
    }
}
