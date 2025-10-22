<?php

namespace App\Modules\WarehouseEquipment\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseEquipment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\WarehouseEquipmentFactory::new();
    }

    protected $table = 'warehouse_equipment';

    protected $fillable = [
        'branch_id',
        'equipment_code',
        'equipment_name',
        'equipment_type',
        'manufacturer',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'current_value',
        'qr_code_path',
        'nfc_tag_id',
        'status',
        'location',
        'load_rating',
        'requires_license',
        'license_type',
        'requires_ppe',
        'required_ppe_types',
        'last_inspection_date',
        'next_inspection_due',
        'maintenance_due_date',
        'inspection_frequency_days',
        'maintenance_frequency_days',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_inspection_date' => 'date',
        'next_inspection_due' => 'date',
        'maintenance_due_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'requires_license' => 'boolean',
        'requires_ppe' => 'boolean',
        'required_ppe_types' => 'array',
    ];

    /**
     * Global scope for branch isolation
     */
    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth()->check() && ! auth()->user()->hasRole('admin')) {
                $builder->where('branch_id', auth()->user()->branch_id);
            }
        });
    }

    /**
     * Get the branch that owns the equipment
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all inspections for this equipment
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(EquipmentInspection::class, 'equipment_id');
    }

    /**
     * Get all custody logs for this equipment
     */
    public function custodyLogs(): HasMany
    {
        return $this->hasMany(ToolCustodyLog::class, 'equipment_id');
    }

    /**
     * Get the current custody log if equipment is checked out
     */
    public function currentCustody()
    {
        return $this->hasOne(ToolCustodyLog::class, 'equipment_id')
            ->where('status', 'checked_out')
            ->latest();
    }

    /**
     * Check if equipment is currently checked out
     */
    public function isCheckedOut(): bool
    {
        return $this->custodyLogs()
            ->where('status', 'checked_out')
            ->exists();
    }

    /**
     * Check if inspection is overdue
     */
    public function isInspectionOverdue(): bool
    {
        return $this->next_inspection_due && $this->next_inspection_due->isPast();
    }

    /**
     * Check if maintenance is due
     */
    public function isMaintenanceDue(): bool
    {
        return $this->maintenance_due_date && $this->maintenance_due_date->isPast();
    }

    /**
     * Get days until next inspection
     */
    public function getDaysUntilInspectionAttribute(): ?int
    {
        if (! $this->next_inspection_due) {
            return null;
        }

        return now()->diffInDays($this->next_inspection_due, false);
    }

    /**
     * Get days until maintenance
     */
    public function getDaysUntilMaintenanceAttribute(): ?int
    {
        if (! $this->maintenance_due_date) {
            return null;
        }

        return now()->diffInDays($this->maintenance_due_date, false);
    }

    /**
     * Scope to filter available equipment
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to filter by equipment type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('equipment_type', $type);
    }

    /**
     * Scope to filter equipment requiring inspection
     */
    public function scopeInspectionDue(Builder $query): Builder
    {
        return $query->where('next_inspection_due', '<=', now());
    }

    /**
     * Scope to filter equipment requiring maintenance
     */
    public function scopeMaintenanceDue(Builder $query): Builder
    {
        return $query->where('maintenance_due_date', '<=', now());
    }
}
