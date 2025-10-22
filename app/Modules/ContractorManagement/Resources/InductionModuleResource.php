<?php

namespace App\Modules\ContractorManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InductionModuleResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,

            // Video
            'video' => [
                'url' => $this->video_url,
                'duration_minutes' => $this->video_duration_minutes,
            ],

            // Quiz
            'quiz' => [
                'has_quiz' => $this->has_quiz,
                'pass_mark_percentage' => $this->pass_mark_percentage,
            ],

            // Settings
            'validity_months' => $this->validity_months,
            'is_mandatory' => $this->is_mandatory,
            'status' => $this->status,
            'display_order' => $this->display_order,

            // Statistics
            'completion_rate' => $this->completion_rate,
            'average_quiz_score' => $this->average_quiz_score,

            // Relationships
            'contractor_inductions' => ContractorInductionResource::collection($this->whenLoaded('contractorInductions')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
