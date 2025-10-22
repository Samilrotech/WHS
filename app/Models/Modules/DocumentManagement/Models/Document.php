<?php

namespace App\Models\Modules\DocumentManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'category_id',
        'uploaded_by',
        'title',
        'document_number',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'mime_type',
        'file_hash',
        'current_version',
        'tags',
        'metadata',
        'requires_review',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'review_status',
        'expiry_date',
        'is_expired',
        'visibility',
        'restricted_to',
        'status',
    ];

    protected $casts = [
        'branch_id' => 'string',
        'uploaded_by' => 'string',
        'reviewed_by' => 'string',
        'file_size' => 'integer',
        'current_version' => 'integer',
        'tags' => 'array',
        'metadata' => 'array',
        'restricted_to' => 'array',
        'requires_review' => 'boolean',
        'is_expired' => 'boolean',
        'reviewed_at' => 'datetime',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('documents.branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-generate document number on creation
        static::creating(function ($document) {
            if (!$document->document_number) {
                $document->document_number = 'DOC-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
            }
        });

        // Check expiry on retrieval
        static::retrieved(function ($document) {
            if ($document->expiry_date && $document->expiry_date->isPast() && !$document->is_expired) {
                $document->update(['is_expired' => true]);
            }
        });
    }

    /**
     * Branch relationship
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Category relationship
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    /**
     * Uploader relationship
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Reviewer relationship
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    /**
     * Versions relationship
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)
            ->orderBy('version_number', 'desc');
    }

    /**
     * Access logs relationship
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if document is approved
     */
    public function isApproved(): bool
    {
        return $this->review_status === 'approved';
    }

    /**
     * Check if document is pending review
     */
    public function isPendingReview(): bool
    {
        return $this->requires_review && $this->review_status === 'pending';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected(): bool
    {
        return $this->review_status === 'rejected';
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast() || $this->is_expired;
    }

    /**
     * Check if document is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date || $this->isExpired()) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= 30;
    }

    /**
     * Check if user has access to document
     */
    public function userHasAccess(\App\Models\User $user): bool
    {
        // Public documents are accessible to all
        if ($this->visibility === 'public') {
            return true;
        }

        // Private documents only accessible to uploader and admins
        if ($this->visibility === 'private') {
            return $user->id === $this->uploaded_by || $user->hasRole('Admin');
        }

        // Restricted documents check restricted_to array
        if ($this->visibility === 'restricted') {
            if ($user->hasRole('Admin')) {
                return true;
            }

            if (!$this->restricted_to) {
                return false;
            }

            // Check if user ID or any user role is in restricted list
            $userRoles = $user->roles->pluck('name')->toArray();
            $allowedIds = array_filter($this->restricted_to, 'is_numeric');
            $allowedRoles = array_filter($this->restricted_to, 'is_string');

            return in_array($user->id, $allowedIds) ||
                   !empty(array_intersect($userRoles, $allowedRoles));
        }

        return false;
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Verify file integrity using hash
     */
    public function verifyIntegrity(): bool
    {
        if (!file_exists(storage_path('app/' . $this->file_path))) {
            return false;
        }

        $currentHash = hash_file('sha256', storage_path('app/' . $this->file_path));
        return $currentHash === $this->file_hash;
    }

    /**
     * Get latest version
     */
    public function latestVersion(): ?DocumentVersion
    {
        return $this->versions()->first();
    }
}
