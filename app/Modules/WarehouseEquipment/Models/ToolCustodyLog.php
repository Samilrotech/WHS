<?php

namespace App\Modules\WarehouseEquipment\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolCustodyLog extends Model
{
    use HasFactory, HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\ToolCustodyLogFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'equipment_id',
        'custodian_user_id',
        'checked_out_at',
        'expected_return_date',
        'purpose',
        'checkout_notes',
        'condition_on_checkout',
        'checked_in_at',
        'checkin_notes',
        'condition_on_checkin',
        'damage_reported',
        'damage_description',
        'status',
        'is_overdue',
        'days_overdue',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'expected_return_date' => 'date',
        'checked_in_at' => 'datetime',
        'approved_at' => 'datetime',
        'damage_reported' => 'boolean',
        'is_overdue' => 'boolean',
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
     * Get the branch that owns the custody log
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the equipment being held
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(WarehouseEquipment::class, 'equipment_id');
    }

    /**
     * Get the custodian
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_user_id');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Check and update overdue status
     */
    public function updateOverdueStatus(): void
    {
        if ($this->status === 'checked_out' && $this->expected_return_date) {
            $isOverdue = $this->expected_return_date->isPast();
            $daysOverdue = $isOverdue ? now()->diffInDays($this->expected_return_date) : 0;

            $this->update([
                'is_overdue' => $isOverdue,
                'days_overdue' => $daysOverdue,
                'status' => $isOverdue ? 'overdue' : 'checked_out',
            ]);
        }
    }

    /**
     * Check in the equipment
     */
    public function checkIn(string $condition, ?string $notes = null, bool $damaged = false, ?string $damageDescription = null): void
    {
        $this->update([
            'checked_in_at' => now(),
            'condition_on_checkin' => $condition,
            'checkin_notes' => $notes,
            'damage_reported' => $damaged,
            'damage_description' => $damageDescription,
            'status' => 'returned',
            'is_overdue' => false,
        ]);

        // Update equipment status back to available
        $this->equipment->update(['status' => 'available']);
    }

    /**
     * Scope to filter checked out items
     */
    public function scopeCheckedOut(Builder $query): Builder
    {
        return $query->where('status', 'checked_out');
    }

    /**
     * Scope to filter overdue items
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'checked_out')
                    ->where('expected_return_date', '<', now());
            });
    }

    /**
     * Scope to filter by custodian
     */
    public function scopeByCustodian(Builder $query, string $userId): Builder
    {
        return $query->where('custodian_user_id', $userId);
    }

    /**
     * Get checkout duration in days
     */
    public function getCheckoutDurationAttribute(): ?int
    {
        if (! $this->checked_in_at) {
            return now()->diffInDays($this->checked_out_at);
        }

        return $this->checked_in_at->diffInDays($this->checked_out_at);
    }
}
