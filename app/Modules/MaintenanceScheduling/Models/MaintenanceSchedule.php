<?php

namespace App\Modules\MaintenanceScheduling\Models;

use App\Models\Branch;
use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use Database\Factories\Modules\MaintenanceScheduling\MaintenanceScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceSchedule extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): MaintenanceScheduleFactory
    {
        return MaintenanceScheduleFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'vehicle_id',
        'created_by_user_id',
        'schedule_name',
        'description',
        'schedule_type',
        'recurrence_type',
        'recurrence_interval',
        'odometer_interval',
        'engine_hours_interval',
        'start_date',
        'next_due_date',
        'last_completed_date',
        'completed_count',
        'status',
        'estimated_cost_per_service',
        'actual_total_cost',
        'preferred_vendor',
        'vendor_contact',
        'required_parts',
        'auto_order_parts',
        'reminder_days_before',
        'email_notifications',
        'sms_notifications',
        'priority',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_due_date' => 'date',
        'last_completed_date' => 'date',
        'completed_count' => 'integer',
        'estimated_cost_per_service' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',
        'required_parts' => 'array',
        'auto_order_parts' => 'boolean',
        'reminder_days_before' => 'integer',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'recurrence_interval' => 'integer',
        'odometer_interval' => 'integer',
        'engine_hours_interval' => 'integer',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if schedule is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->next_due_date < now()->toDateString();
    }

    /**
     * Check if schedule is due soon (within reminder window)
     */
    public function isDueSoon(): bool
    {
        return $this->status === 'active' &&
               $this->next_due_date <= now()->addDays($this->reminder_days_before)->toDateString() &&
               $this->next_due_date >= now()->toDateString();
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->next_due_date, false);
    }

    /**
     * Get average cost per service
     */
    public function getAverageCostPerServiceAttribute(): float
    {
        if ($this->completed_count === 0) {
            return 0;
        }

        return round($this->actual_total_cost / $this->completed_count, 2);
    }

    /**
     * Get cost variance (actual vs estimated)
     */
    public function getCostVarianceAttribute(): float
    {
        if (!$this->estimated_cost_per_service || $this->completed_count === 0) {
            return 0;
        }

        $averageCost = $this->average_cost_per_service;
        return round($averageCost - $this->estimated_cost_per_service, 2);
    }

    /**
     * Get cost variance percentage
     */
    public function getCostVariancePercentageAttribute(): float
    {
        if (!$this->estimated_cost_per_service || $this->completed_count === 0) {
            return 0;
        }

        $variance = $this->cost_variance;
        return round(($variance / $this->estimated_cost_per_service) * 100, 2);
    }

    /**
     * Check if schedule is odometer-based
     */
    public function isOdometerBased(): bool
    {
        return $this->recurrence_type === 'odometer_based';
    }

    /**
     * Check if schedule is time-based
     */
    public function isTimeBased(): bool
    {
        return !in_array($this->recurrence_type, ['odometer_based', 'engine_hours']);
    }

    /**
     * Get next recurrence date
     */
    public function getNextRecurrenceDate(): \Carbon\Carbon
    {
        if (!$this->last_completed_date) {
            return \Carbon\Carbon::parse($this->start_date);
        }

        $lastCompleted = \Carbon\Carbon::parse($this->last_completed_date);

        return match ($this->recurrence_type) {
            'daily' => $lastCompleted->addDays($this->recurrence_interval),
            'weekly' => $lastCompleted->addWeeks($this->recurrence_interval),
            'monthly' => $lastCompleted->addMonths($this->recurrence_interval),
            'quarterly' => $lastCompleted->addMonths(3 * $this->recurrence_interval),
            'semi_annual' => $lastCompleted->addMonths(6 * $this->recurrence_interval),
            'annual' => $lastCompleted->addYears($this->recurrence_interval),
            default => $lastCompleted->addMonths($this->recurrence_interval),
        };
    }

    /**
     * Calculate ROI (Return on Investment)
     */
    public function getMaintenanceROI(): array
    {
        $totalCost = $this->actual_total_cost;
        $preventedBreakdowns = $this->maintenanceLogs()
            ->where('maintenance_type', 'scheduled')
            ->count();

        // Estimated cost of breakdowns prevented (conservative estimate)
        $estimatedBreakdownCost = 1000; // Average breakdown cost
        $preventedCost = $preventedBreakdowns * $estimatedBreakdownCost;

        $roi = $totalCost > 0 ? (($preventedCost - $totalCost) / $totalCost) * 100 : 0;

        return [
            'total_cost' => $totalCost,
            'prevented_breakdowns' => $preventedBreakdowns,
            'estimated_prevented_cost' => $preventedCost,
            'roi_percentage' => round($roi, 2),
        ];
    }

    /**
     * Get compliance status
     */
    public function getComplianceStatusAttribute(): string
    {
        if ($this->status !== 'active') {
            return 'inactive';
        }

        if ($this->isOverdue()) {
            return 'overdue';
        }

        if ($this->isDueSoon()) {
            return 'due_soon';
        }

        return 'compliant';
    }

    /**
     * Check if parts need to be ordered
     */
    public function needsPartsOrder(): bool
    {
        return $this->auto_order_parts &&
               $this->required_parts &&
               count($this->required_parts) > 0 &&
               $this->isDueSoon();
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', 'active')
            ->where('next_due_date', '<', now()->toDateString());
    }

    public function scopeDueSoon(Builder $query): void
    {
        $query->where('status', 'active')
            ->where('next_due_date', '<=', now()->addDays(14)->toDateString())
            ->where('next_due_date', '>=', now()->toDateString());
    }

    public function scopeByVehicle(Builder $query, string $vehicleId): void
    {
        $query->where('vehicle_id', $vehicleId);
    }

    public function scopeByType(Builder $query, string $type): void
    {
        $query->where('schedule_type', $type);
    }

    public function scopeByPriority(Builder $query, string $priority): void
    {
        $query->where('priority', $priority);
    }

    public function scopePreventive(Builder $query): void
    {
        $query->where('schedule_type', 'preventive');
    }

    public function scopeCritical(Builder $query): void
    {
        $query->where('priority', 'critical');
    }
}
