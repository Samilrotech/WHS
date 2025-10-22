<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractorInduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'contractor_id',
        'induction_module_id',
        'started_at',
        'completed_at',
        'time_spent_minutes',
        'video_watched',
        'video_progress_percentage',
        'quiz_score',
        'quiz_attempts',
        'quiz_passed',
        'expiry_date',
        'status',
        'certificate_number',
        'certificate_issued_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent_minutes' => 'integer',
        'video_watched' => 'boolean',
        'video_progress_percentage' => 'integer',
        'quiz_score' => 'integer',
        'quiz_attempts' => 'integer',
        'quiz_passed' => 'boolean',
        'expiry_date' => 'date',
        'certificate_issued_at' => 'datetime',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('contractor_inductions.branch_id', auth()->user()->branch_id);
            }
        });

        // Generate certificate number on completion
        static::creating(function ($induction) {
            if ($induction->status === 'completed' && !$induction->certificate_number) {
                $induction->certificate_number = 'IND-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the branch that owns the induction
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the contractor
     */
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Get the induction module
     */
    public function inductionModule(): BelongsTo
    {
        return $this->belongsTo(InductionModule::class);
    }

    /**
     * Check if induction is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if induction is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute(): int
    {
        $percentage = 0;

        // Video watched (50%)
        if ($this->video_watched) {
            $percentage += 50;
        }

        // Quiz passed (50%)
        if ($this->quiz_passed) {
            $percentage += 50;
        }

        return $percentage;
    }

    /**
     * Scope to filter completed inductions
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter expired inductions
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
            ->orWhere('expiry_date', '<', now());
    }

    /**
     * Scope to filter expiring inductions
     */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
    }
}
