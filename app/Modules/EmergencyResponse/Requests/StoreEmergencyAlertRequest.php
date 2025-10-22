<?php

namespace App\Modules\EmergencyResponse\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmergencyAlertRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:panic,medical,fire,evacuation,other'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Please select an emergency type.',
            'type.in' => 'The selected emergency type is invalid.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'location_description.max' => 'Location description cannot exceed 500 characters.',
            'description.max' => 'Description cannot exceed 2000 characters.',
        ];
    }
}
