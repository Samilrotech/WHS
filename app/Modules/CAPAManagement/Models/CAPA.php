<?php

namespace App\Modules\CAPAManagement\Models;

use App\Models\Branch;
use App\Models\User;
use App\Modules\IncidentManagement\Models\Incident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CAPA extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Modules\CAPAManagement\CAPAFactory::new();
    }

    protected $table = 'capas';

    protected $fillable = [
        'branch_id',
        'incident_id',
        'raised_by_user_id',
        'assigned_to_user_id',
        'capa_number',
        'type',
        'title',
        'description',
        'problem_statement',
        'root_cause_analysis',
        'five_whys',
        'contributing_factors',
        'proposed_action',
        'implementation_steps',
        'resources_required',
        'estimated_cost',
        'target_completion_date',
        'actual_completion_date',
        'estimated_hours',
        'actual_hours',
        'status',
        'priority',
        'verification_date',
        'verified_by_user_id',
        'verification_method',
        'verification_results',
        'effectiveness_confirmed',
        'approved_by_user_id',
        'approval_date',
        'approval_notes',
        'rejection_reason',
        'closed_by_user_id',
        'closure_date',
        'closure_notes',
        'attachment_paths',
        'notes',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'verification_date' => 'date',
        'approval_date' => 'date',
        'closure_date' => 'date',
        'effectiveness_confirmed' => 'boolean',
        'implementation_steps' => 'array',
        'attachment_paths' => 'array',
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
     * Get the branch that owns the CAPA
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the incident that triggered this CAPA (if any)
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * Get the user who raised the CAPA
     */
    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by_user_id');
    }

    /**
     * Get the user assigned to implement the CAPA
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Get the user who verified the CAPA
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /**
     * Get the user who approved the CAPA
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Get the user who closed the CAPA
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    /**
     * Get all actions for this CAPA
     */
    public function actions(): HasMany
    {
        return $this->hasMany(CAPAAction::class, 'capa_id');
    }

    /**
     * Check if CAPA is overdue
     */
    public function isOverdue(): bool
    {
        if (in_array($this->status, ['completed', 'verified', 'closed', 'cancelled'])) {
            return false;
        }

        return $this->target_completion_date->isPast();
    }

    /**
     * Check if CAPA is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if CAPA is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if CAPA is completed but not verified
     */
    public function isPendingVerification(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get completion percentage based on actions
     */
    public function getCompletionPercentageAttribute(): int
    {
        $totalActions = $this->actions()->count();

        if ($totalActions === 0) {
            return 0;
        }

        $completedActions = $this->actions()->where('is_completed', true)->count();

        return round(($completedActions / $totalActions) * 100);
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->target_completion_date, false);
    }

    /**
     * Scope to only include overdue CAPAs
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', ['completed', 'verified', 'closed', 'cancelled'])
            ->where('target_completion_date', '<', now());
    }

    /**
     * Scope to only include pending approval CAPAs
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope to only include in progress CAPAs
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to only include pending verification CAPAs
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter by priority
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
