<?php

namespace App\Modules\CAPAManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAPAAction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'capa_actions';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Modules\CAPAManagement\CAPAActionFactory::new();
    }

    protected $fillable = [
        'capa_id',
        'assigned_to_user_id',
        'title',
        'description',
        'sequence_order',
        'due_date',
        'completed_date',
        'status',
        'is_completed',
        'completed_by_user_id',
        'completion_notes',
        'evidence_paths',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'is_completed' => 'boolean',
        'evidence_paths' => 'array',
    ];

    /**
     * Get the CAPA that owns the action
     */
    public function capa(): BelongsTo
    {
        return $this->belongsTo(CAPA::class);
    }

    /**
     * Get the user assigned to this action
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Get the user who completed this action
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    /**
     * Check if action is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->is_completed) {
            return false;
        }

        return $this->due_date->isPast();
    }

    /**
     * Scope to only include overdue actions
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
            ->where('due_date', '<', now());
    }

    /**
     * Scope to only include pending actions
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope to only include completed actions
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
}
