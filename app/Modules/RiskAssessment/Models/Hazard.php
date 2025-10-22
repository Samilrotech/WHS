<?php

namespace App\Modules\RiskAssessment\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Hazard extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'risk_assessment_id',
        'branch_id',
        'hazard_type',
        'description',
        'potential_consequences',
        'persons_at_risk',
        'affected_groups',
    ];

    protected $casts = [
        'affected_groups' => 'array',
    ];

    /**
     * Boot method for global scope
     */
    protected static function booted(): void
    {
        // Branch isolation global scope
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-set branch_id from parent risk assessment
        static::creating(function (Hazard $hazard) {
            if (!$hazard->branch_id && $hazard->riskAssessment) {
                $hazard->branch_id = $hazard->riskAssessment->branch_id;
            }
        });
    }

    /**
     * Relationships
     */
    public function riskAssessment(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function controlMeasures(): HasMany
    {
        return $this->hasMany(ControlMeasure::class);
    }

    /**
     * Scopes
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('hazard_type', $type);
    }
}
