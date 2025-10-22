<?php

namespace App\Modules\RiskAssessment\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class RiskAssessment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'user_id',
        'category',
        'task_description',
        'location',
        'assessment_date',
        'initial_likelihood',
        'initial_consequence',
        'initial_risk_score',
        'initial_risk_level',
        'residual_likelihood',
        'residual_consequence',
        'residual_risk_score',
        'residual_risk_level',
        'status',
        'approved_by',
        'approved_at',
        'review_date',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'review_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot method for global scope and automatic calculations
     */
    protected static function booted(): void
    {
        // Branch isolation global scope
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-calculate risk scores before creating
        static::creating(function (RiskAssessment $assessment) {
            // Calculate initial risk
            $initialScore = $assessment->initial_likelihood * $assessment->initial_consequence;
            $assessment->initial_risk_score = $initialScore;
            $assessment->initial_risk_level = static::calculateRiskLevel($initialScore);

            // Calculate residual risk
            $residualScore = $assessment->residual_likelihood * $assessment->residual_consequence;
            $assessment->residual_risk_score = $residualScore;
            $assessment->residual_risk_level = static::calculateRiskLevel($residualScore);

            // Auto-set branch_id from auth user
            if (!$assessment->branch_id) {
                $assessment->branch_id = auth()->user()->branch_id;
            }
        });

        // Auto-calculate risk scores before updating
        static::updating(function (RiskAssessment $assessment) {
            if ($assessment->isDirty(['initial_likelihood', 'initial_consequence'])) {
                $initialScore = $assessment->initial_likelihood * $assessment->initial_consequence;
                $assessment->initial_risk_score = $initialScore;
                $assessment->initial_risk_level = static::calculateRiskLevel($initialScore);
            }

            if ($assessment->isDirty(['residual_likelihood', 'residual_consequence'])) {
                $residualScore = $assessment->residual_likelihood * $assessment->residual_consequence;
                $assessment->residual_risk_score = $residualScore;
                $assessment->residual_risk_level = static::calculateRiskLevel($residualScore);
            }
        });
    }

    /**
     * Calculate risk level from score
     * Green: 1-5, Yellow: 6-11, Orange: 12-19, Red: 20-25
     */
    public static function calculateRiskLevel(int $score): string
    {
        return match (true) {
            $score <= 5 => 'green',
            $score <= 11 => 'yellow',
            $score <= 19 => 'orange',
            default => 'red',
        };
    }

    /**
     * Relationships
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hazards(): HasMany
    {
        return $this->hasMany(Hazard::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByRiskLevel(Builder $query, string $level): Builder
    {
        return $query->where('residual_risk_level', $level);
    }

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->whereIn('residual_risk_level', ['orange', 'red']);
    }
}
