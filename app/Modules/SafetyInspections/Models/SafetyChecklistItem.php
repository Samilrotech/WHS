<?php

namespace App\Modules\SafetyInspections\Models;

use Database\Factories\Modules\SafetyInspections\SafetyChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class SafetyChecklistItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): SafetyChecklistItemFactory
    {
        return SafetyChecklistItemFactory::new();
    }

    protected $fillable = [
        'inspection_id',
        'sequence_order',
        'item_code',
        'category',
        'question',
        'guidance_notes',
        'item_type',
        'response_options',
        'response_value',
        'result',
        'is_critical',
        'score_weight',
        'score_awarded',
        'non_compliant',
        'severity',
        'non_compliance_notes',
        'corrective_action_required',
        'correction_due_date',
        'photo_urls',
        'photos_count',
        'annotations',
        'regulation_reference',
        'compliance_standard',
        'responded_at',
        'response_notes',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'response_options' => 'array',
        'is_critical' => 'boolean',
        'score_weight' => 'integer',
        'score_awarded' => 'integer',
        'non_compliant' => 'boolean',
        'correction_due_date' => 'date',
        'photo_urls' => 'array',
        'photos_count' => 'integer',
        'annotations' => 'array',
        'responded_at' => 'datetime',
    ];

    // ============================================================================
    // RELATIONSHIPS
    // ============================================================================

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(SafetyInspection::class);
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if item passed
     */
    public function passed(): bool
    {
        return $this->result === 'pass';
    }

    /**
     * Check if item failed
     */
    public function failed(): bool
    {
        return $this->result === 'fail';
    }

    /**
     * Check if item is N/A
     */
    public function isNA(): bool
    {
        return $this->result === 'na';
    }

    /**
     * Check if item is pending
     */
    public function isPending(): bool
    {
        return $this->result === 'pending';
    }

    /**
     * Check if item is critical
     */
    public function isCritical(): bool
    {
        return $this->is_critical === true;
    }

    /**
     * Check if item is non-compliant
     */
    public function isNonCompliant(): bool
    {
        return $this->non_compliant === true;
    }

    /**
     * Get severity color
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'destructive',
            'high' => 'destructive',
            'medium' => 'warning',
            'low' => 'warning',
            default => 'success',
        };
    }

    /**
     * Get result color
     */
    public function getResultColorAttribute(): string
    {
        return match($this->result) {
            'pass' => 'success',
            'fail' => 'destructive',
            'na' => 'secondary',
            default => 'warning',
        };
    }

    /**
     * Record response
     */
    public function recordResponse(
        string $result,
        $responseValue = null,
        ?string $notes = null,
        array $photoUrls = []
    ): bool {
        $this->result = $result;
        $this->response_value = $responseValue;
        $this->response_notes = $notes;
        $this->responded_at = now();

        if (!empty($photoUrls)) {
            $this->photo_urls = $photoUrls;
            $this->photos_count = count($photoUrls);
        }

        // Award score if passed
        if ($result === 'pass') {
            $this->score_awarded = $this->score_weight;
        } else {
            $this->score_awarded = 0;
        }

        // Mark as non-compliant if failed and critical
        if ($result === 'fail') {
            $this->non_compliant = true;

            if ($this->is_critical) {
                $this->severity = 'high';
            }
        }

        return $this->save();
    }

    /**
     * Mark as non-compliant
     */
    public function markNonCompliant(
        string $severity,
        string $notes,
        ?string $correctiveAction = null,
        ?\DateTime $correctionDueDate = null
    ): bool {
        $this->non_compliant = true;
        $this->severity = $severity;
        $this->non_compliance_notes = $notes;
        $this->corrective_action_required = $correctiveAction;
        $this->correction_due_date = $correctionDueDate;

        return $this->save();
    }

    /**
     * Add photo
     */
    public function addPhoto(string $photoUrl, ?array $annotation = null): bool
    {
        $photos = $this->photo_urls ?? [];
        $photos[] = $photoUrl;

        $this->photo_urls = $photos;
        $this->photos_count = count($photos);

        if ($annotation) {
            $annotations = $this->annotations ?? [];
            $annotations[] = $annotation;
            $this->annotations = $annotations;
        }

        return $this->save();
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeForInspection(Builder $query, string $inspectionId): void
    {
        $query->where('inspection_id', $inspectionId);
    }

    public function scopePassed(Builder $query): void
    {
        $query->where('result', 'pass');
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('result', 'fail');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('result', 'pending');
    }

    public function scopeNonCompliant(Builder $query): void
    {
        $query->where('non_compliant', true);
    }

    public function scopeCritical(Builder $query): void
    {
        $query->where('is_critical', true);
    }

    public function scopeByCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    public function scopeBySeverity(Builder $query, string $severity): void
    {
        $query->where('severity', $severity);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sequence_order');
    }
}
