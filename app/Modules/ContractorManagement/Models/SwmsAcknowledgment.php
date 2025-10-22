<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SwmsAcknowledgment extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'contractor_id',
        'swms_title',
        'swms_reference_number',
        'swms_version_date',
        'work_activity',
        'acknowledged_at',
        'signature_path',
        'ip_address',
        'declaration_text',
        'verified_by',
        'verified_at',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'swms_version_date' => 'date',
        'acknowledged_at' => 'datetime',
        'verified_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('swms_acknowledgments.branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-update status based on expiry date
        static::saving(function ($acknowledgment) {
            if ($acknowledgment->expiry_date && $acknowledgment->expiry_date->isPast()) {
                $acknowledgment->status = 'expired';
            }
        });
    }

    /**
     * Get the branch
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
     * Get the user who verified the acknowledgment
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if acknowledgment is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if acknowledgment is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Check if acknowledgment requires urgent renewal (within 7 days)
     */
    public function requiresUrgentRenewal(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays(7));
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if acknowledgment has digital signature
     */
    public function hasSignature(): bool
    {
        return !empty($this->signature_path);
    }

    /**
     * Check if acknowledgment is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_by &&
               $this->verified_at &&
               $this->status === 'active';
    }

    /**
     * Get time since acknowledgment in hours
     */
    public function getTimeSinceAcknowledgmentAttribute(): ?int
    {
        if (!$this->acknowledged_at) {
            return null;
        }

        return $this->acknowledged_at->diffInHours(now());
    }

    /**
     * Get acknowledgment age in days
     */
    public function getAgeInDaysAttribute(): ?int
    {
        if (!$this->acknowledged_at) {
            return null;
        }

        return $this->acknowledged_at->diffInDays(now());
    }

    /**
     * Check if SWMS is current version
     */
    public function isCurrentVersion(): bool
    {
        // Check if there are newer versions of this SWMS
        return !static::where('swms_reference_number', $this->swms_reference_number)
            ->where('branch_id', $this->branch_id)
            ->where('swms_version_date', '>', $this->swms_version_date)
            ->exists();
    }

    /**
     * Scope to filter active acknowledgments
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
            });
    }

    /**
     * Scope to filter expired acknowledgments
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
            ->orWhere('expiry_date', '<', now());
    }

    /**
     * Scope to filter expiring acknowledgments
     */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
    }

    /**
     * Scope to filter by SWMS reference number
     */
    public function scopeForSwms(Builder $query, string $referenceNumber): Builder
    {
        return $query->where('swms_reference_number', $referenceNumber);
    }

    /**
     * Scope to filter by work activity
     */
    public function scopeForActivity(Builder $query, string $activity): Builder
    {
        return $query->where('work_activity', 'like', "%{$activity}%");
    }

    /**
     * Scope to filter verified acknowledgments
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_by')
            ->whereNotNull('verified_at');
    }

    /**
     * Scope to filter unverified acknowledgments
     */
    public function scopeUnverified(Builder $query): Builder
    {
        return $query->whereNull('verified_by')
            ->orWhereNull('verified_at');
    }

    /**
     * Scope to filter by contractor
     */
    public function scopeForContractor(Builder $query, int $contractorId): Builder
    {
        return $query->where('contractor_id', $contractorId);
    }

    /**
     * Scope to filter recent acknowledgments (within days)
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('acknowledged_at', '>=', now()->subDays($days));
    }
}
