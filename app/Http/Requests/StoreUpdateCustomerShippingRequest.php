<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use App\Models\ShippingContact;


class StoreUpdateCustomerShippingRequest extends FormRequest
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
            'shipping.*.type' => 'required|in:pan_india,local',
            'shipping.*.vendor_id' => 'nullable|exists:vendors,id',
            'shipping.*.plant_id' => 'nullable|exists:plants,id',
            'shipping.*.route_id' => 'nullable|exists:routes,id',
            'shipping.*.driver_id' => 'nullable|exists:drivers,id',
            'shipping.*.shipping_address' => 'required|string|max:255',
            'shipping.*.shipping_country' => 'required|string|max:255',
            'shipping.*.shipping_state' => 'nullable|string|max:255',
            'shipping.*.shipping_city' => 'required|string|max:255',
            'shipping.*.shipping_pincode' => 'required|digits:6',
            'shipping.*.machine_deployed' => 'nullable|string|max:255',

            'shipping.*.shipping_contacts.*.name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'shipping.*.shipping_contacts.*.phone' => 'required|digits:10,',
            
            'contract.*.product_id' => 'required|exists:products,id',
            'contract.*.quantity' => 'required|integer|min:1',
            'contract.*.price' => 'required|numeric|max:255|min:1',
            'contract.*.duration' => 'nullable|integer|min:1',
            'contract.*.duration_type' => 'nullable|string|in:days,weeks,months,years',
            'contract.*.frequency' => 'required|string|in:daily,alternate_day,weekly,monthly',
            'contract.*.frequency_count' => 'nullable|integer|min:1',
            'contract.*.days' => 'nullable|array',
            'contract.*.days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping.*.type.required' => 'The type is required.',
            'shipping.*.type.in' => 'The type must be either "pan_india" or "local".',
            'shipping.*.plant_id.required' => 'The plant ID is required.',
            'shipping.*.plant_id.exists' => 'The selected plant ID is invalid.',
            'shipping.*.route_id.required' => 'The route ID is required.',
            'shipping.*.route_id.exists' => 'The selected route ID is invalid.',
            'shipping.*.driver_id.required' => 'The driver ID is required.',
            'shipping.*.driver_id.exists' => 'The selected driver ID is invalid.',

            'shipping.*.shipping_address.required' => 'The shipping address is required.',
            'shipping.*.shipping_address.string' => 'The shipping address must be a string.',
            'shipping.*.shipping_address.max' => 'The shipping address may not be greater than 255 characters.',
    
            'shipping.*.shipping_country.required' => 'The shipping country is required.',
            'shipping.*.shipping_country.string' => 'The shipping country must be a string.',
            'shipping.*.shipping_country.max' => 'The shipping country may not be greater than 255 characters.',
        
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
        
            'shipping.*.shipping_contacts.*.phone.required' => 'The contact person\'s phone number is required.',
            'shipping.*.shipping_contacts.*.phone.digits' => 'The contact person\'s phone number must be exactly 10 digits.',
        
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
            $data = $this->all(); // Use $this->all() instead of $request->all()

            foreach ($data['shipping'] ?? [] as $index => $item) {
                $type = $item['type'] ?? null;

                if ($type === 'pan_india') {
                    if (empty($item['vendor_id'])) {
                        $validator->errors()->add("shipping.$index.vendor_id", 'The vendor is required.');
                    }
                }

                if ($type === 'local') {
                    $fieldLabels = [
                        'plant_id' => 'plant',
                        'route_id' => 'route',
                        'driver_id' => 'driver',
                    ];

                    foreach (['plant_id', 'route_id', 'driver_id'] as $field) {
                        if (empty($item[$field])) {
                            $label = $fieldLabels[$field];
                            $validator->errors()->add("shipping.$index.$field", "The $label is required.");
                        }
                    }
                }
            }

            foreach ($data['contract'] ?? [] as $index => $contract) {
                $days = $contract['days'] ?? [];

                if (
                    $contract['frequency'] === 'weekly' &&
                    !empty($contract['frequency_count']) &&
                    (int) $contract['frequency_count'] < count($days)
                ) {
                    $validator->errors()->add(
                        "contract.$index.days[]",
                        'Number of selected days cannot be greater than the Frequency count.'
                    );
                }
            }

            foreach ($data['shipping'] ?? [] as $sIndex => $shipping) {
                foreach ($shipping['shipping_contacts'] ?? [] as $cIndex => $contact) {
                    $contactId = $contact['id'] ?? null;
        
                    // Check for duplicate name in DB
                    $existingName = ShippingContact::where('name', $contact['name'] ?? '')
                        ->when($contactId, fn($query) => $query->where('id', '!=', $contactId))
                        ->exists();
        
                    if ($existingName) {
                        $validator->errors()->add(
                            "shipping.$sIndex.shipping_contacts.$cIndex.name",
                            'The contact person name has already been taken.'
                        );
                    }
        
                    // Check for duplicate phone in DB
                    $existingPhone = ShippingContact::where('phone', $contact['phone'] ?? '')
                        ->when($contactId, fn($query) => $query->where('id', '!=', $contactId))
                        ->exists();
        
                    if ($existingPhone) {
                        $validator->errors()->add(
                            "shipping.$sIndex.shipping_contacts.$cIndex.phone",
                            'The contact person phone number has already been taken.'
                        );
                    }
                }
            }
        });
    }
}
