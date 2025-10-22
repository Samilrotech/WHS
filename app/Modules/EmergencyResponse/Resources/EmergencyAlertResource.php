<?php

namespace App\Modules\EmergencyResponse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyAlertResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_description' => $this->location_description,
            'description' => $this->description,
            'triggered_at' => $this->triggered_at,
            'responded_at' => $this->responded_at,
            'resolved_at' => $this->resolved_at,
            'response_notes' => $this->response_notes,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ],
            'responder' => $this->responder ? [
                'id' => $this->responder->id,
                'name' => $this->responder->name,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
