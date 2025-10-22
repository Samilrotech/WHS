<?php

namespace App\Models\Modules\DocumentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'user_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Document relationship
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get action label for display
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'view' => 'Viewed',
            'download' => 'Downloaded',
            'print' => 'Printed',
            'share' => 'Shared',
            'edit' => 'Edited',
            'delete' => 'Deleted',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get action icon for display
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'view' => 'bx-show',
            'download' => 'bx-download',
            'print' => 'bx-printer',
            'share' => 'bx-share-alt',
            'edit' => 'bx-edit',
            'delete' => 'bx-trash',
            default => 'bx-file',
        };
    }

    /**
     * Get action color for display
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'view' => 'info',
            'download' => 'primary',
            'print' => 'secondary',
            'share' => 'warning',
            'edit' => 'success',
            'delete' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Check if action is critical (edit/delete)
     */
    public function isCriticalAction(): bool
    {
        return in_array($this->action, ['edit', 'delete']);
    }

    /**
     * Get browser name from user agent
     */
    public function getBrowserAttribute(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        $ua = $this->user_agent;

        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Edge')) return 'Edge';
        if (str_contains($ua, 'Opera')) return 'Opera';

        return 'Unknown';
    }

    /**
     * Get device type from user agent
     */
    public function getDeviceTypeAttribute(): string
    {
        if (!$this->user_agent) {
            return 'Unknown';
        }

        $ua = strtolower($this->user_agent);

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            return 'Mobile';
        }

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'Tablet';
        }

        return 'Desktop';
    }

    /**
     * Scope to filter by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get critical actions only
     */
    public function scopeCriticalActions($query)
    {
        return $query->whereIn('action', ['edit', 'delete']);
    }
}
