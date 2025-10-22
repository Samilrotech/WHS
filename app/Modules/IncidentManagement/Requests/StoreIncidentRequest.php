<?php

namespace App\Modules\IncidentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:injury,near-miss,property-damage,environmental,security'],
            'severity' => ['nullable', 'in:low,medium,high,critical'],
            'incident_datetime' => ['required', 'date', 'before_or_equal:now'],
            'location_branch' => ['required', 'string', 'max:255'],
            'location_specific' => ['required', 'string', 'max:255'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'min:10'],
            'immediate_actions' => ['nullable', 'string'],
            'requires_emergency' => ['boolean'],
            'notify_authorities' => ['boolean'],

            // Photos validation (max 10 files @ 10MB each)
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['file', 'image', 'max:10240'], // 10MB

            // Voice note validation
            'voice_note' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:5120'], // 5MB

            // Witnesses validation
            'witnesses' => ['nullable', 'array'],
            'witnesses.*.name' => ['required', 'string', 'max:255'],
            'witnesses.*.contact_number' => ['nullable', 'string', 'max:20'],
            'witnesses.*.email' => ['nullable', 'email', 'max:255'],
            'witnesses.*.statement' => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Please select an incident type.',
            'incident_datetime.required' => 'Incident date and time is required.',
            'incident_datetime.before_or_equal' => 'Incident date cannot be in the future.',
            'description.required' => 'Please provide a description of the incident.',
            'description.min' => 'Description must be at least 10 characters.',
            'photos.max' => 'Maximum 10 photos allowed per incident.',
            'photos.*.max' => 'Each photo must not exceed 10MB.',
            'witnesses.*.name.required' => 'Witness name is required.',
            'witnesses.*.statement.required' => 'Witness statement is required.',
            'witnesses.*.statement.min' => 'Witness statement must be at least 10 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_emergency' => $this->boolean('requires_emergency'),
            'notify_authorities' => $this->boolean('notify_authorities'),
        ]);
    }
}
