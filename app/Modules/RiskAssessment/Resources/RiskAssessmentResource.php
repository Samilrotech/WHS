<?php

namespace App\Modules\RiskAssessment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiskAssessmentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'category' => $this->category,
            'task_description' => $this->task_description,
            'location' => $this->location,
            'assessment_date' => $this->assessment_date->format('Y-m-d'),

            // Initial Risk
            'initial_risk' => [
                'likelihood' => $this->initial_likelihood,
                'consequence' => $this->initial_consequence,
                'score' => $this->initial_risk_score,
                'level' => $this->initial_risk_level,
            ],

            // Residual Risk
            'residual_risk' => [
                'likelihood' => $this->residual_likelihood,
                'consequence' => $this->residual_consequence,
                'score' => $this->residual_risk_score,
                'level' => $this->residual_risk_level,
            ],

            'status' => $this->status,
            'review_date' => $this->review_date?->format('Y-m-d'),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),

            // Relationships
            'user' => [
                'id' => $this->whenLoaded('user', fn() => $this->user->id),
                'name' => $this->whenLoaded('user', fn() => $this->user->name),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
            ],

            'branch' => [
                'id' => $this->whenLoaded('branch', fn() => $this->branch->id),
                'name' => $this->whenLoaded('branch', fn() => $this->branch->name),
                'code' => $this->whenLoaded('branch', fn() => $this->branch->code),
            ],

            'approver' => [
                'id' => $this->whenLoaded('approver', fn() => $this->approver?->id),
                'name' => $this->whenLoaded('approver', fn() => $this->approver?->name),
            ],

            'hazards' => $this->whenLoaded('hazards', function () {
                return $this->hazards->map(function ($hazard) {
                    return [
                        'id' => $hazard->id,
                        'hazard_type' => $hazard->hazard_type,
                        'description' => $hazard->description,
                        'potential_consequences' => $hazard->potential_consequences,
                        'persons_at_risk' => $hazard->persons_at_risk,
                        'affected_groups' => $hazard->affected_groups,
                        'control_measures' => $hazard->controlMeasures->map(function ($control) {
                            return [
                                'id' => $control->id,
                                'hierarchy' => $control->hierarchy,
                                'description' => $control->description,
                                'status' => $control->status,
                                'implementation_date' => $control->implementation_date?->format('Y-m-d'),
                                'responsible_person' => [
                                    'id' => $control->responsiblePerson?->id,
                                    'name' => $control->responsiblePerson?->name,
                                ],
                            ];
                        }),
                    ];
                });
            }),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
