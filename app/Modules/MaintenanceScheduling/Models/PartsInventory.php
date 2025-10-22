<?php

namespace App\Modules\MaintenanceScheduling\Models;

use App\Models\Branch;
use Database\Factories\Modules\MaintenanceScheduling\PartsInventoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class PartsInventory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PartsInventoryFactory
    {
        return PartsInventoryFactory::new();
    }

    protected $table = 'parts_inventory';

    protected $fillable = [
        'branch_id',
        'part_number',
        'part_name',
        'description',
        'part_category',
        'quantity_on_hand',
        'reorder_point',
        'reorder_quantity',
        'minimum_stock_level',
        'maximum_stock_level',
        'unit_cost',
        'selling_price',
        'currency',
        'supplier_name',
        'supplier_part_number',
        'supplier_contact',
        'lead_time_days',
        'storage_location',
        'storage_bin',
        'compatible_vehicles',
        'compatible_vehicle_ids',
        'status',
        'last_restocked_date',
        'units_consumed_last_30_days',
        'average_monthly_usage',
        'critical_part',
        'quality_grade',
    ];

    protected $casts = [
        'quantity_on_hand' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'minimum_stock_level' => 'integer',
        'maximum_stock_level' => 'integer',
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'lead_time_days' => 'integer',
        'compatible_vehicles' => 'array',
        'compatible_vehicle_ids' => 'array',
        'last_restocked_date' => 'date',
        'units_consumed_last_30_days' => 'integer',
        'average_monthly_usage' => 'decimal:2',
        'critical_part' => 'boolean',
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

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Check if part is low stock
     */
    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_point;
    }

    /**
     * Check if part is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    /**
     * Check if part is overstocked
     */
    public function isOverstocked(): bool
    {
        return $this->maximum_stock_level && $this->quantity_on_hand > $this->maximum_stock_level;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }

        if ($this->isLowStock()) {
            return 'low_stock';
        }

        if ($this->isOverstocked()) {
            return 'overstocked';
        }

        return 'normal';
    }

    /**
     * Get days until stockout (based on average usage)
     */
    public function getDaysUntilStockoutAttribute(): ?int
    {
        if (!$this->average_monthly_usage || $this->average_monthly_usage == 0) {
            return null;
        }

        $dailyUsage = $this->average_monthly_usage / 30;
        if ($dailyUsage == 0) {
            return null;
        }

        return (int) ceil($this->quantity_on_hand / $dailyUsage);
    }

    /**
     * Get restock urgency level
     */
    public function getRestockUrgencyAttribute(): string
    {
        $daysUntilStockout = $this->days_until_stockout;

        if ($this->isOutOfStock()) {
            return 'critical';
        }

        if ($this->isLowStock()) {
            if ($daysUntilStockout !== null && $daysUntilStockout <= 7) {
                return 'high';
            }
            return 'medium';
        }

        return 'low';
    }

    /**
     * Calculate inventory value
     */
    public function getInventoryValueAttribute(): float
    {
        return round($this->quantity_on_hand * $this->unit_cost, 2);
    }

    /**
     * Calculate profit margin per unit
     */
    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->selling_price || !$this->unit_cost) {
            return null;
        }

        return round($this->selling_price - $this->unit_cost, 2);
    }

    /**
     * Calculate profit margin percentage
     */
    public function getProfitMarginPercentageAttribute(): ?float
    {
        if (!$this->unit_cost || $this->unit_cost == 0) {
            return null;
        }

        $margin = $this->profit_margin;
        if ($margin === null) {
            return null;
        }

        return round(($margin / $this->unit_cost) * 100, 2);
    }

    /**
     * Get estimated restock date (based on lead time)
     */
    public function getEstimatedRestockDateAttribute(): ?\Carbon\Carbon
    {
        if (!$this->lead_time_days) {
            return null;
        }

        return now()->addDays($this->lead_time_days);
    }

    /**
     * Check if part needs reordering
     */
    public function needsReorder(): bool
    {
        return $this->isLowStock() && $this->status === 'active';
    }

    /**
     * Get recommended order quantity
     */
    public function getRecommendedOrderQuantityAttribute(): int
    {
        if (!$this->reorder_quantity) {
            // If no reorder quantity set, calculate based on usage
            if ($this->average_monthly_usage) {
                // Order 2 months worth
                return (int) ceil($this->average_monthly_usage * 2);
            }

            // Default to minimum stock level
            return $this->minimum_stock_level ?: 10;
        }

        return $this->reorder_quantity;
    }

    /**
     * Get turnover rate (times per year)
     */
    public function getTurnoverRateAttribute(): ?float
    {
        if (!$this->average_monthly_usage || $this->quantity_on_hand == 0) {
            return null;
        }

        $annualUsage = $this->average_monthly_usage * 12;
        return round($annualUsage / $this->quantity_on_hand, 2);
    }

    /**
     * Check if part is fast-moving
     */
    public function isFastMoving(): bool
    {
        $turnoverRate = $this->turnover_rate;
        return $turnoverRate !== null && $turnoverRate > 6; // More than 6 times per year
    }

    /**
     * Check if part is slow-moving
     */
    public function isSlowMoving(): bool
    {
        $turnoverRate = $this->turnover_rate;
        return $turnoverRate !== null && $turnoverRate < 2; // Less than 2 times per year
    }

    /**
     * Consume stock (use parts)
     */
    public function consumeStock(int $quantity): bool
    {
        if ($this->quantity_on_hand < $quantity) {
            return false;
        }

        $this->quantity_on_hand -= $quantity;
        $this->units_consumed_last_30_days += $quantity;

        if ($this->quantity_on_hand <= 0) {
            $this->status = 'out_of_stock';
        }

        return $this->save();
    }

    /**
     * Restock parts
     */
    public function restock(int $quantity): bool
    {
        $this->quantity_on_hand += $quantity;
        $this->last_restocked_date = now();

        if ($this->status === 'out_of_stock') {
            $this->status = 'active';
        }

        return $this->save();
    }

    /**
     * Update average monthly usage
     */
    public function updateAverageUsage(): void
    {
        // Simple moving average - in production, use more sophisticated calculation
        $this->average_monthly_usage = $this->units_consumed_last_30_days;
        $this->save();
    }

    // ============================================================================
    // QUERY SCOPES
    // ============================================================================

    public function scopeLowStock(Builder $query): void
    {
        $query->whereRaw('quantity_on_hand <= reorder_point');
    }

    public function scopeOutOfStock(Builder $query): void
    {
        $query->where('quantity_on_hand', '<=', 0);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeCriticalParts(Builder $query): void
    {
        $query->where('critical_part', true);
    }

    public function scopeByCategory(Builder $query, string $category): void
    {
        $query->where('part_category', $category);
    }

    public function scopeFastMoving(Builder $query): void
    {
        $query->whereRaw('(average_monthly_usage * 12 / NULLIF(quantity_on_hand, 0)) > 6');
    }

    public function scopeSlowMoving(Builder $query): void
    {
        $query->whereRaw('(average_monthly_usage * 12 / NULLIF(quantity_on_hand, 0)) < 2');
    }

    public function scopeNeedsReorder(Builder $query): void
    {
        $query->lowStock()
            ->where('status', 'active');
    }
}
