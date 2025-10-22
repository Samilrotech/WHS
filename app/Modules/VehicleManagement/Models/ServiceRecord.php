<?php

namespace App\Modules\VehicleManagement\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRecord extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'service_date',
        'service_type',
        'service_provider',
        'cost',
        'odometer_at_service',
        'description',
        'parts_replaced',
        'next_service_due',
        'next_service_odometer',
    ];

    protected $casts = [
        'service_date' => 'date',
        'cost' => 'decimal:2',
        'odometer_at_service' => 'integer',
        'next_service_due' => 'date',
        'next_service_odometer' => 'integer',
    ];

    /**
     * Get the vehicle that owns the service record
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Check if next service is due soon (within 7 days or 1000 km)
     */
    public function isNextServiceDue(): bool
    {
        $vehicle = $this->vehicle;

        if ($this->next_service_due && $this->next_service_due->lte(now()->addDays(7))) {
            return true;
        }

        if ($this->next_service_odometer && $vehicle) {
            $odometerDiff = $this->next_service_odometer - $vehicle->odometer_reading;
            if ($odometerDiff <= 1000) {
                return true;
            }
        }

        return false;
    }
}
