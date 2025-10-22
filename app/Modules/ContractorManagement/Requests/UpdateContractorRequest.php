<?php

namespace App\Modules\ContractorManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit_contractors');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $contractorId = $this->route('contractor');

        return [
            'contractor_company_id' => ['required', 'integer', 'exists:contractor_companies,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contractors')->ignore($contractorId)],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'driver_license_number' => ['nullable', 'string', 'max:50'],
            'driver_license_expiry' => ['nullable', 'date'],
            'induction_completed' => ['nullable', 'boolean'],
            'induction_completion_date' => ['nullable', 'date', 'before_or_equal:today'],
            'induction_expiry_date' => ['nullable', 'date'],
            'inducted_by' => ['nullable', 'uuid', 'exists:users,id'],
            'site_access_granted' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'contractor_company_id' => 'contractor company',
            'driver_license_expiry' => 'driver license expiry date',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered to another contractor.',
        ];
    }
}
