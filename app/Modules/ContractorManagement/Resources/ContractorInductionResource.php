<?php

namespace App\Modules\ContractorManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractorInductionResource extends JsonResource
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
            'induction_module_id' => $this->induction_module_id,

            // Progress
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'time_spent_minutes' => $this->time_spent_minutes,

            // Video progress
            'video' => [
                'watched' => $this->video_watched,
                'progress_percentage' => $this->video_progress_percentage,
            ],

            // Quiz results
            'quiz' => [
                'score' => $this->quiz_score,
                'attempts' => $this->quiz_attempts,
                'passed' => $this->quiz_passed,
            ],

            // Status
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'status' => $this->status,
            'is_expired' => $this->isExpired(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'completion_percentage' => $this->completion_percentage,

            // Certificate
            'certificate' => [
                'number' => $this->certificate_number,
                'issued_at' => $this->certificate_issued_at?->toIso8601String(),
            ],

            // Relationships
            'contractor' => new ContractorResource($this->whenLoaded('contractor')),
            'induction_module' => new InductionModuleResource($this->whenLoaded('inductionModule')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
