<?php

namespace App\Modules\SafetyInspections\Models;

use App\Models\Branch;
use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use Database\Factories\Modules\SafetyInspections\SafetyInspectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class SafetyInspection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): SafetyInspectionFactory
    {
        return SafetyInspectionFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'template_id',
        'inspector_user_id',
        'reviewed_by_user_id',
        'inspection_number',
        'inspection_type',
        'title',
        'description',
        'location',
        'area',
        'asset_tag',
        'vehicle_id',
        'gps_coordinates',
        'scheduled_date',
        'started_at',
        'completed_at',
        'submitted_at',
        'reviewed_at',
        'status',
        'total_items',
        'completed_items',
        'passed_items',
        'failed_items',
        'na_items',
        'inspection_score',
        'max_possible_score',
        'passed',
        'has_non_compliance',
        'non_compliance_count',
        'non_compliance_severity',
        'non_compliance_summary',
        'escalation_required',
        'escalated_at',
        'photos_count',
        'photo_urls',
        'inspector_signature_path',
        'reviewer_signature_path',
        'inspector_notes',
        'reviewer_comments',
        'weather_conditions',
        'temperature',
        'requires_follow_up',
        'follow_up_due_date',
        'assigned_to_user_id',
        'duration_minutes',
        'audit_log',
    ];

    protected $casts = [
        'gps_coordinates' => 'array',
        'scheduled_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'total_items' => 'integer',
        'completed_items' => 'integer',
        'passed_items' => 'integer',
        'failed_items' => 'integer',
        'na_items' => 'integer',
        'inspection_score' => 'decimal:2',
        'max_possible_score' => 'integer',
        'passed' => 'boolean',
        'has_non_compliance' => 'boolean',
        'non_compliance_count' => 'integer',
        'escalation_required' => 'boolean',
        'escalated_at' => 'datetime',
        'photos_count' => 'integer',
        'photo_urls' => 'array',
        'temperature' => 'integer',
        'requires_follow_up' => 'boolean',
        'follow_up_due_date' => 'date',
        'duration_minutes' => 'integer',
        'audit_log' => 'array',
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

        // Auto-generate inspection number
        static::creating(function ($inspection) {
            if (!$inspection->inspection_number) {
                $inspection->inspection_number = static::generateInspectionNumber($inspection->branch_id);
            }
        });
    }

    /**
     * Generate unique inspection number
     */
    protected static function generateInspectionNumber(string $branchId): string
    {
        $prefix = 'SI';
        $year = now()->format('y');
        $month = now()->format('m');

        $lastInspection = static::withoutGlobalScope('branch')
            ->where('branch_id', $branchId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->latest('inspection_number')
            ->first();

        $sequence = $lastInspection
            ? ((int) substr($lastInspection->inspection_number, -4)) + 1
            : 1;

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    // ============================================================================
    // RELATIONSHIPS
    // ============================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SafetyInspectionTemplate::class, 'template_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(SafetyChecklistItem::class, 'inspection_id');
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if inspection is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'scheduled' &&
               $this->scheduled_date &&
               $this->scheduled_date->isPast();
    }

    /**
     * Check if inspection is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if inspection is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'submitted', 'approved']);
    }

    /**
     * Check if inspection passed
     */
    public function isPassed(): bool
    {
        return $this->passed === true;
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_items === 0) {
            return 0;
        }

        return round(($this->completed_items / $this->total_items) * 100, 2);
    }

    /**
     * Get pass percentage
     */
    public function getPassPercentageAttribute(): float
    {
        if ($this->completed_items === 0) {
            return 0;
        }

        return round(($this->passed_items / $this->completed_items) * 100, 2);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'completed', 'submitted' => 'success',
            'in_progress' => 'warning',
            'rejected' => 'destructive',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->non_compliance_severity) {
            'critical' => 'destructive',
            'high' => 'destructive',
            'medium' => 'warning',
            'low' => 'warning',
            default => 'success',
        };
    }

    /**
     * Calculate inspection duration
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Start inspection
     */
    public function start(): bool
    {
        $this->started_at = now();
        $this->status = 'in_progress';
        return $this->save();
    }

    /**
     * Complete inspection
     */
    public function complete(): bool
    {
        $this->completed_at = now();
        $this->duration_minutes = $this->duration;
        $this->status = 'completed';

        // Calculate scores
        $this->calculateScore();

        return $this->save();
    }

    /**
     * Submit inspection for review
     */
    public function submit(): bool
    {
        if (!$this->isCompleted()) {
            $this->complete();
        }

        $this->submitted_at = now();
        $this->status = 'submitted';

        return $this->save();
    }

    /**
     * Approve inspection
     */
    public function approve(User $reviewer, ?string $comments = null): bool
    {
        $this->reviewed_at = now();
        $this->reviewed_by_user_id = $reviewer->id;
        $this->reviewer_comments = $comments;
        $this->status = 'approved';

        return $this->save();
    }

    /**
     * Reject inspection
     */
    public function reject(User $reviewer, string $reason): bool
    {
        $this->reviewed_at = now();
        $this->reviewed_by_user_id = $reviewer->id;
        $this->reviewer_comments = $reason;
        $this->status = 'rejected';

        return $this->save();
    }

    /**
     * Calculate inspection score
     */
    public function calculateScore(): void
    {
        $items = $this->checklistItems;

        $this->total_items = $items->count();
        $this->completed_items = $items->whereIn('result', ['pass', 'fail', 'na'])->count();
        $this->passed_items = $items->where('result', 'pass')->count();
        $this->failed_items = $items->where('result', 'fail')->count();
        $this->na_items = $items->where('result', 'na')->count();

        // Calculate score (excluding N/A items)
        $scorableItems = $this->total_items - $this->na_items;

        if ($scorableItems > 0) {
            $this->inspection_score = round(($this->passed_items / $scorableItems) * 100, 2);
        }

        // Determine if passed
        $passThreshold = $this->template ? $this->template->pass_threshold : 80;
        $this->passed = $this->inspection_score >= $passThreshold;

        // Check non-compliance
        $nonCompliantItems = $items->where('non_compliant', true);
        $this->has_non_compliance = $nonCompliantItems->count() > 0;
        $this->non_compliance_count = $nonCompliantItems->count();

        if ($this->has_non_compliance) {
            $maxSeverity = $nonCompliantItems->max('severity');
            $this->non_compliance_severity = $maxSeverity ?? 'low';

            // Auto-escalate critical non-compliance
            if (in_array($maxSeverity, ['high', 'critical'])) {
                $this->escalation_required = true;
            }
        }
    }

    /**
     * Escalate inspection
     */
    public function escalate(?User $assignedTo = null): bool
    {
        $this->escalation_required = true;
        $this->escalated_at = now();

        if ($assignedTo) {
            $this->assigned_to_user_id = $assignedTo->id;
        }

        return $this->save();
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeScheduled(Builder $query): void
    {
        $query->where('status', 'scheduled');
    }

    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', 'in_progress');
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->whereIn('status', ['completed', 'submitted', 'approved']);
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', 'scheduled')
            ->where('scheduled_date', '<', now());
    }

    public function scopeByType(Builder $query, string $type): void
    {
        $query->where('inspection_type', $type);
    }

    public function scopeByInspector(Builder $query, string $userId): void
    {
        $query->where('inspector_user_id', $userId);
    }

    public function scopeWithNonCompliance(Builder $query): void
    {
        $query->where('has_non_compliance', true);
    }

    public function scopeCriticalNonCompliance(Builder $query): void
    {
        $query->where('non_compliance_severity', 'critical');
    }

    public function scopeRequiringFollowUp(Builder $query): void
    {
        $query->where('requires_follow_up', true);
    }

    public function scopePassed(Builder $query): void
    {
        $query->where('passed', true);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('passed', false);
    }
}
