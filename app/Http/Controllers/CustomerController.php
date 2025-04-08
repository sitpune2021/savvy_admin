<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
use App\Models\Contracts;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Orders;
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
            'email' => 'required|email||unique:customers|max:255', 
            'phone_no' => 'required|digits:10', // assuming 10 digit phone number
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required', // assuming 6 digit postal code
            'shipping_address' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_pincode' => 'nullable', // assuming 6 digit postal code
            'contact_person' => 'required|string|max:255',
            'contact_person_phone' => 'required|digits:10', // assuming 10 digit phone number
            'machine_deployed' => 'nullable|string|max:255',
            'machine_deployed_date' => 'nullable|date',
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
            
            $customer = Customers::create($request->all());
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
            
                $order = Orders::create([
                    'customer_id' => $customer->id,
                    'status' => 'pending',
                    'develivered_qty' => $contract->quantity,
                    'return_qty' => 0,
                ]);

            return response()->json([
                'message' => 'Customer created successfully!',
                'customer' => $customer,
                'order' => $order,

            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $Customer = Customers::with('contracts')->findOrFail($id);
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
            $Customer = Customers::findOrFail($id);
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
            'email' => 'required|email|unique:customers,email,' . $id . '|max:255',
            'phone_no' => 'required|digits:10',
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required|digits:6',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_pincode' => 'nullable|digits:6',
            'contact_person' => 'required|string|max:255',
            'contact_person_phone' => 'required|digits:10',
            'machine_deployed' => 'nullable|string|max:255',
            'machine_deployed_date' => 'nullable|date',
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
            }
        
            return response()->json([
                'message' => 'Customer updated successfully!',
            ]);
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
}
