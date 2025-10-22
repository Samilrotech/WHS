<?php

namespace App\Modules\RiskAssessment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ControlMeasure extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'hazard_id',
        'hierarchy',
        'description',
        'responsible_person',
        'implementation_date',
        'status',
    ];

    protected $casts = [
        'implementation_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function hazard(): BelongsTo
    {
        return $this->belongsTo(Hazard::class);
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person');
    }

    /**
     * Scopes
     */
    public function scopeByHierarchy(Builder $query, string $hierarchy): Builder
    {
        return $query->where('hierarchy', $hierarchy);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeImplemented(Builder $query): Builder
    {
        return $query->where('status', 'implemented');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }
}
