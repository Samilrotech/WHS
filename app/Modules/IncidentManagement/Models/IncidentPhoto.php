<?php

namespace App\Modules\IncidentManagement\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class IncidentPhoto extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'incident_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'caption',
        'display_order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the incident this photo belongs to
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * Get the full URL for this photo
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human-readable file size
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete photo file from storage when model is deleted
     */
    protected static function booted(): void
    {
        static::deleting(function (IncidentPhoto $photo) {
            if (Storage::exists($photo->file_path)) {
                Storage::delete($photo->file_path);
            }
        });
    }
}
