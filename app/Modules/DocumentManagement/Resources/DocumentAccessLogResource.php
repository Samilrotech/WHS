<?php

namespace App\Modules\DocumentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentAccessLogResource extends JsonResource
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
            'user_id' => $this->user_id,

            // Action information
            'action' => $this->action,
            'action_label' => $this->action_label,
            'action_icon' => $this->action_icon,
            'action_color' => $this->action_color,
            'is_critical_action' => $this->isCriticalAction(),

            // Request information
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'browser' => $this->browser,
            'device_type' => $this->device_type,
            'metadata' => $this->metadata,

            // Relationships
            'document' => $this->when($this->document, [
                'id' => $this->document?->id,
                'title' => $this->document?->title,
                'document_number' => $this->document?->document_number,
            ]),
            'user' => $this->when($this->user, [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
