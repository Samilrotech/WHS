<?php

namespace App\Modules\IncidentManagement\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Branch;
use App\Models\User;

class Incident extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'user_id',
        'type',
        'severity',
        'incident_datetime',
        'location_branch',
        'location_specific',
        'gps_latitude',
        'gps_longitude',
        'description',
        'immediate_actions',
        'requires_emergency',
        'notify_authorities',
        'status',
        'assigned_to',
        'root_cause',
        'voice_note_path',
    ];

    protected $casts = [
        'incident_datetime' => 'datetime',
        'requires_emergency' => 'boolean',
        'notify_authorities' => 'boolean',
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
    ];

    /**
     * CRITICAL: Branch isolation global scope
     * Users can only see incidents from their branch (except admins)
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('Admin')) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });
    }

    /**
     * Get the branch this incident belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who reported this incident
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user assigned to investigate this incident
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all witnesses for this incident
     */
    public function witnesses(): HasMany
    {
        return $this->hasMany(Witness::class);
    }

    /**
     * Get all photos for this incident
     */
    public function photos(): HasMany
    {
        return $this->hasMany(IncidentPhoto::class);
    }

    /**
     * Scope: Filter by severity
     */
    public function scopeSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Recent incidents (last 30 days)
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('incident_datetime', '>=', now()->subDays(30));
    }

    /**
     * Check if incident requires emergency response
     */
    public function requiresEmergency(): bool
    {
        return $this->requires_emergency || $this->severity === 'critical';
    }

    /**
     * Get GPS coordinates as array
     */
    public function getGpsCoordinatesAttribute(): ?array
    {
        if ($this->gps_latitude && $this->gps_longitude) {
            return [
                'latitude' => (float) $this->gps_latitude,
                'longitude' => (float) $this->gps_longitude,
            ];
        }

        return null;
    }
}
