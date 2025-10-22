<?php

namespace App\Modules\DocumentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
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

            // Basic information
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,

            // Hierarchy
            'parent_id' => $this->parent_id,
            'display_order' => $this->display_order,
            'is_top_level' => $this->isTopLevel(),
            'breadcrumb' => $this->breadcrumb(),

            // Settings
            'requires_approval' => $this->requires_approval,
            'retention_days' => $this->retention_days,
            'has_retention_policy' => $this->hasRetentionPolicy(),
            'status' => $this->status,

            // Counts
            'documents_count' => $this->when($this->documents_count !== null, $this->documents_count),
            'children_count' => $this->when($this->children_count !== null, $this->children_count),

            // Relationships
            'parent' => new DocumentCategoryResource($this->whenLoaded('parent')),
            'children' => DocumentCategoryResource::collection($this->whenLoaded('children')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
