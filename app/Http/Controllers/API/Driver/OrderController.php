<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            $totalContractQuantityToday = $todayQuery->with('contract')->get()->sum(function ($order) {
                return optional($order->contract)->quantity;
            });
            $statuses = ['pending', 'completed', 'in_progress', 'cancelled'];
            $data = [
                'all_orders' => $baseQuery->count(),
                'todays_orders' => $todayQuery->count(),
                'total_delivery_count' => $totalContractQuantityToday,
                'total_deliverd_count' => $baseQuery->sum('return_qty'),
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
            $ordersQuery = Orders::where('driver_id', $driverId)->with(['customers:id,name', 'shipping:id,shipping_address']);
    
            if ($status !== 'all') {
                $ordersQuery->where('status', $status);
            }
    
            $orders = $ordersQuery->latest()->get();
    
            if ($orders->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'No orders found for the given status.',
                    'data' => []
                ], 200);
            }
    
            $transformedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => optional($order->customers)->name,
                    'contract_id' => $order->contract_id,
                    'driver_id' => $order->driver_id,
                    'shipping_id' => $order->shipping_id,
                    'shipping_address' => optional($order->shipping)->shipping_address,
                    'status' => $order->status,
                    'develivered_qty' => $order->develivered_qty,
                    'return_qty' => $order->return_qty,
                    'delevered_card_img' => $order->delevered_card_img 
                    ? url('storage/OrderCard/' . $order->delevered_card_img) 
                    : null,
                    'return_card_img' => $order->return_card_img 
                    ? url('storage/OrderCard/' . $order->return_card_img) 
                    : null,
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
        $order = Orders::where('driver_id', $driverId)->with('shipping')->find($id);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }
        $order->delevered_card_img =  $order->delevered_card_img 
                    ? url('storage/OrderCard/' . $order->delevered_card_img) 
                    : null;
        $order->return_card_img =   $order->return_card_img 
                    ? url('storage/OrderCard/' . $order->return_card_img) 
                    : null;
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
            'delevered_card_img' => 'nullable',
            'return_card_img' => 'nullable',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            $order = Orders::findOrFail($id);
            $order->update($request->except('delevered_card_img', 'return_card_img'));

            if ($request->filled('delevered_card_img')) {
                $imageData = $request->input('delevered_card_img');
            
                if (Str::contains($imageData, 'base64,')) {
                    $imageData = explode('base64,', $imageData)[1];
                }
            
                $decodedImage = base64_decode($imageData);
            
                if ($decodedImage) {
                    $filename = Str::random(10) . '.jpg';
                    Storage::put("public/OrderCard/$filename", $decodedImage);
                    $order->delevered_card_img = $filename;
                }
            }

            if ($request->filled('return_card_img')) {
                $imageDataCard = $request->input('return_card_img');
            
                if (Str::contains($imageDataCard, 'base64,')) {
                    $imageDataCard = explode('base64,', $imageDataCard)[1];
                }
            
                $decodedImageCard = base64_decode($imageDataCard);
            
                if ($decodedImageCard) {
                    $filename = Str::random(10) . '.jpg';
                    Storage::put("public/OrderCard/$filename", $decodedImageCard);
                    $order->return_card_img = $filename;
                }
            }
    
            $order->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
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
