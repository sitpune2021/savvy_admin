<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class StoreCustomerRequest extends FormRequest
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
            'customer_zohi_id' => [
                'required',
                'string',
                Rule::unique('customers')->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('customers')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('customers')->whereNull('deleted_at'),
            ],
            'phone_no' => [
                'required',
                'digits:10',
                Rule::unique('customers')->whereNull('deleted_at'),
            ],
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required|digits:6|numeric',
        
            'shipping.*.type' => 'required|in:pan_india,local',
            'shipping.*.vendor_id' => 'nullable|exists:vendors,id',
            'shipping.*.plant_id' => 'nullable|exists:plants,id',
            'shipping.*.route_id' => 'nullable|exists:routes,id',
            'shipping.*.driver_id' => 'nullable|exists:drivers,id',
            'shipping.*.shipping_address' => 'required|string|max:255',
            'shipping.*.email' => 'nullable|email|max:255',
            'shipping.*.shipping_country' => 'required|string|max:255',
            'shipping.*.shipping_state' => 'required|string|max:255',
            'shipping.*.shipping_city' => 'required|string|max:255',
            'shipping.*.shipping_pincode' => 'required|digits:6',
            'shipping.*.po_no' => 'nullable',
        
            'shipping.*.shipping_contacts.*.name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('shipping_contacts', 'name')->whereNull('deleted_at'),
            ],
            'shipping.*.shipping_contacts.*.phone' => [
                'required',
                'digits:10',
                Rule::unique('shipping_contacts', 'phone')->whereNull('deleted_at'),
            ],
            'shipping.*.machine_deployed' => 'nullable|string|max:255',
        
            'contract.*.product_id' => 'required|exists:products,id',
            'contract.*.quantity' => 'required|integer|min:1',
            'contract.*.price' => 'required|numeric|max:255|min:1',
            'contract.*.duration' => 'required|integer|min:1',
            'contract.*.duration_type' => 'required|string|in:days,weeks,months,years',
            'contract.*.frequency' => 'required|string|in:daily,alternate_day,weekly,monthly',
            'contract.*.frequency_count' => 'required_if:contract.*.frequency,weekly,alternate_day|nullable|integer|min:1',
            'contract.*.days' => 'nullable|array',
            'contract.*.days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping.*.type.required' => 'The type is required.',
            'shipping.*.type.in' => 'The type must be either "pan_india" or "local".',
            'shipping.*.plant_id.exists' => 'The selected plant ID is invalid.',
            'shipping.*.route_id.exists' => 'The selected route ID is invalid.',
            'shipping.*.driver_id.exists' => 'The selected driver ID is invalid.',

            'shipping.*.shipping_address.required' => 'The shipping address is required.',
            'shipping.*.shipping_address.string' => 'The shipping address must be a string.',
            'shipping.*.shipping_address.max' => 'The shipping address may not be greater than 255 characters.',
            'shipping.*.email.email' => 'The shipping address email must be a valid email address.',
            'shipping.*.email.max' => 'The shipping address email may not be greater than 255 characters.',
    
            'shipping.*.shipping_country.required' => 'The shipping country is required.',
            'shipping.*.shipping_country.string' => 'The shipping country must be a string.',
            'shipping.*.shipping_country.max' => 'The shipping country may not be greater than 255 characters.',

            'shipping.*.shipping_state.required' => 'The shipping state is required.',
            'shipping.*.shipping_state.string' => 'The shipping state must be a string.',
            'shipping.*.shipping_state.max' => 'The shipping state may not be greater than 255 characters.',
        
            'shipping.*.shipping_city.required' => 'The shipping city is required.',
            'shipping.*.shipping_city.string' => 'The shipping city must be a string.',
            'shipping.*.shipping_city.max' => 'The shipping city may not be greater than 255 characters.',
        
            'shipping.*.shipping_pincode.required' => 'The shipping pincode is required.',
            'shipping.*.shipping_pincode.digits' => 'The shipping pincode must be exactly 6 digits.',
        
            'shipping.*.shipping_contacts.*.name.required' => 'The contact person is required.',
            'shipping.*.shipping_contacts.*.name.string' => 'The contact person must be a string.',
            'shipping.*.shipping_contacts.*.name.max' => 'The contact person name may not be greater than 255 characters.',
            'shipping.*.shipping_contacts.*.name.unique' => 'The contact person name has already been taken.',
        
            'shipping.*.shipping_contacts.*.phone.required' => 'The contact person\'s phone number is required.',
            'shipping.*.shipping_contacts.*.phone.digits' => 'The contact person\'s phone number must be exactly 10 digits.',
            'shipping.*.shipping_contacts.*.phone.unique' => 'The contact person\'s phone number has already been taken.',
        
            'shipping.*.machine_deployed.string' => 'The machine deployed field must be a string.',
            'shipping.*.machine_deployed.max' => 'The machine deployed field may not be greater than 255 characters.',

            'contract.*.product_id.required' => 'The product ID is required.',
            'contract.*.product_id.exists' => 'The selected product ID is invalid.',
            'contract.*.quantity.required' => 'The quantity is required.',
            'contract.*.quantity.integer' => 'The quantity must be an integer.',
            'contract.*.quantity.min' => 'The quantity must be at least 1.',
            'contract.*.price.required' => 'The price is required.',
            'contract.*.price.numeric' => 'The price must be a numeric.',
            'contract.*.price.max' => 'The price may not be greater than 255 characters.',
            'contract.*.price.min' => 'The price must be at least 1.',
            'contract.*.duration.integer' => 'The duration must be an integer.',
            'contract.*.duration.min' => 'The duration must be at least 1.',
            'contract.*.duration_type.string' => 'The duration type must be a string.',
            'contract.*.duration_type.in' => 'The selected duration type is invalid.',
            'contract.*.frequency.required' => 'The frequency is required.',
            'contract.*.frequency.string' => 'The frequency must be a string.',
            'contract.*.frequency.in' => 'The selected frequency is invalid.',
            'contract.*.frequency_count.integer' => 'The frequency count must be an integer.',
            'contract.*.frequency_count.min' => 'The frequency count must be at least 1.',
            'contract.*.frequency_count.required_if' => 'The frequency count field is required when Delivery Frequency is weekly.',
            
            'contract.*.days.array' => 'The days must be an array.',
            'contract.*.days.*.in' => 'The selected days are invalid.',
            'contract.*.days.*.required' => 'The days field is required.',
            'contract.*.days.*.string' => 'The days field must be a string.',
            'contract.*.days.*.max' => 'The days field may not be greater than 255 characters.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Shipping PAN/local logic
            foreach ($data['shipping'] ?? [] as $index => $item) {
                $type = $item['type'] ?? null;

                if ($type === 'pan_india' && empty($item['vendor_id'])) {
                    $validator->errors()->add("shipping.$index.vendor_id", 'The vendor is required.');
                }

                if ($type === 'local') {
                    $fields = ['plant_id' => 'plant', 'route_id' => 'route', 'driver_id' => 'driver'];

                    foreach ($fields as $field => $label) {
                        if (empty($item[$field])) {
                            $validator->errors()->add("shipping.$index.$field", "The $label is required.");
                        }
                    }
                }

                // ✅ Contact name/phone uniqueness if not exited
                foreach ($item['shipping_contacts'] ?? [] as $cIndex => $contact) {
                    $exit = $contact['exit'] ?? null;

                    if ($exit !== 'on') {
                        $name = $contact['name'] ?? null;
                        $phone = $contact['phone'] ?? null;

                        if ($name && \App\Models\ShippingContact::where('name', $name)->whereNull('deleted_at')->exists()) {
                            $validator->errors()->add("shipping.$index.shipping_contacts.$cIndex.name", "The contact name '$name' has already been taken.");
                        }

                        if ($phone && \App\Models\ShippingContact::where('phone', $phone)->whereNull('deleted_at')->exists()) {
                            $validator->errors()->add("shipping.$index.shipping_contacts.$cIndex.phone", "The phone number '$phone' has already been taken.");
                        }
                    }
                }
            }

            // Contract day count vs frequency check (unchanged)
            foreach ($data['contract'] ?? [] as $index => $contract) {
                $days = $contract['days'] ?? [];

                if (
                    in_array($contract['frequency'] ?? '', ['weekly', 'monthly'], true) &&
                    !empty($contract['frequency_count']) &&
                    (int) $contract['frequency_count'] < count($days)
                ) {
                    $validator->errors()->add(
                        "contract.$index.days[]",
                        'Number of selected days cannot be greater than the Frequency count.'
                    );
                }
            }
        });
    }


    
}
