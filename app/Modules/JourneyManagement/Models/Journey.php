<?php

namespace App\Modules\JourneyManagement\Models;

use App\Models\Branch;
use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journey extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Modules\JourneyManagement\JourneyFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'user_id',
        'vehicle_id',
        'title',
        'purpose',
        'destination',
        'destination_address',
        'destination_latitude',
        'destination_longitude',
        'planned_route',
        'estimated_distance_km',
        'estimated_duration_minutes',
        'planned_start_time',
        'planned_end_time',
        'actual_start_time',
        'actual_end_time',
        'checkin_interval_minutes',
        'last_checkin_time',
        'next_checkin_due',
        'checkin_overdue',
        'emergency_contact_name',
        'emergency_contact_phone',
        'hazards_identified',
        'control_measures',
        'status',
        'notes',
        'completion_notes',
    ];

    protected $casts = [
        'destination_latitude' => 'decimal:7',
        'destination_longitude' => 'decimal:7',
        'planned_route' => 'array',
        'estimated_distance_km' => 'decimal:2',
        'estimated_duration_minutes' => 'integer',
        'planned_start_time' => 'datetime',
        'planned_end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'checkin_interval_minutes' => 'integer',
        'last_checkin_time' => 'datetime',
        'next_checkin_due' => 'datetime',
        'checkin_overdue' => 'boolean',
    ];

    /**
     * Global scope for branch isolation
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });
    }

    /**
     * Get the branch that owns the journey
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user (lone worker) on this journey
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle assigned to this journey
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get all checkpoints for this journey
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(JourneyCheckpoint::class);
    }

    /**
     * Check if journey is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if check-in is overdue
     */
    public function isCheckinOverdue(): bool
    {
        if (!$this->isActive() || !$this->next_checkin_due) {
            return false;
        }

        return $this->next_checkin_due->isPast();
    }

    /**
     * Check if journey is in emergency status
     */
    public function isEmergency(): bool
    {
        return $this->status === 'emergency';
    }

    /**
     * Get the latest checkpoint
     */
    public function latestCheckpoint()
    {
        return $this->hasOne(JourneyCheckpoint::class)->latestOfMany('checkin_time');
    }

    /**
     * Get total journey duration in minutes
     */
    public function getActualDurationAttribute(): ?int
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return null;
        }

        return $this->actual_start_time->diffInMinutes($this->actual_end_time);
    }

    /**
     * Scope to only include active journeys
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to only include overdue journeys
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'active')
                    ->where('next_checkin_due', '<', now());
            });
    }

    /**
     * Scope to only include emergency journeys
     */
    public function scopeEmergency($query)
    {
        return $query->where('status', 'emergency');
    }
}
