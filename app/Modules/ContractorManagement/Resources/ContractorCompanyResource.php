<?php

namespace App\Modules\ContractorManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractorCompanyResource extends JsonResource
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
            'company_name' => $this->company_name,
            'abn' => $this->abn,
            'acn' => $this->acn,
            'trading_name' => $this->trading_name,

            // Contact information
            'primary_contact' => [
                'name' => $this->primary_contact_name,
                'phone' => $this->primary_contact_phone,
                'email' => $this->primary_contact_email,
            ],
            'address' => $this->address,

            // Insurance details
            'public_liability' => [
                'insurer' => $this->public_liability_insurer,
                'policy_number' => $this->public_liability_policy_number,
                'expiry_date' => $this->public_liability_expiry_date?->format('Y-m-d'),
                'coverage_amount' => $this->public_liability_coverage_amount,
                'is_expiring_soon' => $this->isPublicLiabilityExpiringSoon(),
            ],
            'workers_comp' => [
                'insurer' => $this->workers_comp_insurer,
                'policy_number' => $this->workers_comp_policy_number,
                'expiry_date' => $this->workers_comp_expiry_date?->format('Y-m-d'),
                'is_expiring_soon' => $this->isWorkersCompExpiringSoon(),
            ],

            // Verification
            'is_verified' => $this->is_verified,
            'verification_date' => $this->verification_date?->format('Y-m-d'),
            'verified_by' => $this->when($this->verifier, [
                'id' => $this->verifier?->id,
                'name' => $this->verifier?->name,
            ]),

            // Performance
            'performance_rating' => (float) $this->performance_rating,
            'status' => $this->status,
            'notes' => $this->notes,

            // Relationships
            'contractors_count' => $this->when($this->relationLoaded('contractors'),
                $this->contractors->count()
            ),
            'contractors' => ContractorResource::collection($this->whenLoaded('contractors')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
