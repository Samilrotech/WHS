<?php

namespace App\Modules\ContractorManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignInLogResource extends JsonResource
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

            // Signable (polymorphic)
            'signable_type' => $this->signable_type,
            'signable_id' => $this->signable_id,
            'person_name' => $this->person_name,

            // Sign in/out times
            'signed_in_at' => $this->signed_in_at?->toIso8601String(),
            'signed_out_at' => $this->signed_out_at?->toIso8601String(),
            'time_on_site' => $this->time_on_site,
            'formatted_time_on_site' => $this->formatted_time_on_site,

            // Location and work
            'location' => $this->location,
            'purpose' => $this->purpose,
            'work_description' => $this->work_description,
            'areas_accessed' => $this->areas_accessed,

            // Safety
            'ppe_acknowledged' => $this->ppe_acknowledged,
            'emergency_procedures_acknowledged' => $this->emergency_procedures_acknowledged,
            'ppe_items' => $this->ppe_items,
            'has_safety_compliance' => $this->hasSafetyCompliance(),

            // Health check
            'temperature_check' => $this->temperature_check,
            'health_declaration' => $this->health_declaration,

            // Entry/exit
            'entry_method' => $this->entry_method,
            'exit_method' => $this->exit_method,
            'signature_in' => $this->signature_in,
            'signature_out' => $this->signature_out,

            // Status
            'status' => $this->status,
            'is_signed_in' => $this->isSignedIn(),
            'is_overdue' => $this->isOverdue(),

            'notes' => $this->notes,

            // Relationships
            'signable' => $this->when($this->signable, function () {
                if ($this->signable_type === 'App\\Modules\\ContractorManagement\\Models\\Contractor') {
                    return new ContractorResource($this->signable);
                } elseif ($this->signable_type === 'App\\Modules\\ContractorManagement\\Models\\Visitor') {
                    return new VisitorResource($this->signable);
                }
                return null;
            }),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
