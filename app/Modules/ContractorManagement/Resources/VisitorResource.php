<?php

namespace App\Modules\ContractorManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
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

            // Personal details
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,

            // Visit details
            'purpose_of_visit' => $this->purpose_of_visit,
            'host' => $this->when($this->host, [
                'id' => $this->host?->id,
                'name' => $this->host?->name,
            ]),

            // Schedule
            'expected_arrival' => $this->expected_arrival?->toIso8601String(),
            'expected_departure' => $this->expected_departure?->toIso8601String(),
            'actual_arrival' => $this->actual_arrival?->toIso8601String(),
            'actual_departure' => $this->actual_departure?->toIso8601String(),

            // Safety briefing
            'safety_briefing' => [
                'completed' => $this->safety_briefing_completed,
                'completed_at' => $this->briefing_completed_at?->toIso8601String(),
                'briefed_by' => $this->when($this->briefer, [
                    'id' => $this->briefer?->id,
                    'name' => $this->briefer?->name,
                ]),
            ],

            // On-site details
            'badge_number' => $this->badge_number,
            'vehicle_registration' => $this->vehicle_registration,
            'parking_location' => $this->parking_location,

            // Status
            'status' => $this->status,
            'is_on_site' => $this->isOnSite(),
            'is_overdue' => $this->isOverdue(),
            'is_expected_today' => $this->isExpectedToday(),
            'time_on_site' => $this->time_on_site,

            'notes' => $this->notes,

            // Relationships
            'sign_in_logs' => SignInLogResource::collection($this->whenLoaded('signInLogs')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
