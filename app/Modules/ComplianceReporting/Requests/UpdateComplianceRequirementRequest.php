<?php

namespace App\Modules\ComplianceReporting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplianceRequirementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['sometimes', 'required', 'in:legal,regulatory,industry,internal,certification'],
            'frequency' => ['sometimes', 'required', 'in:daily,weekly,monthly,quarterly,yearly,once'],
            'due_date' => ['nullable', 'date'],
            'last_review_date' => ['nullable', 'date'],
            'next_review_date' => ['nullable', 'date'],
            'owner_id' => ['sometimes', 'required', 'exists:users,id'],
            'reviewer_id' => ['nullable', 'exists:users,id'],
            'status' => ['sometimes', 'required', 'in:compliant,non-compliant,partial,not-applicable,under-review'],
            'compliance_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'evidence_required' => ['nullable', 'string'],
            'evidence_files' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'risk_level' => ['sometimes', 'required', 'in:low,medium,high,critical'],
            'non_compliance_impact' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'owner_id' => 'requirement owner',
            'reviewer_id' => 'reviewer',
            'risk_level' => 'risk level',
            'compliance_score' => 'compliance score',
        ];
    }
}
