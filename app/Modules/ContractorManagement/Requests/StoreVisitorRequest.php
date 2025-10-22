<?php

namespace App\Modules\ContractorManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_visitors');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'purpose_of_visit' => ['required', 'string', 'max:500'],
            'host_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'expected_arrival' => ['nullable', 'date', 'after_or_equal:today'],
            'expected_departure' => ['nullable', 'date', 'after:expected_arrival'],
            'vehicle_registration' => ['nullable', 'string', 'max:20'],
            'parking_location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:expected,on_site,departed,cancelled'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'host_user_id' => 'host',
            'purpose_of_visit' => 'purpose',
            'vehicle_registration' => 'vehicle registration',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'expected_arrival.after_or_equal' => 'Expected arrival must be today or a future date.',
            'expected_departure.after' => 'Expected departure must be after expected arrival.',
        ];
    }
}
