<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Modules\VehicleManagement\Models\VehicleAssignment;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'password',
        'phone',
        'employee_id',
        'position',
        'is_active',
        'employment_status',
        'employment_start_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'employment_start_date' => 'date',
        ];
    }

    /**
     * Get the branch this user belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all incidents created by this user
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(\App\Modules\IncidentManagement\Models\Incident::class, 'user_id');
    }

    /**
     * Get all incidents assigned to this user
     */
    public function assignedIncidents(): HasMany
    {
        return $this->hasMany(\App\Modules\IncidentManagement\Models\Incident::class, 'assigned_to');
    }

    /**
     * Vehicle assignments for this user
     */
    public function vehicleAssignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    /**
     * Current active vehicle assignment
     */
    public function currentVehicleAssignment(): HasOne
    {
        return $this->hasOne(VehicleAssignment::class)
            ->whereNull('returned_date')
            ->latestOfMany('assigned_date');
    }

    /**
     * Scope to only include active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole([
            'Admin',
            'Super Admin',
            'admin',        // backward compatibility
            'super-admin',
        ]);
    }
}
