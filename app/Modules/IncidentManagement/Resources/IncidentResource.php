<?php

namespace App\Modules\IncidentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'severity' => $this->severity,
            'status' => $this->status,
            'incident_datetime' => $this->incident_datetime->toIso8601String(),
            'location' => [
                'branch' => $this->location_branch,
                'specific' => $this->location_specific,
                'gps_coordinates' => $this->gps_coordinates,
            ],
            'description' => $this->description,
            'immediate_actions' => $this->immediate_actions,
            'requires_emergency' => $this->requires_emergency,
            'notify_authorities' => $this->notify_authorities,
            'root_cause' => $this->root_cause,
            'voice_note_url' => $this->voice_note_path ? url('storage/' . $this->voice_note_path) : null,

            // Relationships
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
                'code' => $this->branch->code,
            ],
            'assigned_to' => $this->whenLoaded('assignedTo', function () {
                return $this->assignedTo ? [
                    'id' => $this->assignedTo->id,
                    'name' => $this->assignedTo->name,
                ] : null;
            }),
            'witnesses' => $this->whenLoaded('witnesses', function () {
                return $this->witnesses->map(fn($witness) => [
                    'id' => $witness->id,
                    'name' => $witness->name,
                    'contact_number' => $witness->contact_number,
                    'email' => $witness->email,
                    'statement' => $witness->statement,
                ]);
            }),
            'photos' => $this->whenLoaded('photos', function () {
                return $this->photos->map(fn($photo) => [
                    'id' => $photo->id,
                    'url' => $photo->url,
                    'file_name' => $photo->file_name,
                    'file_size' => $photo->human_file_size,
                    'caption' => $photo->caption,
                ]);
            }),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
