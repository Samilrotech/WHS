<?php

namespace App\Modules\VehicleManagement\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\InspectionManagement\Models\Inspection;

class VehicleAssignment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'branch_id',
        'assigned_date',
        'returned_date',
        'odometer_start',
        'odometer_end',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'returned_date' => 'date',
        'odometer_start' => 'integer',
        'odometer_end' => 'integer',
    ];

    /**
     * Get the vehicle that owns the assignment
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who was assigned the vehicle
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch associated with this assignment
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Inspections submitted during this assignment
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Check if assignment is currently active
     */
    public function isActive(): bool
    {
        return is_null($this->returned_date);
    }

    /**
     * Calculate kilometers driven during assignment
     */
    public function getKilometersDrivenAttribute(): ?int
    {
        if ($this->odometer_start && $this->odometer_end) {
            return $this->odometer_end - $this->odometer_start;
        }

        return null;
    }

    /**
     * Calculate assignment duration in days
     */
    public function getAssignmentDurationAttribute(): int
    {
        $endDate = $this->returned_date ?? now();
        return $this->assigned_date->diffInDays($endDate);
    }
}
