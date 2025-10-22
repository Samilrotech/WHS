<?php

namespace App\Modules\ContractorManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractorCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit_contractor_companies');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = $this->route('contractor_company') ?? $this->route('contractorCompany');

        return [
            'company_name' => ['required', 'string', 'max:255'],
            'abn' => ['required', 'string', 'max:11', Rule::unique('contractor_companies')->ignore($companyId)],
            'acn' => ['nullable', 'string', 'max:9'],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'primary_contact_name' => ['required', 'string', 'max:255'],
            'primary_contact_phone' => ['required', 'string', 'max:20'],
            'primary_contact_email' => ['required', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'public_liability_insurer' => ['nullable', 'string', 'max:255'],
            'public_liability_policy_number' => ['nullable', 'string', 'max:255'],
            'public_liability_expiry_date' => ['nullable', 'date'],
            'public_liability_coverage_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999999999.99'],
            'workers_comp_insurer' => ['nullable', 'string', 'max:255'],
            'workers_comp_policy_number' => ['nullable', 'string', 'max:255'],
            'workers_comp_expiry_date' => ['nullable', 'date'],
            'performance_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'abn' => 'ABN',
            'acn' => 'ACN',
            'public_liability_expiry_date' => 'public liability expiry date',
            'public_liability_coverage_amount' => 'public liability coverage amount',
            'workers_comp_expiry_date' => 'workers compensation expiry date',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'abn.unique' => 'This ABN is already registered to another company.',
        ];
    }
}
