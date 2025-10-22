<?php

namespace App\Models\Modules\DocumentManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'parent_id',
        'display_order',
        'requires_approval',
        'retention_days',
        'status',
    ];

    protected $casts = [
        'branch_id' => 'string',
        'requires_approval' => 'boolean',
        'retention_days' => 'integer',
        'display_order' => 'integer',
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
                $builder->where('document_categories.branch_id', auth()->user()->branch_id);
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
     * Parent category relationship
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    /**
     * Child categories relationship
     */
    public function children(): HasMany
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')
            ->orderBy('display_order');
    }

    /**
     * Documents relationship
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    /**
     * Check if category requires approval for documents
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    /**
     * Check if category has retention policy
     */
    public function hasRetentionPolicy(): bool
    {
        return !is_null($this->retention_days);
    }

    /**
     * Get retention policy expiry date for a given created date
     */
    public function getRetentionExpiryDate(\Carbon\Carbon $createdAt): ?\Carbon\Carbon
    {
        if (!$this->hasRetentionPolicy()) {
            return null;
        }

        return $createdAt->copy()->addDays($this->retention_days);
    }

    /**
     * Check if category is top-level (no parent)
     */
    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Get all ancestor categories
     */
    public function ancestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Get breadcrumb trail for category
     */
    public function breadcrumb(): string
    {
        $trail = $this->ancestors()->pluck('name')->toArray();
        $trail[] = $this->name;

        return implode(' > ', $trail);
    }
}
