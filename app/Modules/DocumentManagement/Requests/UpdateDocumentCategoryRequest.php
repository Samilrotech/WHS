<?php

namespace App\Modules\DocumentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentCategoryRequest extends FormRequest
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
        $categoryId = $this->route('document_category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('document_categories', 'slug')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:7'],
            'parent_id' => ['nullable', 'exists:document_categories,id'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'requires_approval' => ['boolean'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
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
