<?php

namespace App\Modules\DocumentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDocumentVersionRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240'], // Max 10MB
            'change_notes' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.max' => 'The file size must not exceed 10MB.',
            'change_notes.required' => 'Please provide a description of the changes in this version.',
        ];
    }
}
