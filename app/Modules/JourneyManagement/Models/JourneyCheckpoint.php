<?php

namespace App\Modules\JourneyManagement\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyCheckpoint extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'journey_id',
        'checkin_time',
        'latitude',
        'longitude',
        'location_name',
        'type',
        'status',
        'notes',
        'issues_reported',
        'photo_paths',
    ];

    protected $casts = [
        'checkin_time' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'photo_paths' => 'array',
    ];

    /**
     * Get the journey that owns the checkpoint
     */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    /**
     * Check if checkpoint indicates emergency
     */
    public function isEmergency(): bool
    {
        return $this->status === 'emergency' || $this->type === 'emergency';
    }

    /**
     * Check if checkpoint is overdue (missed check-in)
     */
    public function isMissed(): bool
    {
        return $this->type === 'missed';
    }
}
