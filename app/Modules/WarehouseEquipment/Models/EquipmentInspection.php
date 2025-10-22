<?php

namespace App\Modules\WarehouseEquipment\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentInspection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\EquipmentInspectionFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'equipment_id',
        'inspector_user_id',
        'inspection_number',
        'inspection_type',
        'scheduled_date',
        'started_at',
        'completed_at',
        'status',
        'total_items',
        'completed_items',
        'inspection_score',
        'passed',
        'defects_found',
        'defect_count',
        'severity',
        'escalation_required',
        'inspector_notes',
        'inspector_signature_path',
        'reviewer_user_id',
        'reviewed_at',
        'reviewer_comments',
        'rejection_reason',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'inspection_score' => 'decimal:2',
        'passed' => 'boolean',
        'defects_found' => 'boolean',
        'escalation_required' => 'boolean',
    ];

    /**
     * Global scope for branch isolation
     */
    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && ! auth()->user()->hasRole('admin')) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });

        static::creating(function ($inspection) {
            if (! $inspection->inspection_number) {
                $inspection->inspection_number = static::generateInspectionNumber();
            }
        });
    }

    /**
     * Generate unique inspection number
     */
    protected static function generateInspectionNumber(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('EQI-%d-%04d', $year, $count);
    }

    /**
     * Get the branch that owns the inspection
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the equipment being inspected
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(WarehouseEquipment::class, 'equipment_id');
    }

    /**
     * Get the inspector
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_user_id');
    }

    /**
     * Get the reviewer
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /**
     * Get all checklist items for this inspection
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(InspectionChecklistItem::class, 'inspection_id');
    }

    /**
     * Calculate inspection score based on checklist items
     */
    public function calculateScore(): void
    {
        $items = $this->checklistItems;
        $totalItems = $items->count();

        if ($totalItems === 0) {
            return;
        }

        $passedItems = $items->where('result', 'pass')->count();
        $failedItems = $items->where('result', 'fail')->count();

        $this->total_items = $totalItems;
        $this->completed_items = $items->whereIn('result', ['pass', 'fail', 'na'])->count();
        $this->inspection_score = ($passedItems / $totalItems) * 100;

        // Check for defects
        $criticalDefects = $items->where('defect_identified', true)
            ->where('defect_severity', 'critical');

        $this->defects_found = $items->where('defect_identified', true)->count() > 0;
        $this->defect_count = $items->where('defect_identified', true)->count();

        // Determine severity
        if ($criticalDefects->count() > 0) {
            $this->severity = 'critical';
            $this->escalation_required = true;
        } elseif ($items->where('defect_severity', 'major')->count() > 0) {
            $this->severity = 'major';
        } elseif ($items->where('defect_severity', 'moderate')->count() > 0) {
            $this->severity = 'moderate';
        } elseif ($items->where('defect_severity', 'minor')->count() > 0) {
            $this->severity = 'minor';
        } else {
            $this->severity = 'none';
        }

        // Determine pass/fail (assuming 80% threshold)
        $this->passed = $this->inspection_score >= 80 && ! $criticalDefects->count();

        $this->save();
    }

    /**
     * Scope to filter by inspection type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('inspection_type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter passed inspections
     */
    public function scopePassed(Builder $query): Builder
    {
        return $query->where('passed', true);
    }

    /**
     * Scope to filter failed inspections
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('passed', false);
    }

    /**
     * Scope to filter inspections with defects
     */
    public function scopeWithDefects(Builder $query): Builder
    {
        return $query->where('defects_found', true);
    }
}
