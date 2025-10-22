<?php

namespace App\Modules\ComplianceReporting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplianceReportRequest extends FormRequest
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
            'report_type' => ['required', 'in:periodic,audit,incident-based,regulatory,custom'],
            'period' => ['required', 'in:daily,weekly,monthly,quarterly,yearly,custom'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'requirements_included' => ['nullable', 'array'],
            'requirements_included.*' => ['exists:compliance_requirements,id'],
            'executive_summary' => ['nullable', 'string'],
            'key_findings' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'auto_generate_metrics' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'report_type' => 'report type',
            'period_start' => 'period start date',
            'period_end' => 'period end date',
            'requirements_included' => 'included requirements',
        ];
    }
}
