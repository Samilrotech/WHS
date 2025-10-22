<?php

namespace App\Modules\ContractorManagement\Models;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'purpose_of_visit',
        'host_user_id',
        'expected_arrival',
        'expected_departure',
        'actual_arrival',
        'actual_departure',
        'safety_briefing_completed',
        'briefed_by',
        'briefing_completed_at',
        'badge_number',
        'vehicle_registration',
        'parking_location',
        'status',
        'notes',
    ];

    protected $casts = [
        'expected_arrival' => 'datetime',
        'expected_departure' => 'datetime',
        'actual_arrival' => 'datetime',
        'actual_departure' => 'datetime',
        'safety_briefing_completed' => 'boolean',
        'briefing_completed_at' => 'datetime',
    ];

    /**
     * Boot the model and apply global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('visitors.branch_id', auth()->user()->branch_id);
            }
        });

        // Auto-update status based on actual times
        static::saving(function ($visitor) {
            if ($visitor->actual_arrival && !$visitor->actual_departure) {
                $visitor->status = 'on_site';
            } elseif ($visitor->actual_departure) {
                $visitor->status = 'departed';
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
     * Get the host user
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Get the user who conducted safety briefing
     */
    public function briefer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'briefed_by');
    }

    /**
     * Get all sign-in logs (polymorphic)
     */
    public function signInLogs(): MorphMany
    {
        return $this->morphMany(SignInLog::class, 'signable');
    }

    /**
     * Get visitor's full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if visitor is currently on site
     */
    public function isOnSite(): bool
    {
        return $this->status === 'on_site' &&
               $this->actual_arrival &&
               !$this->actual_departure;
    }

    /**
     * Check if visitor is overdue (expected departure passed)
     */
    public function isOverdue(): bool
    {
        return $this->status === 'on_site' &&
               $this->expected_departure &&
               $this->expected_departure->isPast();
    }

    /**
     * Check if visitor has completed safety briefing
     */
    public function hasSafetyBriefing(): bool
    {
        return $this->safety_briefing_completed &&
               $this->briefing_completed_at !== null;
    }

    /**
     * Check if visitor is expected today
     */
    public function isExpectedToday(): bool
    {
        return $this->status === 'expected' &&
               $this->expected_arrival &&
               $this->expected_arrival->isToday();
    }

    /**
     * Get duration on site in minutes
     */
    public function getTimeOnSiteAttribute(): ?int
    {
        if (!$this->actual_arrival) {
            return null;
        }

        $endTime = $this->actual_departure ?? now();
        return $this->actual_arrival->diffInMinutes($endTime);
    }

    /**
     * Check if visitor is currently signed in
     */
    public function isSignedIn(): bool
    {
        return $this->signInLogs()
            ->where('status', 'signed_in')
            ->whereNull('signed_out_at')
            ->exists();
    }

    /**
     * Scope to filter visitors on site
     */
    public function scopeOnSite(Builder $query): Builder
    {
        return $query->where('status', 'on_site');
    }

    /**
     * Scope to filter expected visitors
     */
    public function scopeExpected(Builder $query): Builder
    {
        return $query->where('status', 'expected');
    }

    /**
     * Scope to filter overdue visitors
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'on_site')
            ->where('expected_departure', '<', now());
    }

    /**
     * Scope to filter visitors expected today
     */
    public function scopeExpectedToday(Builder $query): Builder
    {
        return $query->where('status', 'expected')
            ->whereDate('expected_arrival', today());
    }

    /**
     * Scope to filter visitors without safety briefing
     */
    public function scopeNoBriefing(Builder $query): Builder
    {
        return $query->where('safety_briefing_completed', false);
    }
}
