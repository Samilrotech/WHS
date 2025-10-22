<?php

namespace App\Modules\IncidentManagement\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Witness extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'incident_id',
        'name',
        'contact_number',
        'email',
        'statement',
        'statement_taken_at',
        'taken_by_user_id',
    ];

    protected $casts = [
        'statement_taken_at' => 'datetime',
    ];

    /**
     * Get the incident this witness belongs to
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * Get the user who took this witness statement
     */
    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by_user_id');
    }
}
