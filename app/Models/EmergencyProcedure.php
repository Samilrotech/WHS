<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyProcedure extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'title',
        'incident_type',
        'description',
        'steps',
        'equipment_needed',
        'key_contacts',
        'file_path',
        'is_active',
    ];

    protected $casts = [
        'steps' => 'array',
        'is_active' => 'boolean',
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
}
