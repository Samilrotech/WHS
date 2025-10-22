<?php

namespace App\Modules\InspectionManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionItem extends Model
{
    use HasFactory, HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Modules\InspectionManagement\InspectionItemFactory::new();
    }

    protected $fillable = [
        'inspection_id',
        'item_category',
        'item_name',
        'item_description',
        'sequence_order',
        'result',
        'defect_severity',
        'measurement_value',
        'expected_range',
        'within_tolerance',
        'defect_notes',
        'repair_recommendation',
        'urgency',
        'photo_paths',
        'annotations',
        'repair_required',
        'repair_due_date',
        'repair_completed',
        'repaired_by_user_id',
        'repair_completion_date',
        'repair_cost',
        'repair_notes',
        'safety_critical',
        'compliance_item',
        'compliance_standard',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'within_tolerance' => 'boolean',
        'photo_paths' => 'array',
        'annotations' => 'array',
        'repair_required' => 'boolean',
        'repair_due_date' => 'date',
        'repair_completed' => 'boolean',
        'repair_completion_date' => 'datetime',
        'repair_cost' => 'decimal:2',
        'safety_critical' => 'boolean',
        'compliance_item' => 'boolean',
    ];

    /**
     * Get the parent inspection
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    /**
     * Get the user who completed the repair
     */
    public function repairedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'repaired_by_user_id');
    }

    /**
     * Check if item passed inspection
     */
    public function isPassed(): bool
    {
        return $this->result === 'pass';
    }

    /**
     * Check if item failed inspection
     */
    public function isFailed(): bool
    {
        return $this->result === 'fail';
    }

    /**
     * Check if item has a defect
     */
    public function hasDefect(): bool
    {
        return $this->defect_severity !== null && $this->defect_severity !== 'none';
    }

    /**
     * Check if defect is critical
     */
    public function isCriticalDefect(): bool
    {
        return $this->defect_severity === 'critical';
    }

    /**
     * Check if defect is major
     */
    public function isMajorDefect(): bool
    {
        return $this->defect_severity === 'major';
    }

    /**
     * Check if repair is overdue
     */
    public function isRepairOverdue(): bool
    {
        if (!$this->repair_required || $this->repair_completed || !$this->repair_due_date) {
            return false;
        }

        return $this->repair_due_date->isPast();
    }

    /**
     * Get defect severity color
     */
    public function getDefectColorAttribute(): string
    {
        return match ($this->defect_severity) {
            'critical' => 'destructive',
            'major' => 'warning',
            'minor' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get result color
     */
    public function getResultColorAttribute(): string
    {
        return match ($this->result) {
            'pass' => 'success',
            'fail' => 'destructive',
            'na' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Scope query to failed items
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('result', 'fail');
    }

    /**
     * Scope query to critical defects
     */
    public function scopeCritical(Builder $query): void
    {
        $query->where('defect_severity', 'critical');
    }

    /**
     * Scope query to items requiring repair
     */
    public function scopeRequiresRepair(Builder $query): void
    {
        $query->where('repair_required', true)->where('repair_completed', false);
    }

    /**
     * Scope query to overdue repairs
     */
    public function scopeRepairOverdue(Builder $query): void
    {
        $query->where('repair_required', true)
            ->where('repair_completed', false)
            ->where('repair_due_date', '<', now());
    }

    /**
     * Scope query to safety critical items
     */
    public function scopeSafetyCritical(Builder $query): void
    {
        $query->where('safety_critical', true);
    }

    /**
     * Scope query to compliance items
     */
    public function scopeCompliance(Builder $query): void
    {
        $query->where('compliance_item', true);
    }

    /**
     * Scope query by category
     */
    public function scopeInCategory(Builder $query, string $category): void
    {
        $query->where('item_category', $category);
    }

    /**
     * Scope query by defect severity
     */
    public function scopeBySeverity(Builder $query, string $severity): void
    {
        $query->where('defect_severity', $severity);
    }
}
