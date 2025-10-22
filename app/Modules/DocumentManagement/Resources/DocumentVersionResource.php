<?php

namespace App\Modules\DocumentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentVersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'created_by' => $this->created_by,

            // Version information
            'version_number' => $this->version_number,
            'is_latest' => $this->isLatest(),

            // File information
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,
            'file_hash' => $this->file_hash,

            // Version notes
            'change_notes' => $this->change_notes,

            // Size comparison
            'size_difference' => $this->getSizeDifference(),
            'formatted_size_difference' => $this->getFormattedSizeDifference(),

            // Relationships
            'document' => $this->when($this->document, [
                'id' => $this->document?->id,
                'title' => $this->document?->title,
                'document_number' => $this->document?->document_number,
            ]),
            'creator' => $this->when($this->creator, [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ]),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
