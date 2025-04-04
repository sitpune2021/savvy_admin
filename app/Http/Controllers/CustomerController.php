<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
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
        return view('pages.customer.add-edit',compact('show'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', 
            'email' => 'required|email|max:255', 
            'phone_no' => 'required|digits:10', // assuming 10 digit phone number
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required|digits:6', // assuming 6 digit postal code
            'shipping_address' => 'required|string|max:255',
            'shipping_country' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_pincode' => 'required|digits:6', // assuming 6 digit postal code
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            Customers::create($request->all());
            return response()->json([
                'message' => 'Customer created successfully!',
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
        $Customer= Customers::findOrFail($id);
        return view('pages.customer.add-edit',compact('show', 'Customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Customer = Customers::findOrFail($id);
            return view('pages.customer.add-edit',compact('show', 'Customer'));
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
            'name' => 'required|string|max:255', 
            'email' => 'required|email|max:255', 
            'phone_no' => 'required|digits:10',
            'billing_address' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_pincode' => 'required|digits:6',
            'shipping_address' => 'required|string|max:255',
            'shipping_country' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_pincode' => 'required|digits:6',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $customer = Customers::findOrFail($id);
        
            // Update the order with the new data
            $customer->update($request->all());
        
            return response()->json([
                'message' => 'Customer updated successfully!',
            ]);
        } catch (\Exception $e) {
            // Handle any errors that occur during the update process
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
