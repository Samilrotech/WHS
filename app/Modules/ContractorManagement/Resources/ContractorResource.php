<?php

namespace App\Modules\ContractorManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractorResource extends JsonResource
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
            'contractor_company_id' => $this->contractor_company_id,

            // Personal details
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),

            // Emergency contact
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
            ],

            // Driver license
            'driver_license' => [
                'number' => $this->driver_license_number,
                'expiry_date' => $this->driver_license_expiry?->format('Y-m-d'),
            ],

            // Induction status
            'induction' => [
                'completed' => $this->induction_completed,
                'completion_date' => $this->induction_completion_date?->format('Y-m-d'),
                'expiry_date' => $this->induction_expiry_date?->format('Y-m-d'),
                'is_expiring_soon' => $this->isInductionExpiringSoon(),
                'has_valid_induction' => $this->hasValidInduction(),
                'inducted_by' => $this->when($this->inductor, [
                    'id' => $this->inductor?->id,
                    'name' => $this->inductor?->name,
                ]),
            ],

            // Access
            'site_access_granted' => $this->site_access_granted,
            'status' => $this->status,
            'is_signed_in' => $this->isSignedIn(),
            'notes' => $this->notes,

            // Relationships
            'company' => new ContractorCompanyResource($this->whenLoaded('company')),
            'inductions' => ContractorInductionResource::collection($this->whenLoaded('inductions')),
            'certifications' => ContractorCertificationResource::collection($this->whenLoaded('certifications')),
            'sign_in_logs' => SignInLogResource::collection($this->whenLoaded('signInLogs')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
