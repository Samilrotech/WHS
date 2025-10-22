<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InductionModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'title',
        'description',
        'content',
        'video_url',
        'video_duration_minutes',
        'has_quiz',
        'pass_mark_percentage',
        'validity_months',
        'is_mandatory',
        'status',
        'display_order',
    ];

    protected $casts = [
        'video_duration_minutes' => 'integer',
        'has_quiz' => 'boolean',
        'pass_mark_percentage' => 'integer',
        'validity_months' => 'integer',
        'is_mandatory' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('induction_modules.branch_id', auth()->user()->branch_id);
            }
        });
    }

    /**
     * Get the branch that owns the induction module
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all contractor inductions using this module
     */
    public function contractorInductions(): HasMany
    {
        return $this->hasMany(ContractorInduction::class);
    }

    /**
     * Get completion rate for this module
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->contractorInductions()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->contractorInductions()
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Get average quiz score for this module
     */
    public function getAverageQuizScoreAttribute(): ?float
    {
        if (!$this->has_quiz) {
            return null;
        }

        return $this->contractorInductions()
            ->where('quiz_passed', true)
            ->avg('quiz_score');
    }

    /**
     * Scope to filter active modules
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter mandatory modules
     */
    public function scopeMandatory(Builder $query): Builder
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }
}
