<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPaymentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transaction_id' => 'required',
            'gateway_order_id' => 'required|string|max:255',
            'gateway_payment_id' => 'nullable|string|max:255',  // optional for PayU (verify by txnid)
            'gateway_signature' => 'nullable|string|max:500',   // optional for PayU
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
            'transaction_id.required' => 'Transaction ID is required',
            'gateway_order_id.required' => 'Gateway order ID is required',
        ];
    }
}

