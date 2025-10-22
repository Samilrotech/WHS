<?php

namespace App\Modules\DocumentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'branch_id' => $this->branch_id,
            'category_id' => $this->category_id,
            'uploaded_by' => $this->uploaded_by,

            // Document details
            'title' => $this->title,
            'document_number' => $this->document_number,
            'description' => $this->description,

            // File information
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,
            'mime_type' => $this->mime_type,
            'file_hash' => $this->file_hash,

            // Versioning
            'current_version' => $this->current_version,

            // Metadata
            'tags' => $this->tags,
            'metadata' => $this->metadata,

            // Review and approval
            'requires_review' => $this->requires_review,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'review_notes' => $this->review_notes,
            'review_status' => $this->review_status,
            'is_approved' => $this->isApproved(),
            'is_pending_review' => $this->isPendingReview(),
            'is_rejected' => $this->isRejected(),

            // Expiry
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),

            // Access control
            'visibility' => $this->visibility,
            'restricted_to' => $this->restricted_to,
            'user_has_access' => $this->when(
                $request->user(),
                fn() => $this->userHasAccess($request->user())
            ),

            'status' => $this->status,

            // Relationships
            'category' => new DocumentCategoryResource($this->whenLoaded('category')),
            'uploader' => $this->when($this->uploader, [
                'id' => $this->uploader?->id,
                'name' => $this->uploader?->name,
                'email' => $this->uploader?->email,
            ]),
            'reviewer' => $this->when($this->reviewer, [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name,
                'email' => $this->reviewer?->email,
            ]),
            'versions' => DocumentVersionResource::collection($this->whenLoaded('versions')),
            'latest_version' => new DocumentVersionResource($this->whenLoaded('latestVersion')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
