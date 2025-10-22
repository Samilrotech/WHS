<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'postcode',
        'phone',
        'email',
        'manager_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users for this branch
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all incidents for this branch
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(\App\Modules\IncidentManagement\Models\Incident::class);
    }

    /**
     * Scope to only include active branches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
