<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SignInLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'signable_id',
        'signable_type',
        'signed_in_at',
        'signed_out_at',
        'location',
        'purpose',
        'work_description',
        'areas_accessed',
        'ppe_acknowledged',
        'emergency_procedures_acknowledged',
        'ppe_items',
        'temperature_check',
        'health_declaration',
        'entry_method',
        'exit_method',
        'signature_in',
        'signature_out',
        'status',
        'notes',
    ];

    protected $casts = [
        'signed_in_at' => 'datetime',
        'signed_out_at' => 'datetime',
        'areas_accessed' => 'array',
        'ppe_acknowledged' => 'boolean',
        'emergency_procedures_acknowledged' => 'boolean',
        'ppe_items' => 'array',
        'temperature_check' => 'decimal:1',
        'health_declaration' => 'boolean',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('sign_in_logs.branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-update status based on sign-out
        static::saving(function ($log) {
            if ($log->signed_out_at) {
                $log->status = 'signed_out';
            } elseif ($log->signed_in_at && !$log->signed_out_at) {
                // Check if overdue (more than 12 hours)
                if ($log->signed_in_at->addHours(12)->isPast()) {
                    $log->status = 'overdue';
                } else {
                    $log->status = 'signed_in';
                }
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
     * Get the signable model (Contractor or Visitor)
     */
    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get duration on site in minutes
     */
    public function getTimeOnSiteAttribute(): ?int
    {
        if (!$this->signed_in_at) {
            return null;
        }

        $endTime = $this->signed_out_at ?? now();
        return $this->signed_in_at->diffInMinutes($endTime);
    }

    /**
     * Get duration on site formatted as HH:MM
     */
    public function getFormattedTimeOnSiteAttribute(): ?string
    {
        $minutes = $this->time_on_site;

        if ($minutes === null) {
            return null;
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Check if currently signed in
     */
    public function isSignedIn(): bool
    {
        return $this->status === 'signed_in' &&
               $this->signed_in_at &&
               !$this->signed_out_at;
    }

    /**
     * Check if overdue for sign-out
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' ||
               ($this->signed_in_at &&
                !$this->signed_out_at &&
                $this->signed_in_at->addHours(12)->isPast());
    }

    /**
     * Check if all safety requirements completed
     */
    public function hasSafetyCompliance(): bool
    {
        return $this->ppe_acknowledged &&
               $this->emergency_procedures_acknowledged &&
               $this->health_declaration;
    }

    /**
     * Get person's name from polymorphic relationship
     */
    public function getPersonNameAttribute(): ?string
    {
        if (!$this->signable) {
            return null;
        }

        return $this->signable->full_name ?? $this->signable->name ?? null;
    }

    /**
     * Scope to filter currently signed in
     */
    public function scopeSignedIn(Builder $query): Builder
    {
        return $query->where('status', 'signed_in')
            ->whereNull('signed_out_at');
    }

    /**
     * Scope to filter signed out
     */
    public function scopeSignedOut(Builder $query): Builder
    {
        return $query->where('status', 'signed_out')
            ->whereNotNull('signed_out_at');
    }

    /**
     * Scope to filter overdue sign-outs
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'signed_in')
                  ->whereNull('signed_out_at')
                  ->where('signed_in_at', '<', now()->subHours(12));
            });
    }

    /**
     * Scope to filter by entry method
     */
    public function scopeByEntryMethod(Builder $query, string $method): Builder
    {
        return $query->where('entry_method', $method);
    }

    /**
     * Scope to filter today's logs
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('signed_in_at', today());
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('signed_in_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by signable type
     */
    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('signable_type', $type);
    }

    /**
     * Scope to filter contractors only
     */
    public function scopeContractors(Builder $query): Builder
    {
        return $query->where('signable_type', Contractor::class);
    }

    /**
     * Scope to filter visitors only
     */
    public function scopeVisitors(Builder $query): Builder
    {
        return $query->where('signable_type', Visitor::class);
    }
}
