<?php

namespace App\Modules\ComplianceReporting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplianceRequirementRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:legal,regulatory,industry,internal,certification'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,yearly,once'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'owner_id' => ['required', 'exists:users,id'],
            'reviewer_id' => ['nullable', 'exists:users,id'],
            'evidence_required' => ['nullable', 'string'],
            'evidence_files' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'risk_level' => ['required', 'in:low,medium,high,critical'],
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
        ];
    }
}
