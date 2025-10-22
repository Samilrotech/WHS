<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvacuationRecord extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'emergency_alert_id',
        'user_id',
        'evacuation_time',
        'assembly_point',
        'arrived_at_assembly',
        'status',
        'notes',
    ];

    protected $casts = [
        'evacuation_time' => 'datetime',
        'arrived_at_assembly' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('branch', function ($builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function emergencyAlert(): BelongsTo
    {
        return $this->belongsTo(EmergencyAlert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
