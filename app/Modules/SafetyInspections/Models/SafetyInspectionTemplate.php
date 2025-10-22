<?php

namespace App\Modules\SafetyInspections\Models;

use App\Models\Branch;
use App\Models\User;
use Database\Factories\Modules\SafetyInspections\SafetyInspectionTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class SafetyInspectionTemplate extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): SafetyInspectionTemplateFactory
    {
        return SafetyInspectionTemplateFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'created_by_user_id',
        'template_name',
        'description',
        'category',
        'checklist_items',
        'estimated_duration_minutes',
        'requires_photos',
        'requires_signature',
        'photo_minimum_count',
        'is_scored',
        'pass_threshold',
        'scoring_method',
        'is_mandatory',
        'frequency',
        'reminder_days_before',
        'regulatory_references',
        'compliance_requirements',
        'required_certifications',
        'status',
        'version',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'estimated_duration_minutes' => 'integer',
        'requires_photos' => 'boolean',
        'requires_signature' => 'boolean',
        'photo_minimum_count' => 'integer',
        'is_scored' => 'boolean',
        'pass_threshold' => 'integer',
        'is_mandatory' => 'boolean',
        'reminder_days_before' => 'integer',
        'regulatory_references' => 'array',
        'compliance_requirements' => 'array',
        'required_certifications' => 'array',
        'version' => 'integer',
        'effective_from' => 'date',
        'effective_until' => 'date',
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

    public function inspections(): HasMany
    {
        return $this->hasMany(SafetyInspection::class, 'template_id');
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if template is currently active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->effective_from && $now->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $now->gt($this->effective_until)) {
            return false;
        }

        return true;
    }

    /**
     * Get total checklist items count
     */
    public function getTotalItemsAttribute(): int
    {
        return is_array($this->checklist_items) ? count($this->checklist_items) : 0;
    }

    /**
     * Get total inspections using this template
     */
    public function getTotalInspectionsAttribute(): int
    {
        return $this->inspections()->count();
    }

    /**
     * Get completed inspections count
     */
    public function getCompletedInspectionsAttribute(): int
    {
        return $this->inspections()->whereIn('status', ['completed', 'submitted', 'approved'])->count();
    }

    /**
     * Get average inspection score
     */
    public function getAverageScoreAttribute(): ?float
    {
        return $this->inspections()
            ->whereNotNull('inspection_score')
            ->avg('inspection_score');
    }

    /**
     * Get pass rate
     */
    public function getPassRateAttribute(): float
    {
        $total = $this->inspections()->whereNotNull('passed')->count();

        if ($total === 0) {
            return 0;
        }

        $passed = $this->inspections()->where('passed', true)->count();

        return round(($passed / $total) * 100, 2);
    }

    /**
     * Check if template requires photos
     */
    public function requiresPhotos(): bool
    {
        return $this->requires_photos === true;
    }

    /**
     * Check if template requires signature
     */
    public function requiresSignature(): bool
    {
        return $this->requires_signature === true;
    }

    /**
     * Check if template is mandatory
     */
    public function isMandatory(): bool
    {
        return $this->is_mandatory === true;
    }

    /**
     * Check if template is scored
     */
    public function isScored(): bool
    {
        return $this->is_scored === true;
    }

    /**
     * Create inspection from template
     */
    public function createInspection(User $inspector, array $additionalData = []): SafetyInspection
    {
        $data = array_merge([
            'branch_id' => $this->branch_id,
            'template_id' => $this->id,
            'inspector_user_id' => $inspector->id,
            'inspection_type' => $this->category,
            'title' => $this->template_name,
            'description' => $this->description,
            'total_items' => $this->total_items,
            'max_possible_score' => $this->total_items * 1, // Assuming 1 point per item
            'status' => 'scheduled',
        ], $additionalData);

        return SafetyInspection::create($data);
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', now());
            });
    }

    public function scopeMandatory(Builder $query): void
    {
        $query->where('is_mandatory', true);
    }

    public function scopeByCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    public function scopeRequiringPhotos(Builder $query): void
    {
        $query->where('requires_photos', true);
    }

    public function scopeScored(Builder $query): void
    {
        $query->where('is_scored', true);
    }
}
