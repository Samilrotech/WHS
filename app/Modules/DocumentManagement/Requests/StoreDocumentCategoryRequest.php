<?php

namespace App\Modules\DocumentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:document_categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:7'], // Hex color code
            'parent_id' => ['nullable', 'exists:document_categories,id'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'requires_approval' => ['boolean'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'], // Max 10 years
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'parent_id' => 'parent category',
            'requires_approval' => 'approval requirement',
            'retention_days' => 'retention period',
        ];
    }
}
