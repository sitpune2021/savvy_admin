<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\Orders;
use Exception;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Orders::with('customers', 'drivers')->get();
        return view('pages.order.index', compact('orders'));
        //  border-0 mb-0
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;
        $customers= Customers::all();
        $drivers= Drivers::all();
        return view('pages.order.add-edit',compact('show', 'customers' , 'drivers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'driver_id' => 'required|exists:drivers,id', 
            'quantity' => 'required|integer|min:1',     
            'order_details' => 'required|string',   
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            Orders::create($request->all());
            return response()->json([
                'message' => 'Order created successfully!',
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
        $Order = Orders::findOrFail($id);
        $customers= Customers::all();
        $drivers= Drivers::all();
        return view('pages.order.add-edit',compact('show', 'customers' , 'drivers', 'Order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Order = Orders::findOrFail($id);
            $customers= Customers::all();
            $drivers= Drivers::all();
            return view('pages.order.add-edit',compact('show', 'customers' , 'drivers', 'Order'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Orders not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Orders for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'driver_id' => 'required|exists:drivers,id', 
            'quantity' => 'required|integer|min:1',     
            'order_details' => 'required|string',   
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $order = Orders::findOrFail($id);
        
            // Update the order with the new data
            $order->update($request->all());
        
            return response()->json([
                'message' => 'Order updated successfully!',
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
