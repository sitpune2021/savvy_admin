<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\Orders;
use App\Models\Product;
use App\Models\Contracts;
use App\Models\Routes;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\DB;

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
        $customers= Customers::whereHas('contracts')->whereHas('shippingAddresses')->with('contracts.product', 'shippingAddresses')->get();
        $contracts = Contracts::all();
        $shippingAddresses = ShippingAddress::all();
        // $routes = Routes::whereHas('drivers')->get();
        return view('pages.order.add-edit',compact('show', 'customers' ,  'contracts', 'shippingAddresses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'shipping_id' => 'required|array',
            'shipping_id.*' => 'exists:shipping_addresses,id',
            'develivered_qty' => 'nullable|integer|min:0',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            foreach ($request->shipping_id as $shippingId) {
            $shipping = ShippingAddress::where('id', $shippingId)->first();
                Orders::create([
                    'customer_id' => $request->customer_id,
                    'contract_id' => $shipping?->contract_id,
                    'shipping_id' => $shippingId,
                    'route_id' => $shipping?->route_id,
                    'driver_id' => $shipping?->driver_id,
                    'develivered_qty' => $request->develivered_qty,
                    'return_qty' => 0,
                    'status' => 'pending',
                ]);
            }
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
        $customers= Customers::whereHas('contracts')->whereHas('shippingAddresses')->with('contracts.product', 'shippingAddresses')->get();
        // $routes = Routes::whereHas('drivers')->get();
        $contracts = Contracts::all();
        $shippingAddresses = ShippingAddress::all();
        return view('pages.order.add-edit',compact('show', 'customers' , 'Order', 'contracts', 'shippingAddresses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Order = Orders::findOrFail($id);
            $customers= Customers::whereHas('contracts')->whereHas('shippingAddresses')->with('contracts.product', 'shippingAddresses')->get();
            // $routes = Routes::whereHas('drivers')->get();
            $contracts = Contracts::all();
        $shippingAddresses = ShippingAddress::all();
            return view('pages.order.add-edit',compact('show', 'customers' , 'Order','contracts', 'shippingAddresses'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Orders not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Orders for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'customer_id' => 'required|exists:customers,id',
    //         'contract_id' => 'nullable|exists:contracts,id',
    //         'shipping_id' => 'nullable|exists:shipping_addresses,id',
    //         'route_id' => 'nullable|exists:routes,id',
    //         'develivered_qty' => 'nullable|integer|min:0',
    //     ]);
        
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }
        
    //     try {
    //         $order = Orders::findOrFail($id);
    //         $driver = Drivers::where('route_id', $request->route_id)->first();
    //         $order->update([
    //             'customer_id' => $request->customer_id,
    //             'contract_id' => $request->contract_id,
    //             'shipping_id' => $request->shipping_id,
    //             'route_id' => $request->route_id,
    //             'driver_id' => $driver->id,
    //             'develivered_qty' => $request->develivered_qty,
    //             'return_qty' => $request->return_qty,
    //             'status' => 'pending',
    //         ]);
    //         return response()->json([
    //             'message' => 'Order updated successfully!',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
        
    // }

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

    public function storeRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [   
        'order.*.shipping_id' => 'required|integer|exists:shipping_addresses,id',
        'order.*.route_id' => 'required|integer|exists:routes,id',
        ], [
            'order.*.shipping_id.required' => 'Shipping ID is required for each order.',
            'order.*.route_id.required' => 'Route ID is required for each order.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            foreach ($request->order as $orderData) {
                $order = Orders::findOrFail($orderData['id']);
                $driver_id = Drivers::where('route_id', $orderData['route_id'])->first();
                $order->update([
                    'shipping_id' => $orderData['shipping_id'],
                    'route_id' => $orderData['route_id'],
                    'driver_id' => $driver_id->id ?? null,
                ]);
            }
            return response()->json([
                'message' => 'Orders updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
