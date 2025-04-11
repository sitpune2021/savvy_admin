<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Exception;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $driverId = $request->driver_id;
        $count = $request->count;
        $status = $request->status;
    
        if (!$driverId) {
            return response()->json([
                'status' => false,
                'message' => 'Driver ID is required.',
            ], 422);
        }
    
        if ($count) {
            $today = Carbon::today();
    
            $baseQuery = Orders::where('driver_id', $driverId);
            $todayQuery = (clone $baseQuery)->whereDate('created_at', $today);
    
            $statuses = ['pending', 'completed', 'in_progress', 'cancelled'];
            $data = [
                'all_orders' => $baseQuery->count(),
                'todays_orders' => $todayQuery->count(),
            ];
    
            foreach ($statuses as $status) {
                $data["all_{$status}_orders"] = (clone $baseQuery)->where('status', $status)->count();
                $data["todays_{$status}_orders"] = (clone $todayQuery)->where('status', $status)->count();
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Order statistics retrieved successfully.',
                'data' => $data
            ], 200);
        }
    
        if ($status) {
            $ordersQuery = Orders::where('driver_id', $driverId)->with(['customers:id,name']);
    
            if ($status !== 'all') {
                $ordersQuery->where('status', $status);
            }
    
            $orders = $ordersQuery->latest()->get();
    
            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No orders found for the given status.',
                    'data' => []
                ], 404);
            }
    
            $transformedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => optional($order->customers)->name,
                    'contract_id' => $order->contract_id,
                    'driver_id' => $order->driver_id,
                    'status' => $order->status,
                    'develivered_qty' => $order->develivered_qty,
                    'return_qty' => $order->return_qty,
                    'delevered_card_img' => $order->delevered_card_img,
                    'return_card_img' => $order->return_card_img,
                    'deleted_at' => $order->deleted_at,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });
    
            return response()->json([
                'status' => true,
                'message' => 'Orders retrieved successfully.',
                'data' => $transformedOrders
            ], 200);
        }
    
        return response()->json([
            'status' => false,
            'message' => 'Please provide either a count flag or a status value.',
        ], 400);
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $driverId = $request->driver_id;
        if (!$driverId) {
            return response()->json([
                'status' => false,
                'message' => 'Driver ID is required.',
            ], 422);
        }
        $order = Orders::where('driver_id', $driverId)->find($id);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully.',
            'data' => $order
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'driver_id' => 'required|exists:drivers,id', 
            'develivered_qty' => 'required|integer|min:0',
            'return_qty' => 'required|integer|min:0',
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
        //
    }

    public function updateCard(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'delevered_card_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'return_card_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $order = Orders::findOrFail($id);
            $order->update($request->except('delevered_card_img', 'return_card_img'));

        
            // Handle Pan Card Upload
            if ($request->hasFile('delevered_card_img')) {
                if ($order->delevered_card_img) {
                    Storage::delete('public/OrderCard/' . $order->delevered_card_img); // Corrected $jobPost to $Driver
                }
                $panCard = $request->file('delevered_card_img');
                $panCardFile = Str::random(10) . '.' . $panCard->getClientOriginalExtension();
                $panCard->storeAs('public/OrderCard', $panCardFile);
                $order->delevered_card_img = $panCardFile;
            }
        
            // Handle Aadhar Card Upload
            if ($request->hasFile('return_card_img')) {
                if ($order->return_card_img) {
                    Storage::delete('public/OrderCard/' . $order->return_card_img); // Corrected $jobPost to $Driver
                }
                $aadharCard = $request->file('return_card_img');
                $aadharCardFile = Str::random(10) . '.' . $aadharCard->getClientOriginalExtension();
                $aadharCard->storeAs('public/OrderCard', $aadharCardFile);
                $order->return_card_img = $aadharCardFile;
            }
        
            $order->update($request->except('return_card_img', 'delevered_card_img'));

            return response()->json([
                'status' => true,
                'message' => 'Order Card updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
