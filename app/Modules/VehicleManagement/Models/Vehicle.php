<?php

namespace App\Modules\VehicleManagement\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Modules\VehicleManagement\VehicleFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'registration_number',
        'make',
        'model',
        'year',
        'vin_number',
        'color',
        'fuel_type',
        'odometer_reading',
        'purchase_date',
        'purchase_price',
        'current_value',
        'depreciation_method',
        'depreciation_rate',
        'insurance_company',
        'insurance_policy_number',
        'insurance_expiry_date',
        'insurance_premium',
        'rego_expiry_date',
        'inspection_due_date',
        'qr_code_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'odometer_reading' => 'integer',
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'insurance_premium' => 'decimal:2',
        'insurance_expiry_date' => 'date',
        'rego_expiry_date' => 'date',
        'inspection_due_date' => 'date',
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
     * Get the branch that owns the vehicle
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get service records for the vehicle
     */
    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    /**
     * Get assignments for the vehicle
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    /**
     * Get the current assignment (not returned)
     */
    public function currentAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)
            ->whereNull('returned_date')
            ->latest('assigned_date');
    }

    /**
     * Check if vehicle is currently assigned
     */
    public function isAssigned(): bool
    {
        return $this->currentAssignment()->exists();
    }

    /**
     * Get total service costs
     */
    public function getTotalServiceCostAttribute(): float
    {
        return $this->serviceRecords()->sum('cost');
    }

    /**
     * Check if registration is expired or expiring soon (within 30 days)
     */
    public function isRegistrationExpiring(): bool
    {
        if (!$this->rego_expiry_date) {
            return false;
        }

        return $this->rego_expiry_date->lte(now()->addDays(30));
    }

    /**
     * Check if insurance is expired or expiring soon (within 30 days)
     */
    public function isInsuranceExpiring(): bool
    {
        if (!$this->insurance_expiry_date) {
            return false;
        }

        return $this->insurance_expiry_date->lte(now()->addDays(30));
    }

    /**
     * Check if inspection is due or overdue (within 7 days)
     */
    public function isInspectionDue(): bool
    {
        if (!$this->inspection_due_date) {
            return false;
        }

        return $this->inspection_due_date->lte(now()->addDays(7));
    }

    /**
     * Get inspections for this vehicle
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(\App\Modules\InspectionManagement\Models\Inspection::class);
    }

    /**
     * Get the latest inspection
     */
    public function latestInspection()
    {
        return $this->hasOne(\App\Modules\InspectionManagement\Models\Inspection::class)
            ->latest('inspection_date');
    }
}
