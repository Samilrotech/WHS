<?php

namespace App\Modules\ComplianceReporting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requirement_id',
        'check_number',
        'check_date',
        'checked_by',
        'result',
        'score',
        'findings',
        'recommendations',
        'evidence_files',
        'notes',
        'requires_action',
        'action_due_date',
        'action_owner_id',
        'action_status',
    ];

    protected $casts = [
        'check_date' => 'date',
        'action_due_date' => 'date',
        'evidence_files' => 'array',
        'requires_action' => 'boolean',
        'score' => 'integer',
    ];

    /**
     * Apply global scope and auto-generate check number
     */
    protected static function booted(): void
    {
        // Auto-generate check number
        static::creating(function ($check) {
            if (!$check->check_number) {
                $check->check_number = 'CC-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the requirement this check belongs to
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ComplianceRequirement::class, 'requirement_id');
    }

    /**
     * Get the user who performed the check
     */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Get the action owner
     */
    public function actionOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_owner_id');
    }

    /**
     * Get all actions for this check
     */
    public function actions(): HasMany
    {
        return $this->hasMany(ComplianceAction::class, 'check_id');
    }

    /**
     * Check if result is pass
     */
    public function isPassed(): bool
    {
        return $this->result === 'pass';
    }

    /**
     * Check if result is fail
     */
    public function isFailed(): bool
    {
        return $this->result === 'fail';
    }

    /**
     * Check if action is overdue
     */
    public function isActionOverdue(): bool
    {
        if (!$this->requires_action || !$this->action_due_date) {
            return false;
        }
        return $this->action_due_date->isPast() && $this->action_status !== 'completed';
    }

    /**
     * Get result badge color
     */
    public function getResultBadgeColor(): string
    {
        return match($this->result) {
            'pass' => 'success',
            'fail' => 'danger',
            'partial' => 'warning',
            'not-applicable' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get action status badge color
     */
    public function getActionStatusBadgeColor(): string
    {
        return match($this->action_status) {
            'pending' => 'warning',
            'in-progress' => 'info',
            'completed' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Scope: Passed checks
     */
    public function scopePassed(Builder $query): Builder
    {
        return $query->where('result', 'pass');
    }

    /**
     * Scope: Failed checks
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('result', 'fail');
    }

    /**
     * Scope: Checks requiring action
     */
    public function scopeRequiresAction(Builder $query): Builder
    {
        return $query->where('requires_action', true);
    }

    /**
     * Scope: Overdue actions
     */
    public function scopeActionOverdue(Builder $query): Builder
    {
        return $query->where('requires_action', true)
            ->where('action_due_date', '<', now())
            ->where('action_status', '!=', 'completed');
    }
}
