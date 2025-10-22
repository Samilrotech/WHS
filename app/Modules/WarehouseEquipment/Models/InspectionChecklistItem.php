<?php

namespace App\Modules\WarehouseEquipment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionChecklistItem extends Model
{
    use HasFactory, HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\InspectionChecklistItemFactory::new();
    }

    protected $fillable = [
        'inspection_id',
        'sequence_order',
        'item_code',
        'category',
        'question',
        'item_type',
        'result',
        'response_value',
        'response_notes',
        'responded_at',
        'defect_identified',
        'defect_description',
        'corrective_action_required',
        'defect_severity',
        'is_critical',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'defect_identified' => 'boolean',
        'is_critical' => 'boolean',
    ];

    /**
     * Get the inspection this item belongs to
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(EquipmentInspection::class, 'inspection_id');
    }

    /**
     * Mark item as passed
     */
    public function markAsPassed(?string $notes = null): void
    {
        $this->update([
            'result' => 'pass',
            'response_notes' => $notes,
            'responded_at' => now(),
            'defect_identified' => false,
        ]);
    }

    /**
     * Mark item as failed with defect details
     */
    public function markAsFailed(string $severity, string $description, ?string $correctiveAction = null): void
    {
        $this->update([
            'result' => 'fail',
            'defect_identified' => true,
            'defect_severity' => $severity,
            'defect_description' => $description,
            'corrective_action_required' => $correctiveAction,
            'responded_at' => now(),
        ]);
    }

    /**
     * Mark item as not applicable
     */
    public function markAsNA(?string $notes = null): void
    {
        $this->update([
            'result' => 'na',
            'response_notes' => $notes,
            'responded_at' => now(),
            'defect_identified' => false,
        ]);
    }
}
