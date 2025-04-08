<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\Orders;
use App\Models\Product;
use App\Models\Contracts;
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
        $products= Product::all();
        return view('pages.order.add-edit',compact('show', 'customers' , 'drivers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'driver_id' => 'required|exists:drivers,id', 
            'develivered_qty' => 'nullable|integer|min:0',
            'return_qty' => 'nullable|integer|min:0',
            // 'status' => 'nullable|in:pending,completed,cancelled',
            // 'product_id' => 'required|exists:products,id',
            // 'quantity' => 'required|integer|min:1',
            // 'price' => 'required|string|max:255',
            // 'delivery_frequency' => 'required|string|max:255',
            // 'delivery_time' => 'nullable|date_format:H:i',
            // 'duration' => 'nullable|integer|min:1',
            // 'duration_type' => 'nullable|string|in:days,weeks,months,years',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            // $contract = Contracts::create([
            //     'customer_id' => $request->customer_id,
            //     'product_id' => $request->product_id,
            //     'quantity' => $request->quantity,
            //     'price' => $request->price,
            //     'delivery_time' => $request->delivery_time,
            //     'duration' => $request->duration,
            //     'duration_type' => $request->duration_type,
            // ]);

            Orders::create([
                'customer_id' => $request->customer_id,
                'driver_id' => $request->driver_id,
                'develivered_qty' => $request->develivered_qty,
                'return_qty' => $request->return_qty,
                'status' => 'pending',
            ]);
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
            'develivered_qty' => 'nullable|integer|min:0',
            'return_qty' => 'nullable|integer|min:0',
            'status' => 'required|in:pending,completed,cancelled',  
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $order = Orders::findOrFail($id);
            $order->update($request->all());
            return response()->json([
                'message' => 'Order updated successfully!',
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
            $Orders = Orders::findOrFail($id);
            $Orders->delete();
            return response()->json([
                'message' => 'Order deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Order not found.',
                'message' => $e->getMessage(),
            ], 404); 
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the  Order.',
                'message' => $e->getMessage(),
            ], 500); 
        }
    }

    public function assignDriver(string $id)
    {
        try {
            $show = false;
            $assign = true;
            $Order = Orders::findOrFail($id);
            $customers= Customers::all();
            $drivers= Drivers::all();
            return view('pages.order.add-edit',compact('show', 'customers' , 'drivers', 'Order', 'assign'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Orders not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Orders for editing: ' . $e->getMessage()]);
        }
    }
}
