<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractorCertification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'contractor_id',
        'certification_type',
        'certification_number',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'document_path',
        'document_hash',
        'is_verified',
        'verified_by',
        'verified_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('contractor_certifications.branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-update status based on expiry date
        static::saving(function ($certification) {
            if ($certification->expiry_date && $certification->expiry_date->isPast()) {
                $certification->status = 'expired';
            }
        });
    }

    /**
     * Get the branch that owns the certification
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
     * Get the user who verified the certification
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if certification is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if certification is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Check if certification requires urgent renewal (within 14 days)
     */
    public function requiresUrgentRenewal(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays(14));
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
     * Check if document is uploaded
     */
    public function hasDocument(): bool
    {
        return !empty($this->document_path);
    }

    /**
     * Verify document hash integrity
     */
    public function verifyDocumentIntegrity(?string $currentHash = null): bool
    {
        if (!$this->document_hash || !$currentHash) {
            return false;
        }

        return hash_equals($this->document_hash, $currentHash);
    }

    /**
     * Scope to filter valid certifications
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', 'valid')
            ->where('expiry_date', '>', now());
    }

    /**
     * Scope to filter expired certifications
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
            ->orWhere('expiry_date', '<', now());
    }

    /**
     * Scope to filter expiring certifications
     */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
    }

    /**
     * Scope to filter by certification type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('certification_type', $type);
    }

    /**
     * Scope to filter verified certifications
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter pending verification
     */
    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('status', 'pending_verification');
    }
}
