<?php

namespace App\Models\Modules\DocumentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'created_by',
        'version_number',
        'file_path',
        'file_name',
        'file_size',
        'file_hash',
        'change_notes',
    ];

    protected $casts = [
        'created_by' => 'string',
        'version_number' => 'integer',
        'file_size' => 'integer',
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
     * Creator relationship
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if this is the latest version
     */
    public function isLatest(): bool
    {
        return $this->version_number === $this->document->current_version;
    }

    /**
     * Get previous version
     */
    public function previousVersion(): ?DocumentVersion
    {
        return DocumentVersion::where('document_id', $this->document_id)
            ->where('version_number', $this->version_number - 1)
            ->first();
    }

    /**
     * Get next version
     */
    public function nextVersion(): ?DocumentVersion
    {
        return DocumentVersion::where('document_id', $this->document_id)
            ->where('version_number', $this->version_number + 1)
            ->first();
    }

    /**
     * Verify file integrity using hash
     */
    public function verifyIntegrity(): bool
    {
        if (!file_exists(storage_path('app/' . $this->file_path))) {
            return false;
        }

        $currentHash = hash_file('sha256', storage_path('app/' . $this->file_path));
        return $currentHash === $this->file_hash;
    }

    /**
     * Compare file sizes with another version
     */
    public function compareSize(DocumentVersion $other): int
    {
        return $this->file_size - $other->file_size;
    }

    /**
     * Get size difference with previous version
     */
    public function getSizeDifference(): ?int
    {
        $previous = $this->previousVersion();

        if (!$previous) {
            return null;
        }

        return $this->compareSize($previous);
    }

    /**
     * Get formatted size difference
     */
    public function getFormattedSizeDifference(): ?string
    {
        $diff = $this->getSizeDifference();

        if ($diff === null) {
            return null;
        }

        $prefix = $diff > 0 ? '+' : '';
        $bytes = abs($diff);
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return $prefix . round($bytes, 2) . ' ' . $units[$i];
    }
}
