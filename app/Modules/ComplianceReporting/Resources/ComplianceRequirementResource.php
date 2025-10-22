<?php

namespace App\Modules\ComplianceReporting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceRequirementResource extends JsonResource
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
            'requirement_number' => $this->requirement_number,

            // Details
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'frequency' => $this->frequency,

            // Dates
            'due_date' => $this->due_date?->toDateString(),
            'last_review_date' => $this->last_review_date?->toDateString(),
            'next_review_date' => $this->next_review_date?->toDateString(),

            // Ownership
            'owner_id' => $this->owner_id,
            'reviewer_id' => $this->reviewer_id,

            // Status
            'status' => $this->status,
            'compliance_score' => $this->compliance_score,
            'is_compliant' => $this->isCompliant(),
            'is_overdue' => $this->isOverdue(),
            'is_review_due_soon' => $this->isReviewDueSoon(),

            // Evidence
            'evidence_required' => $this->evidence_required,
            'evidence_files' => $this->evidence_files,
            'notes' => $this->notes,

            // Risk
            'risk_level' => $this->risk_level,
            'risk_badge_color' => $this->getRiskBadgeColor(),
            'non_compliance_impact' => $this->non_compliance_impact,

            // Styling helpers
            'status_badge_color' => $this->getStatusBadgeColor(),

            // Relationships
            'owner' => $this->when($this->owner, [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
                'email' => $this->owner?->email,
            ]),
            'reviewer' => $this->when($this->reviewer, [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->name,
                'email' => $this->reviewer?->email,
            ]),
            'checks_count' => $this->when(isset($this->checks), $this->checks->count()),
            'actions_count' => $this->when(isset($this->actions), $this->actions->count()),
            'latest_check' => $this->when($this->relationLoaded('checks'), function () {
                $latest = $this->latestCheck();
                return $latest ? [
                    'id' => $latest->id,
                    'check_date' => $latest->check_date->toDateString(),
                    'result' => $latest->result,
                    'score' => $latest->score,
                ] : null;
            }),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
