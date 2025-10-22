<?php

namespace App\Modules\ContractorManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInductionModuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit_induction_modules');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'content' => ['required', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'video_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:999'],
            'has_quiz' => ['required', 'boolean'],
            'pass_mark_percentage' => ['required_if:has_quiz,true', 'integer', 'min:0', 'max:100'],
            'validity_months' => ['required', 'integer', 'min:1', 'max:60'],
            'is_mandatory' => ['required', 'boolean'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'video_duration_minutes' => 'video duration',
            'pass_mark_percentage' => 'pass mark',
            'validity_months' => 'validity period',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'pass_mark_percentage.required_if' => 'Pass mark is required when quiz is enabled.',
            'has_quiz.required' => 'Please specify whether this module has a quiz.',
        ];
    }
}
