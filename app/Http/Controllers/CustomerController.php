<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
use App\Models\Contracts;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Orders;
use App\Models\ShippingAddress;
use App\Models\Drivers;
use App\Models\Routes;
use Illuminate\Support\Facades\DB;
use Exception;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customers::all();
        return view('pages.customer.index', compact('customers'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;
        $plants = Plant::all();
        $products = Product::all();
        return view('pages.customer.add-edit',compact('show', 'plants', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_zohi_id' => 'required|string|unique:customers',
            'plant_id' => 'required|exists:plants,id',
            'name' => 'required|string|max:255', 
            'email' => 'nullable|email|unique:customers,email|max:255', 
            'phone_no' => 'nullable|digits:10',
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required',

            'shipping.*.shipping_address' => 'required|string|max:255',
            'shipping.*.shipping_country' => 'required|string|max:255',
            'shipping.*.shipping_state' => 'nullable|string|max:255',
            'shipping.*.shipping_city' => 'required|string|max:255',
            'shipping.*.shipping_pincode' => 'required|digits:6',
            'shipping.*.contact_person' => 'required|string|max:255',
            'shipping.*.contact_person_phone' => 'required|digits:10',
            'shipping.*.machine_deployed' => 'nullable|string|max:255',

            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|string|max:255',
            'delivery_frequency' => 'required|string|max:255',
            'delivery_time' => 'nullable|date_format:H:i',
            'duration' => 'nullable|integer|min:1',
            'duration_type' => 'nullable|string|in:days,weeks,months,years',
        ],
        [
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
        
            'shipping.*.contact_person.required' => 'The contact person is required.',
            'shipping.*.contact_person.string' => 'The contact person must be a string.',
            'shipping.*.contact_person.max' => 'The contact person name may not be greater than 255 characters.',
        
            'shipping.*.contact_person_phone.required' => 'The contact person\'s phone number is required.',
            'shipping.*.contact_person_phone.digits' => 'The contact person\'s phone number must be exactly 10 digits.',
        
            'shipping.*.machine_deployed.string' => 'The machine deployed field must be a string.',
            'shipping.*.machine_deployed.max' => 'The machine deployed field may not be greater than 255 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Create Customer
            $customerData = $request->only([
                'customer_zohi_id', 'plant_id', 'name', 'email', 'phone_no',
                'billing_address', 'billing_country', 'billing_state', 'billing_city', 'billing_pincode'
            ]);
            $customer = Customers::create($customerData);

            // Create Contract
            $contract = Contracts::create([
                'customer_id' => $customer->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'delivery_frequency' => $request->delivery_frequency,
                'delivery_time' => $request->delivery_time,
                'duration' => $request->duration,
                'duration_type' => $request->duration_type,
            ]);

            $orders = [];

            // Create Shipping Addresses and Orders
            foreach ($request->shipping as $shippingData) {
                $shippingData['customer_id'] = $customer->id;
                $shipping = ShippingAddress::create($shippingData);

                $order = Orders::create([
                    'customer_id' => $customer->id,
                    'contract_id' => $contract->id,
                    'shipping_id' => $shipping->id,
                    'status' => 'pending',
                    'delivered_qty' => $contract->quantity, // corrected spelling
                    'return_qty' => 0,
                ]);

                $orders[] = $order;
            }

            DB::commit();

            return response()->json([
                'message' => 'Customer created successfully!',
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource..
     */
    public function show(string $id)
    {
        $show = true;
        $Customer = Customers::with('contracts', 'shippingAddresses')->findOrFail($id);
        $plants = Plant::all();
        $products = Product::all();
        return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Customer = Customers::with('contracts', 'shippingAddresses')->findOrFail($id);
            $plants = Plant::all();
            $products = Product::all();
            return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Customer not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Customer for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_zohi_id' => 'required|string|unique:customers,customer_zohi_id,' . $id,
            'plant_id' => 'required|exists:plants,id',
            'name' => 'required|string|max:255', 
            'email' => 'nullable|email|unique:customers,email,' . $id . '|max:255',
            'phone_no' => 'nullable|digits:10',
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required|digits:6',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|string|max:255',
            'delivery_frequency' => 'required|string|max:255',
            'delivery_time' => 'nullable|date_format:H:i',
            'duration' => 'nullable|integer|min:1',
            'duration_type' => 'nullable|string|in:days,weeks,months,years',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $customer = Customers::findOrFail($id);
            $customer->update($request->all());

            // Update the contract details
            $contract = Contracts::where('customer_id', $id)->first();
            if ($contract) {
                $contract->update([
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                    'delivery_frequency' => $request->delivery_frequency,
                    'delivery_time' => $request->delivery_time,
                    'duration' => $request->duration,
                    'duration_type' => $request->duration_type,
                ]);
            }else {
                Contracts::create([
                    'customer_id' => $id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                    'delivery_frequency' => $request->delivery_frequency,
                    'delivery_time' => $request->delivery_time,
                    'duration' => $request->duration,
                    'duration_type' => $request->duration_type,
                ]);
            }
        
            return response()->json([
                'message' => 'Customer updated successfully!',
            ],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $customer = Customers::findOrFail($id);
            $contract = Contracts::where('customer_id', $id)->first();
            if ($contract) {
                $contract->delete();
            }
            
            $customer->delete();
            return response()->json([
                'message' => 'Customer deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Customer not found.',
                'message' => $e->getMessage(),
            ], 404); 
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the  Customer.',
                'message' => $e->getMessage(),
            ], 500); 
        }
    }

    public function assignRoute(Request $request, $id)
    {
        $customer = Customers::with('shippingAddresses', 'orders')->findOrFail($id);
        $shippingAddresses = $customer->shippingAddresses;
        $orders = $customer->orders;
        $drivers =  Drivers::all();
        $routes = Routes::all();
        $assign = true;
        $show = false;
        return view('pages.customer.assign-route', compact('customer', 'shippingAddresses', 'drivers', 'routes', 'assign', 'show', 'orders'));
    }

    public function storeUpdateShippingAddress(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shipping.*.shipping_address' => 'required|string|max:255',
            'shipping.*.shipping_country' => 'required|string|max:255',
            'shipping.*.shipping_state' => 'nullable|string|max:255',
            'shipping.*.shipping_city' => 'required|string|max:255',
            'shipping.*.shipping_pincode' => 'required|digits:6',
            'shipping.*.contact_person' => 'required|string|max:255',
            'shipping.*.contact_person_phone' => 'required|digits:10',
            'shipping.*.machine_deployed' => 'nullable|string|max:255',
        ], 
        [
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
        
            'shipping.*.contact_person.required' => 'The contact person is required.',
            'shipping.*.contact_person.string' => 'The contact person must be a string.',
            'shipping.*.contact_person.max' => 'The contact person name may not be greater than 255 characters.',
        
            'shipping.*.contact_person_phone.required' => 'The contact person\'s phone number is required.',
            'shipping.*.contact_person_phone.digits' => 'The contact person\'s phone number must be exactly 10 digits.',
        
            'shipping.*.machine_deployed.string' => 'The machine deployed field must be a string.',
            'shipping.*.machine_deployed.max' => 'The machine deployed field may not be greater than 255 characters.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customers::findOrFail($id);
    
            $orders = [];
            foreach ($request->shipping as $shippingData) {
                if (!empty($shippingData['id'])) {
                    $address = ShippingAddress::findOrFail($shippingData['id']);
                    $address->update($shippingData);
                } else {
                    $shippingData['customer_id'] = $customer->id;
                    $address = ShippingAddress::create($shippingData);
    
                    $contract = $customer->contracts()->first();
                    if ($contract) {
                        $order = Orders::create([
                            'customer_id' => $customer->id,
                            'contract_id' => $contract->id,
                            'shipping_id' => $address->id,
                            'status' => 'pending',
                            'delivered_qty' => $contract->quantity,
                            'return_qty' => 0,
                        ]);
                        $orders[] = $order;
                    }
                }
            }
    
            DB::commit();
            return response()->json([
                'message' => 'Shipping addresses updated successfully!',
                'orders_created' => $orders,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }

    }
}
