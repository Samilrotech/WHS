<?php

namespace App\Modules\MaintenanceScheduling\Models;

use App\Models\Branch;
use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\InspectionManagement\Models\Inspection;
use Database\Factories\Modules\MaintenanceScheduling\MaintenanceLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceLog extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): MaintenanceLogFactory
    {
        return MaintenanceLogFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'vehicle_id',
        'maintenance_schedule_id',
        'inspection_id',
        'performed_by_user_id',
        'approved_by_user_id',
        'work_order_number',
        'description',
        'maintenance_type',
        'work_performed',
        'odometer_reading',
        'engine_hours',
        'service_date',
        'start_time',
        'end_time',
        'labor_hours',
        'status',
        'vendor_name',
        'vendor_invoice_number',
        'invoice_date',
        'parts_cost',
        'labor_cost',
        'vendor_cost',
        'total_cost',
        'parts_used',
        'quality_rating',
        'warranty_applicable',
        'warranty_expiry_date',
        'safety_critical',
        'photo_paths',
        'notes',
        'recommendations',
        'vehicle_out_of_service_at',
        'vehicle_back_in_service_at',
        'downtime_hours',
    ];

    protected $casts = [
        'service_date' => 'date',
        'invoice_date' => 'date',
        'warranty_expiry_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'vehicle_out_of_service_at' => 'datetime',
        'vehicle_back_in_service_at' => 'datetime',
        'labor_hours' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'vendor_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'parts_used' => 'array',
        'photo_paths' => 'array',
        'warranty_applicable' => 'boolean',
        'safety_critical' => 'boolean',
        'odometer_reading' => 'integer',
        'engine_hours' => 'integer',
        'downtime_hours' => 'integer',
    ];

    /**
     * Boot the model and add global scopes
     */
    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->isAdmin()) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });
    }

    // ============================================================================
    // RELATIONSHIPS
    // ============================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function maintenanceSchedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if maintenance is from scheduled maintenance
     */
    public function isScheduled(): bool
    {
        return $this->maintenance_type === 'scheduled' && $this->maintenance_schedule_id !== null;
    }

    /**
     * Check if maintenance is from inspection findings
     */
    public function isInspectionFollowup(): bool
    {
        return $this->maintenance_type === 'inspection_followup' && $this->inspection_id !== null;
    }

    /**
     * Check if maintenance is safety critical
     */
    public function isSafetyCritical(): bool
    {
        return $this->safety_critical === true;
    }

    /**
     * Check if work is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'verified']);
    }

    /**
     * Check if work is awaiting approval
     */
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Calculate downtime hours
     */
    public function calculateDowntime(): int
    {
        if (!$this->vehicle_out_of_service_at || !$this->vehicle_back_in_service_at) {
            return 0;
        }

        return $this->vehicle_out_of_service_at->diffInHours($this->vehicle_back_in_service_at);
    }

    /**
     * Get cost breakdown percentage
     */
    public function getCostBreakdownAttribute(): array
    {
        if ($this->total_cost == 0) {
            return [
                'parts' => 0,
                'labor' => 0,
                'vendor' => 0,
            ];
        }

        return [
            'parts' => round(($this->parts_cost / $this->total_cost) * 100, 2),
            'labor' => round(($this->labor_cost / $this->total_cost) * 100, 2),
            'vendor' => round(($this->vendor_cost / $this->total_cost) * 100, 2),
        ];
    }

    /**
     * Check if warranty is still valid
     */
    public function hasValidWarranty(): bool
    {
        return $this->warranty_applicable &&
               $this->warranty_expiry_date &&
               $this->warranty_expiry_date >= now();
    }

    /**
     * Get days remaining on warranty
     */
    public function getWarrantyDaysRemainingAttribute(): ?int
    {
        if (!$this->hasValidWarranty()) {
            return null;
        }

        return now()->diffInDays($this->warranty_expiry_date);
    }

    /**
     * Check if work is overdue (pending approval for >2 days)
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->created_at->diffInDays(now()) > 2;
    }

    /**
     * Get labor rate
     */
    public function getLaborRateAttribute(): ?float
    {
        if (!$this->labor_hours || $this->labor_hours == 0) {
            return null;
        }

        return round($this->labor_cost / $this->labor_hours, 2);
    }

    /**
     * Check if external vendor was used
     */
    public function usedExternalVendor(): bool
    {
        return !empty($this->vendor_name);
    }

    /**
     * Get total parts count
     */
    public function getTotalPartsCountAttribute(): int
    {
        if (!$this->parts_used) {
            return 0;
        }

        return array_sum(array_column($this->parts_used, 'quantity'));
    }

    /**
     * Get efficiency rating (labor hours vs estimated)
     */
    public function getEfficiencyRating(): string
    {
        if (!$this->labor_hours || !$this->maintenanceSchedule) {
            return 'N/A';
        }

        // Assuming 2 hours is average for most maintenance
        $estimatedHours = 2;

        if ($this->labor_hours <= $estimatedHours) {
            return 'excellent';
        } elseif ($this->labor_hours <= $estimatedHours * 1.5) {
            return 'good';
        } else {
            return 'needs_improvement';
        }
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeCompleted(Builder $query): void
    {
        $query->whereIn('status', ['completed', 'verified']);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(2));
    }

    public function scopeSafetyCritical(Builder $query): void
    {
        $query->where('safety_critical', true);
    }

    public function scopeByVehicle(Builder $query, string $vehicleId): void
    {
        $query->where('vehicle_id', $vehicleId);
    }

    public function scopeByType(Builder $query, string $type): void
    {
        $query->where('maintenance_type', $type);
    }

    public function scopeScheduled(Builder $query): void
    {
        $query->where('maintenance_type', 'scheduled');
    }

    public function scopeUnscheduled(Builder $query): void
    {
        $query->where('maintenance_type', 'unscheduled');
    }

    public function scopeWithWarranty(Builder $query): void
    {
        $query->where('warranty_applicable', true)
            ->where('warranty_expiry_date', '>=', now());
    }

    public function scopeByDateRange(Builder $query, string $from, string $to): void
    {
        $query->whereBetween('service_date', [$from, $to]);
    }
}
