<?php

namespace App\Modules\TrainingManagement\Models;

use App\Models\Branch;
use App\Models\User;
use Database\Factories\Modules\TrainingManagement\CertificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class Certification extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CertificationFactory
    {
        return CertificationFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'user_id',
        'training_record_id',
        'certification_type',
        'certification_name',
        'license_number',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'is_expired',
        'days_until_expiry',
        'license_classes',
        'restrictions',
        'endorsements',
        'verification_status',
        'last_verified_date',
        'verified_by_user_id',
        'verification_notes',
        'certificate_file_path',
        'supporting_documents',
        'reminder_days_before',
        'reminder_sent',
        'last_reminder_sent_at',
        'auto_renewal_required',
        'competencies_covered',
        'proficiency_level',
        'renewal_cost',
        'training_provider',
        'provider_contact',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_expired' => 'boolean',
        'days_until_expiry' => 'integer',
        'license_classes' => 'array',
        'restrictions' => 'array',
        'endorsements' => 'array',
        'last_verified_date' => 'date',
        'supporting_documents' => 'array',
        'reminder_days_before' => 'integer',
        'reminder_sent' => 'boolean',
        'last_reminder_sent_at' => 'datetime',
        'auto_renewal_required' => 'boolean',
        'competencies_covered' => 'array',
        'proficiency_level' => 'integer',
        'renewal_cost' => 'decimal:2',
    ];

    /**
     * Boot the model and add global scopes
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-update expiry status
        static::saving(function ($certification) {
            $certification->is_expired = $certification->expiry_date && $certification->expiry_date->isPast();
            $certification->days_until_expiry = $certification->expiry_date
                ? now()->diffInDays($certification->expiry_date, false)
                : null;
        });
    }

    // ============================================================================
    // RELATIONSHIPS
    // ============================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingRecord(): BelongsTo
    {
        return $this->belongsTo(TrainingRecord::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if certification is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if certification is expiring soon
     */
    public function isExpiringSoon(int $days = null): bool
    {
        $days = $days ?? $this->reminder_days_before;

        return $this->expiry_date &&
               $this->expiry_date->diffInDays(now()) <= $days &&
               $this->expiry_date->isFuture();
    }

    /**
     * Check if certification is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if certification is suspended or revoked
     */
    public function isInactive(): bool
    {
        return in_array($this->verification_status, ['suspended', 'revoked']);
    }

    /**
     * Get verification status color
     */
    public function getVerificationColorAttribute(): string
    {
        return match($this->verification_status) {
            'verified' => 'success',
            'pending' => 'warning',
            'expired' => 'destructive',
            'suspended', 'revoked' => 'destructive',
            default => 'secondary',
        };
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if driver license
     */
    public function isDriverLicense(): bool
    {
        return in_array($this->certification_type, [
            'driver_license',
            'heavy_vehicle_license',
            'dangerous_goods_license'
        ]);
    }

    /**
     * Get license class display
     */
    public function getLicenseClassesDisplayAttribute(): string
    {
        if (!$this->license_classes || count($this->license_classes) === 0) {
            return 'N/A';
        }

        return implode(', ', $this->license_classes);
    }

    /**
     * Check if has specific license class
     */
    public function hasLicenseClass(string $class): bool
    {
        return $this->license_classes && in_array($class, $this->license_classes);
    }

    /**
     * Get proficiency level description
     */
    public function getProficiencyLevelDescriptionAttribute(): string
    {
        return match($this->proficiency_level) {
            1 => 'Beginner',
            2 => 'Basic',
            3 => 'Intermediate',
            4 => 'Advanced',
            5 => 'Expert',
            default => 'Not Assessed',
        };
    }

    /**
     * Check if renewal needed
     */
    public function needsRenewal(): bool
    {
        return $this->auto_renewal_required &&
               $this->isExpiringSoon($this->reminder_days_before);
    }

    /**
     * Verify certification
     */
    public function verify(User $verifiedBy, ?string $notes = null): bool
    {
        $this->verification_status = 'verified';
        $this->last_verified_date = now();
        $this->verified_by_user_id = $verifiedBy->id;
        $this->verification_notes = $notes;

        return $this->save();
    }

    /**
     * Suspend certification
     */
    public function suspend(?string $reason = null): bool
    {
        $this->verification_status = 'suspended';
        $this->verification_notes = $reason;

        return $this->save();
    }

    /**
     * Revoke certification
     */
    public function revoke(?string $reason = null): bool
    {
        $this->verification_status = 'revoked';
        $this->verification_notes = $reason;

        return $this->save();
    }

    /**
     * Renew certification
     */
    public function renew(int $validityMonths): bool
    {
        $this->issue_date = now();
        $this->expiry_date = now()->addMonths($validityMonths);
        $this->is_expired = false;
        $this->verification_status = 'pending';
        $this->reminder_sent = false;

        return $this->save();
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeForUser(Builder $query, string $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function scopeExpired(Builder $query): void
    {
        $query->where('expiry_date', '<', now())
            ->orWhere('is_expired', true);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): void
    {
        $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now())
            ->where('is_expired', false);
    }

    public function scopeVerified(Builder $query): void
    {
        $query->where('verification_status', 'verified');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('verification_status', 'pending');
    }

    public function scopeByType(Builder $query, string $type): void
    {
        $query->where('certification_type', $type);
    }

    public function scopeDriverLicenses(Builder $query): void
    {
        $query->whereIn('certification_type', [
            'driver_license',
            'heavy_vehicle_license',
            'dangerous_goods_license'
        ]);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_expired', false)
            ->whereNotIn('verification_status', ['suspended', 'revoked']);
    }
}
