<?php

namespace App\Modules\DocumentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
            'category_id' => ['required', 'exists:document_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'metadata' => ['nullable', 'array'],
            'requires_review' => ['boolean'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'visibility' => ['required', 'in:public,private,restricted'],
            'restricted_to' => ['nullable', 'array'],
            'restricted_to.*' => ['string'],
            'status' => ['required', 'in:draft,active,archived'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'requires_review' => 'review requirement',
            'expiry_date' => 'expiry date',
            'restricted_to' => 'access restrictions',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'expiry_date.after' => 'The expiry date must be a future date.',
        ];
    }
}
