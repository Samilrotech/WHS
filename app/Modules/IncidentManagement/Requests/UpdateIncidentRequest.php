<?php

namespace App\Modules\IncidentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
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
            'type' => ['sometimes', 'in:injury,near-miss,property-damage,environmental,security'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'incident_datetime' => ['sometimes', 'date', 'before_or_equal:now'],
            'location_branch' => ['sometimes', 'string', 'max:255'],
            'location_specific' => ['sometimes', 'string', 'max:255'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['sometimes', 'string', 'min:10'],
            'immediate_actions' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:reported,investigating,resolved,closed'],
            'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
            'root_cause' => ['nullable', 'string'],

            // Photos
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['file', 'image', 'max:10240'],

            // Witnesses
            'witnesses' => ['nullable', 'array'],
            'witnesses.*.id' => ['nullable', 'uuid', 'exists:witnesses,id'],
            'witnesses.*.name' => ['required', 'string', 'max:255'],
            'witnesses.*.contact_number' => ['nullable', 'string', 'max:20'],
            'witnesses.*.email' => ['nullable', 'email', 'max:255'],
            'witnesses.*.statement' => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('requires_emergency')) {
            $this->merge(['requires_emergency' => $this->boolean('requires_emergency')]);
        }

        if ($this->has('notify_authorities')) {
            $this->merge(['notify_authorities' => $this->boolean('notify_authorities')]);
        }
    }
}
