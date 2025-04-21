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
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
        $routes = Routes::with('plant')->whereHas('drivers')->get();
        $drivers = Drivers::with('routes')->get();
        return view('pages.customer.add-edit',compact('show', 'plants', 'products', 'routes', 'drivers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_zohi_id' => 'required|string|unique:customers',
            'name' => 'required|string|max:255', 
            'email' => 'nullable|email|unique:customers,email|max:255', 
            'phone_no' => 'nullable|digits:10',
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required',

            'shipping.*.plant_id' => 'required|exists:plants,id',
            'shipping.*.route_id' => 'required|exists:routes,id',
            'shipping.*.driver_id' => 'required|exists:drivers,id',
            'shipping.*.shipping_address' => 'required|string|max:255',
            'shipping.*.shipping_country' => 'required|string|max:255',
            'shipping.*.shipping_state' => 'nullable|string|max:255',
            'shipping.*.shipping_city' => 'required|string|max:255',
            'shipping.*.shipping_pincode' => 'required|digits:6',
            'shipping.*.contact_person' => 'required|string|max:255',
            'shipping.*.contact_person_phone' => 'required|digits:10',
            'shipping.*.machine_deployed' => 'nullable|string|max:255',

            'contract.*.product_id' => 'required|exists:products,id',
            'contract.*.quantity' => 'required|integer|min:1',
            'contract.*.price' => 'required|string|max:255',
            'contract.*.duration' => 'nullable|integer|min:1',
            'contract.*.duration_type' => 'nullable|string|in:days,weeks,months,years',
            'contract.*.frequency' => 'required|string|in:daily,alternate_day,weekly,monthly',
            'contract.*.frequency_count' => 'nullable|integer|min:1',
            'contract.*.days' => 'nullable|array',
            'contract.*.days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ],
        [
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
        
            'shipping.*.contact_person.required' => 'The contact person is required.',
            'shipping.*.contact_person.string' => 'The contact person must be a string.',
            'shipping.*.contact_person.max' => 'The contact person name may not be greater than 255 characters.',
        
            'shipping.*.contact_person_phone.required' => 'The contact person\'s phone number is required.',
            'shipping.*.contact_person_phone.digits' => 'The contact person\'s phone number must be exactly 10 digits.',
        
            'shipping.*.machine_deployed.string' => 'The machine deployed field must be a string.',
            'shipping.*.machine_deployed.max' => 'The machine deployed field may not be greater than 255 characters.',

            'contract.*.product_id.required' => 'The product ID is required.',
            'contract.*.product_id.exists' => 'The selected product ID is invalid.',
            'contract.*.quantity.required' => 'The quantity is required.',
            'contract.*.quantity.integer' => 'The quantity must be an integer.',
            'contract.*.quantity.min' => 'The quantity must be at least 1.',
            'contract.*.price.required' => 'The price is required.',
            'contract.*.price.string' => 'The price must be a string.',
            'contract.*.price.max' => 'The price may not be greater than 255 characters.',
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
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $days = $request->input('days');
        $frequencyCount = $request->input('frequency_count');
        $frequency = $request->input('frequency');

        if (
            $frequency !== 'daily' &&
            is_array($days) &&
            !empty($frequencyCount) &&
            $frequencyCount > count($days)
        ) {
            $validator->errors()->add(
                'frequency_count',
                'Frequency count cannot be greater than the number of selected days.'
            );
        }
        if($request->frequency_count > $request->days){
            return response()->json(['error' => 'Frequency count cannot be greater than the number of days.'], 422);
        }
        DB::beginTransaction();
        try {
            $customerData = $request->only([
                'customer_zohi_id', 'plant_id', 'name', 'email', 'phone_no',
                'billing_address', 'billing_country', 'billing_state', 'billing_city', 'billing_pincode'
            ]);
            $customer = Customers::create($customerData);
            $orders = [];

            foreach ($request->shipping as $key => $shippingData) {
                $contract = Contracts::create([
                    'customer_id'      => $customer->id,
                    'product_id'       => $request->contract[$key]['product_id'],
                    'quantity'         => $request->contract[$key]['quantity'],
                    'price'            => $request->contract[$key]['price'],
                    'duration'         => $request->contract[$key]['duration'],
                    'duration_type'    => $request->contract[$key]['duration_type'],
                    'frequency'        => $request->contract[$key]['frequency'],
                    'frequency_count'  => $request->contract[$key]['frequency_count'],
                    'days' => is_array(Arr::get($request->contract[$key] ?? [], 'days'))
                                ? implode('|', $request->contract[$key]['days'])
                                : null,
                    'status'           => 'active',
                ]);
                $shippingData['customer_id'] = $customer->id;
                $shippingData['contract_id'] = $contract->id;
                $shipping = ShippingAddress::create($shippingData);
                $todayDay = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
                $contractDays = explode('|', strtolower($contract->days ?? ''));
                if (in_array($todayDay, $contractDays)) {
                    $order = Orders::create([
                        'customer_id'    => $customer->id,
                        'contract_id'    => $contract->id,
                        'driver_id'      => $address->driver_id,
                        'shipping_id'    => $address->id,
                        'route_id'       => $address->route_id,
                        'status'         => 'pending',
                        'develivered_qty'  => $contract->quantity, // ✅ corrected spelling
                        'return_qty'     => 0,
                    ]);
                    $orders[] = $order;
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Customer created successfully!',
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
        $routes = Routes::with('plant')->whereHas('drivers')->get();
        $drivers = Drivers::with('routes')->get();
        return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products', 'routes', 'drivers'));
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
            $routes = Routes::with('plant')->whereHas('drivers')->get();
            $drivers = Drivers::with('routes')->get();
            return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products', 'routes', 'drivers'));
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
            'name' => 'required|string|max:255', 
            'email' => 'nullable|email|unique:customers,email,' . $id . '|max:255',
            'phone_no' => 'nullable|digits:10',
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required|digits:6',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {

            $customer = Customers::findOrFail($id);
            $customer->update($request->all());
            
            $shippingAddress = ShippingAddress::where('customer_id', $id)->get();
            if (count($shippingAddress) == 0) {
                return response()->json([
                    'error' => 'No shipping address found for this customer, please add a shipping address.',
                ], 404);
            } else {
                return response()->json([
                    'message' => 'Customer updated successfully!',
                ], 200);
            }
        
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

    public function storeUpdateShippingAddress(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shipping.*.plant_id' => 'required|exists:plants,id',
            'shipping.*.route_id' => 'required|exists:routes,id',
            'shipping.*.driver_id' => 'required|exists:drivers,id',
            'shipping.*.shipping_address' => 'required|string|max:255',
            'shipping.*.shipping_country' => 'required|string|max:255',
            'shipping.*.shipping_state' => 'nullable|string|max:255',
            'shipping.*.shipping_city' => 'required|string|max:255',
            'shipping.*.shipping_pincode' => 'required|digits:6',
            'shipping.*.contact_person' => 'required|string|max:255',
            'shipping.*.contact_person_phone' => 'required|digits:10',
            'shipping.*.machine_deployed' => 'nullable|string|max:255',

            'contract.*.product_id' => 'required|exists:products,id',
            'contract.*.quantity' => 'required|integer|min:1',
            'contract.*.price' => 'required|string|max:255',
            'contract.*.duration' => 'nullable|integer|min:1',
            'contract.*.duration_type' => 'nullable|string|in:days,weeks,months,years',
            'contract.*.frequency' => 'required|string|in:daily,alternate_day,weekly,monthly',
            'contract.*.frequency_count' => 'nullable|integer|min:1',
            'contract.*.days' => 'nullable|array',
            'contract.*.days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ], 
        [
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
        
            'shipping.*.contact_person.required' => 'The contact person is required.',
            'shipping.*.contact_person.string' => 'The contact person must be a string.',
            'shipping.*.contact_person.max' => 'The contact person name may not be greater than 255 characters.',
        
            'shipping.*.contact_person_phone.required' => 'The contact person\'s phone number is required.',
            'shipping.*.contact_person_phone.digits' => 'The contact person\'s phone number must be exactly 10 digits.',
        
            'shipping.*.machine_deployed.string' => 'The machine deployed field must be a string.',
            'shipping.*.machine_deployed.max' => 'The machine deployed field may not be greater than 255 characters.',

            'contract.*.product_id.required' => 'The product ID is required.',
            'contract.*.product_id.exists' => 'The selected product ID is invalid.',
            'contract.*.quantity.required' => 'The quantity is required.',
            'contract.*.quantity.integer' => 'The quantity must be an integer.',
            'contract.*.quantity.min' => 'The quantity must be at least 1.',
            'contract.*.price.required' => 'The price is required.',
            'contract.*.price.string' => 'The price must be a string.',
            'contract.*.price.max' => 'The price may not be greater than 255 characters.',
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
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction(); // Make sure the transaction is started
        
            $customer = Customers::findOrFail($id);
            $orders = [];
        
            foreach ($request->shipping as $key => $shippingData) {
                $contractData = $request->contract[$key] ?? [];
        
                // 1. Handle Contract
                if (!empty($contractData['id'])) {
                    $contract = Contracts::findOrFail($contractData['id']);
                    $contract->update([
                        'product_id'      => $contractData['product_id'],
                        'quantity'        => $contractData['quantity'],
                        'price'           => $contractData['price'],
                        'duration'        => $contractData['duration'],
                        'duration_type'   => $contractData['duration_type'],
                        'frequency'       => $contractData['frequency'],
                        'frequency_count' => $contractData['frequency_count'],
                        'days'            => is_array($contractData['days'] ?? null)
                            ? implode('|', $contractData['days'])
                            : null,
                    ]);
                } else {
                    $contract = Contracts::create([
                        'customer_id'     => $customer->id,
                        'product_id'      => $contractData['product_id'],
                        'quantity'        => $contractData['quantity'],
                        'price'           => $contractData['price'],
                        'duration'        => $contractData['duration'],
                        'duration_type'   => $contractData['duration_type'],
                        'frequency'       => $contractData['frequency'],
                        'frequency_count' => $contractData['frequency_count'],
                        'days'            => is_array($contractData['days'] ?? null)
                            ? implode('|', $contractData['days'])
                            : null,
                        'status' => 'active',
                    ]);
                }
        
                // 2. Handle Shipping Address
                $shippingData['customer_id'] = $customer->id;
                $shippingData['contract_id'] = $contract->id;
        
                if (!empty($shippingData['id'])) {
                    $address = ShippingAddress::findOrFail($shippingData['id']);
                    $address->update($shippingData);
                } else {
                    $address = ShippingAddress::create($shippingData);
                }
        
                // 3. Generate Order if needed
                $todayDay = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
                $contractDays = explode('|', strtolower($contract->days ?? ''));
        
                if (in_array($todayDay, $contractDays)) {
                    $existingOrder = Orders::where('customer_id', $customer->id)
                        ->where('contract_id', $contract->id)
                        ->whereDate('created_at', Carbon::today())
                        ->first();
        
                    if ($existingOrder && $existingOrder->status === 'complete') {
                        continue;
                    }
        
                    if ($existingOrder && $existingOrder->status === 'pending') {
                        $existingOrder->update([
                            'develivered_qty' => $contract->quantity, // ✅ corrected spelling
                            'driver_id'     => $address->driver_id,
                            'route_id'      => $address->route_id,
                        ]);
                        $orders[] = $existingOrder;
                    } else {
                        $order = Orders::create([
                            'customer_id'    => $customer->id,
                            'contract_id'    => $contract->id,
                            'driver_id'      => $address->driver_id,
                            'shipping_id'    => $address->id,
                            'route_id'       => $address->route_id,
                            'status'         => 'pending',
                            'develivered_qty'  => $contract->quantity, // ✅ corrected spelling
                            'return_qty'     => 0,
                        ]);
                        $orders[] = $order;
                    }
                }
            }
        
            DB::commit();
        
            return response()->json([
                'message' => 'Shipping addresses updated successfully!',
            ]);
        
        }
         catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }

    }
}
