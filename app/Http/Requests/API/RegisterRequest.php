<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data for validation: read android_id from header (same as other APIs).
     */
    protected function prepareForValidation(): void
    {
        $androidId = $this->header('X-Android-Id') ?? $this->header('X-Android-ID');
        $this->merge(['android_id' => $androidId]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'android_id' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'android_id.required' => 'Android ID is required. Send it in header X-Android-Id.',
            'android_id.string' => 'Android ID must be a string',
            'android_id.max' => 'Android ID must not exceed 255 characters',
        ];
    }
}

