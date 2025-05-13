<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $id = $this->id;
        return [
            'customer_zohi_id' => [
                'required',
                'string',
                Rule::unique('customers')->ignore($id)->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'email' => [
                'nullable',
                'email',
                'max:255',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('customers')->ignore($id)->whereNull('deleted_at'),
            ],
            'phone_no' => [
                'nullable',
                'digits:10',
                Rule::unique('customers')->ignore($id)->whereNull('deleted_at'),
            ],
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required|digits:6',
        ];
    }
}
