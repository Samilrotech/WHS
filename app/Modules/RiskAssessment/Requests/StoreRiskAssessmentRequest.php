<?php

namespace App\Modules\RiskAssessment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiskAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => 'required|in:warehouse,pos-installation,on-road,office,contractor',
            'task_description' => 'required|string|max:500',
            'location' => 'required|string|max:255',
            'assessment_date' => 'required|date',

            // Initial risk (before controls)
            'initial_likelihood' => 'required|integer|between:1,5',
            'initial_consequence' => 'required|integer|between:1,5',

            // Residual risk (after controls)
            'residual_likelihood' => 'required|integer|between:1,5',
            'residual_consequence' => 'required|integer|between:1,5',

            // Hazards array (optional)
            'hazards' => 'nullable|array',
            'hazards.*.type' => 'required_with:hazards|string|max:255',
            'hazards.*.description' => 'required_with:hazards|string',
            'hazards.*.consequences' => 'required_with:hazards|string',
            'hazards.*.persons_at_risk' => 'nullable|integer|min:0',
            'hazards.*.affected_groups' => 'nullable|array',

            // Control measures (optional)
            'hazards.*.controls' => 'nullable|array',
            'hazards.*.controls.*.hierarchy' => 'required_with:hazards.*.controls|in:elimination,substitution,engineering,administrative,ppe',
            'hazards.*.controls.*.description' => 'required_with:hazards.*.controls|string',
            'hazards.*.controls.*.responsible_person' => 'nullable|uuid|exists:users,id',
            'hazards.*.controls.*.implementation_date' => 'nullable|date',
            'hazards.*.controls.*.status' => 'nullable|in:planned,implemented,verified',

            // Optional fields
            'review_date' => 'nullable|date|after:assessment_date',
            'status' => 'nullable|in:draft,submitted,approved,rejected',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'initial_likelihood.between' => 'Initial likelihood must be between 1 and 5',
            'initial_consequence.between' => 'Initial consequence must be between 1 and 5',
            'residual_likelihood.between' => 'Residual likelihood must be between 1 and 5',
            'residual_consequence.between' => 'Residual consequence must be between 1 and 5',
            'review_date.after' => 'Review date must be after the assessment date',
        ];
    }
}
