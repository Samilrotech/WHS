<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractorCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'company_name',
        'abn',
        'acn',
        'trading_name',
        'primary_contact_name',
        'primary_contact_phone',
        'primary_contact_email',
        'address',
        'public_liability_insurer',
        'public_liability_policy_number',
        'public_liability_expiry_date',
        'public_liability_coverage_amount',
        'workers_comp_insurer',
        'workers_comp_policy_number',
        'workers_comp_expiry_date',
        'is_verified',
        'verification_date',
        'verified_by',
        'performance_rating',
        'notes',
        'status',
    ];

    protected $casts = [
        'public_liability_expiry_date' => 'date',
        'public_liability_coverage_amount' => 'decimal:2',
        'workers_comp_expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verification_date' => 'date',
        'performance_rating' => 'decimal:2',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('contractor_companies.branch_id', auth()->user()->branch_id);
            }
        });
    }

    /**
     * Get the branch that owns the contractor company
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who verified the company
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all contractors for this company
     */
    public function contractors(): HasMany
    {
        return $this->hasMany(Contractor::class);
    }

    /**
     * Check if public liability insurance is expiring soon (within 30 days)
     */
    public function isPublicLiabilityExpiringSoon(): bool
    {
        if (!$this->public_liability_expiry_date) {
            return false;
        }

        return $this->public_liability_expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Check if workers comp insurance is expiring soon (within 30 days)
     */
    public function isWorkersCompExpiringSoon(): bool
    {
        if (!$this->workers_comp_expiry_date) {
            return false;
        }

        return $this->workers_comp_expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Check if company has valid insurance
     */
    public function hasValidInsurance(): bool
    {
        return $this->public_liability_expiry_date &&
               $this->public_liability_expiry_date->isFuture() &&
               $this->workers_comp_expiry_date &&
               $this->workers_comp_expiry_date->isFuture();
    }

    /**
     * Scope to filter active companies
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter verified companies
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter companies with expiring insurance
     */
    public function scopeExpiringInsurance(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereBetween('public_liability_expiry_date', [now(), now()->addDays(30)])
              ->orWhereBetween('workers_comp_expiry_date', [now(), now()->addDays(30)]);
        });
    }
}
