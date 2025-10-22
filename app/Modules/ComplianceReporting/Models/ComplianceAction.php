<?php

namespace App\Modules\ComplianceReporting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requirement_id',
        'check_id',
        'action_number',
        'title',
        'description',
        'priority',
        'assigned_to',
        'assigned_by',
        'due_date',
        'completed_date',
        'estimated_hours',
        'actual_hours',
        'status',
        'progress',
        'completion_notes',
        'evidence_files',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'verified_at' => 'datetime',
        'evidence_files' => 'array',
        'estimated_hours' => 'integer',
        'actual_hours' => 'integer',
        'progress' => 'integer',
    ];

    /**
     * Apply global scope and auto-generate action number
     */
    protected static function booted(): void
    {
        // Auto-generate action number
        static::creating(function ($action) {
            if (!$action->action_number) {
                $action->action_number = 'CA-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
            }
        });

        // Update status based on due date
        static::retrieved(function ($action) {
            if ($action->due_date && $action->due_date->isPast() && $action->status === 'pending') {
                $action->update(['status' => 'overdue']);
            }
        });
    }

    /**
     * Get the requirement this action belongs to
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ComplianceRequirement::class, 'requirement_id');
    }

    /**
     * Get the check this action belongs to
     */
    public function check(): BelongsTo
    {
        return $this->belongsTo(ComplianceCheck::class, 'check_id');
    }

    /**
     * Get the user assigned to this action
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who assigned this action
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who verified completion
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if action is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if action is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' ||
               ($this->due_date && $this->due_date->isPast() && !$this->isCompleted());
    }

    /**
     * Check if action is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDue(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeColor(): string
    {
        return match($this->priority) {
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
            'pending' => 'warning',
            'in-progress' => 'info',
            'completed' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): int
    {
        return min(100, max(0, $this->progress));
    }

    /**
     * Get progress bar color
     */
    public function getProgressBarColor(): string
    {
        $progress = $this->getProgressPercentage();

        if ($progress >= 75) {
            return 'success';
        } elseif ($progress >= 50) {
            return 'info';
        } elseif ($progress >= 25) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Scope: Pending actions
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: In progress actions
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in-progress');
    }

    /**
     * Scope: Completed actions
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Overdue actions
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($q2) {
                  $q2->where('due_date', '<', now())
                     ->where('status', '!=', 'completed');
              });
        });
    }

    /**
     * Scope: Assigned to user
     */
    public function scopeAssignedTo(Builder $query, string $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: High priority
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }
}
