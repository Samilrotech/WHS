<?php

namespace App\Modules\ComplianceReporting\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'report_number',
        'title',
        'report_type',
        'period',
        'period_start',
        'period_end',
        'report_date',
        'requirements_included',
        'metrics',
        'executive_summary',
        'key_findings',
        'recommendations',
        'status',
        'created_by',
        'reviewed_by',
        'approved_by',
        'approved_at',
        'file_path',
        'file_name',
        'file_size',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'report_date' => 'date',
        'approved_at' => 'datetime',
        'requirements_included' => 'array',
        'metrics' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('compliance_reports.branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-generate report number
        static::creating(function ($report) {
            if (!$report->report_number) {
                $report->report_number = 'REP-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the branch that owns the report
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the creator of the report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reviewer of the report
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the approver of the report
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if report is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if report is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if report is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'under-review' => 'info',
            'approved' => 'success',
            'published' => 'primary',
            'archived' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get report type label
     */
    public function getReportTypeLabel(): string
    {
        return match($this->report_type) {
            'periodic' => 'Periodic Report',
            'audit' => 'Audit Report',
            'incident-based' => 'Incident-Based Report',
            'regulatory' => 'Regulatory Report',
            'custom' => 'Custom Report',
            default => ucfirst($this->report_type),
        };
    }

    /**
     * Scope: Draft reports
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Published reports
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Approved reports
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Reports for a specific period
     */
    public function scopeForPeriod(Builder $query, string $period): Builder
    {
        return $query->where('period', $period);
    }
}
