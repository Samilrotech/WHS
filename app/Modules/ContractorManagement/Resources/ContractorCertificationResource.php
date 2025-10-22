<?php

namespace App\Modules\ContractorManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractorCertificationResource extends JsonResource
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
            'contractor_id' => $this->contractor_id,

            // Certification details
            'certification_type' => $this->certification_type,
            'certification_number' => $this->certification_number,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'issuing_authority' => $this->issuing_authority,

            // Document
            'document' => [
                'path' => $this->document_path,
                'hash' => $this->document_hash,
                'has_document' => $this->hasDocument(),
            ],

            // Verification
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'verified_by' => $this->when($this->verifier, [
                'id' => $this->verifier?->id,
                'name' => $this->verifier?->name,
            ]),

            // Status
            'status' => $this->status,
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'requires_urgent_renewal' => $this->requiresUrgentRenewal(),
            'days_until_expiry' => $this->days_until_expiry,

            'notes' => $this->notes,

            // Relationships
            'contractor' => new ContractorResource($this->whenLoaded('contractor')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
