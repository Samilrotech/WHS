<?php

namespace App\Modules\InspectionManagement\Models;

use App\Models\Branch;
use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Modules\InspectionManagement\InspectionFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'vehicle_id',
        'inspector_user_id',
        'approved_by_user_id',
        'inspection_number',
        'inspection_type',
        'inspection_date',
        'odometer_reading',
        'location',
        'inspection_hours',
        'status',
        'overall_result',
        'total_items_checked',
        'items_passed',
        'items_failed',
        'critical_defects',
        'major_defects',
        'minor_defects',
        'photo_paths',
        'inspector_notes',
        'defects_summary',
        'recommendations',
        'approved_date',
        'approval_notes',
        'rejection_reason',
        'next_inspection_due',
        'compliance_verified',
        'inspector_signature_path',
        'approver_signature_path',
    ];

    protected $casts = [
        'inspection_date' => 'datetime',
        'approved_date' => 'datetime',
        'next_inspection_due' => 'date',
        'odometer_reading' => 'integer',
        'inspection_hours' => 'decimal:2',
        'total_items_checked' => 'integer',
        'items_passed' => 'integer',
        'items_failed' => 'integer',
        'critical_defects' => 'integer',
        'major_defects' => 'integer',
        'minor_defects' => 'integer',
        'photo_paths' => 'array',
        'compliance_verified' => 'boolean',
    ];

    /**
     * Global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });
    }

    /**
     * Get the branch that owns this inspection
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the vehicle being inspected
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the inspector (user who conducted inspection)
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_user_id');
    }

    /**
     * Get the approver (supervisor who approved inspection)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Get all inspection items (checklist items)
     */
    public function items(): HasMany
    {
        return $this->hasMany(InspectionItem::class);
    }

    /**
     * Get only failed items
     */
    public function failedItems()
    {
        return $this->items()->where('result', 'fail');
    }

    /**
     * Get only critical defects
     */
    public function criticalDefects()
    {
        return $this->items()->where('defect_severity', 'critical');
    }

    /**
     * Get items requiring repair
     */
    public function itemsRequiringRepair()
    {
        return $this->items()->where('repair_required', true)->where('repair_completed', false);
    }

    /**
     * Calculate completion percentage
     */
    public function getCompletionPercentageAttribute(): int
    {
        if ($this->total_items_checked === 0) {
            return 0;
        }

        $passCount = $this->items()->where('result', '=', 'pass')->count();
        $failCount = $this->items()->where('result', '=', 'fail')->count();
        $checkedItems = $passCount + $failCount;

        return (int) round(($checkedItems / $this->total_items_checked) * 100);
    }

    /**
     * Check if inspection has any critical defects
     */
    public function hasCriticalDefects(): bool
    {
        return $this->critical_defects > 0;
    }

    /**
     * Check if inspection has any defects (critical, major, or minor)
     */
    public function hasDefects(): bool
    {
        return $this->critical_defects > 0 || $this->major_defects > 0 || $this->minor_defects > 0;
    }

    /**
     * Check if inspection passed (no critical or major defects)
     */
    public function isPassed(): bool
    {
        return $this->overall_result === 'pass' || $this->overall_result === 'pass_minor';
    }

    /**
     * Check if inspection failed
     */
    public function isFailed(): bool
    {
        return $this->overall_result === 'fail_major' || $this->overall_result === 'fail_critical';
    }

    /**
     * Check if vehicle can be operated based on inspection result
     */
    public function canVehicleOperate(): bool
    {
        return $this->overall_result !== 'fail_critical' && $this->critical_defects === 0;
    }

    /**
     * Check if inspection is overdue for approval
     */
    public function isOverdueForApproval(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        // Inspections should be approved within 48 hours of completion
        return $this->updated_at->addDays(2)->isPast();
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected', 'failed' => 'destructive',
            'completed' => 'warning',
            'in_progress' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get overall result color
     */
    public function getResultColorAttribute(): string
    {
        return match ($this->overall_result) {
            'pass' => 'success',
            'pass_minor' => 'success',
            'fail_major' => 'warning',
            'fail_critical' => 'destructive',
            default => 'gray',
        };
    }

    /**
     * Scope query to overdue inspections
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', 'completed')
            ->where('updated_at', '<=', now()->subDays(2));
    }

    /**
     * Scope query to failed inspections
     */
    public function scopeFailed(Builder $query): void
    {
        $query->whereIn('overall_result', ['fail_major', 'fail_critical']);
    }

    /**
     * Scope query to inspections with critical defects
     */
    public function scopeWithCriticalDefects(Builder $query): void
    {
        $query->where('critical_defects', '>', 0);
    }

    /**
     * Scope query to pending approval
     */
    public function scopePendingApproval(Builder $query): void
    {
        $query->where('status', 'completed');
    }

    /**
     * Scope query by inspection type
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('inspection_type', $type);
    }

    /**
     * Scope query by date range
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): void
    {
        $query->whereBetween('inspection_date', [$startDate, $endDate]);
    }
}
