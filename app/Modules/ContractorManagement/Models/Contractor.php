<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contractor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'contractor_company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'emergency_contact_name',
        'emergency_contact_phone',
        'driver_license_number',
        'driver_license_expiry',
        'induction_completed',
        'induction_completion_date',
        'induction_expiry_date',
        'inducted_by',
        'site_access_granted',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'driver_license_expiry' => 'date',
        'induction_completed' => 'boolean',
        'induction_completion_date' => 'date',
        'induction_expiry_date' => 'date',
        'site_access_granted' => 'boolean',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('contractors.branch_id', auth()->user()->branch_id);
            }
        });
    }

    /**
     * Get the branch that owns the contractor
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the contractor company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(ContractorCompany::class, 'contractor_company_id');
    }

    /**
     * Get the user who inducted this contractor
     */
    public function inductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inducted_by');
    }

    /**
     * Get all inductions for this contractor
     */
    public function inductions(): HasMany
    {
        return $this->hasMany(ContractorInduction::class);
    }

    /**
     * Get all certifications for this contractor
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(ContractorCertification::class);
    }

    /**
     * Get all SWMS acknowledgments for this contractor
     */
    public function swmsAcknowledgments(): HasMany
    {
        return $this->hasMany(SwmsAcknowledgment::class);
    }

    /**
     * Get all sign-in logs (polymorphic)
     */
    public function signInLogs(): MorphMany
    {
        return $this->morphMany(SignInLog::class, 'signable');
    }

    /**
     * Get contractor's full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if induction is expiring soon (within 30 days)
     */
    public function isInductionExpiringSoon(): bool
    {
        if (!$this->induction_expiry_date) {
            return false;
        }

        return $this->induction_expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Check if contractor has valid induction
     */
    public function hasValidInduction(): bool
    {
        return $this->induction_completed &&
               $this->induction_expiry_date &&
               $this->induction_expiry_date->isFuture();
    }

    /**
     * Check if contractor is currently signed in
     */
    public function isSignedIn(): bool
    {
        return $this->signInLogs()
            ->where('status', 'signed_in')
            ->whereNull('signed_out_at')
            ->exists();
    }

    /**
     * Scope to filter active contractors
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter contractors with site access
     */
    public function scopeWithSiteAccess(Builder $query): Builder
    {
        return $query->where('site_access_granted', true);
    }

    /**
     * Scope to filter contractors with valid induction
     */
    public function scopeInductionValid(Builder $query): Builder
    {
        return $query->where('induction_completed', true)
            ->where('induction_expiry_date', '>', now());
    }

    /**
     * Scope to filter contractors with expiring induction
     */
    public function scopeExpiringInduction(Builder $query): Builder
    {
        return $query->whereBetween('induction_expiry_date', [now(), now()->addDays(30)]);
    }
}
