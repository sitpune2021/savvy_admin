<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use App\Models\Contracts;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Authenticated user or shipping ID is missing.',
            ], 422);
        }
        $shippingId = $user->id;
        $status = $request->status;
        $today = Carbon::today();
    
        $ordersQuery = Orders::where('shipping_id', $shippingId)
            ->with(['drivers:id,name,phone_no']);

        if ($status) {
            $orderHistory = (clone $ordersQuery)->where('status', $status)->get();
    
            $formattedOrders = $orderHistory->map(function ($order) {
                return [
                    'id' => $order->id,
                    'delivered_qty' => $order->delivered_qty,
                    'return_qty' => $order->return_qty,
                    'driver_name' => optional($order->drivers)->name,
                    'driver_phone_no' => optional($order->drivers)->phone_no,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toDateTimeString(),
                ];
            });
    
            return response()->json([
                'status' => true,
                'message' => 'Order history retrieved successfully.',
                'data' => $formattedOrders
            ], 200);
        }
    
        $todayOrder = (clone $ordersQuery)->whereDate('created_at', $today)->first();
    
        if (!$todayOrder) {
            $contract = Contracts::findOrFail($user->contract_id);
        
            $latestOrder = (clone $ordersQuery)
                ->where('contract_id', $contract->id)
                ->latest('created_at')
                ->first();
        
            $lastOrderDate = $latestOrder ? Carbon::parse($latestOrder->created_at) : $today;
        
            $nextOrderDate = null;
        
            if ($contract->frequency == 'daily') {
                $nextOrderDate = $today->addDay();
            } elseif ($contract->frequency == 'alternate_day') {
                $nextOrderDate = $today->addDays(2);
            } elseif ($contract->frequency == 'weekly') {
                $contractDays = explode('|', strtolower($contract->days));
                $contractDays = array_map('trim', $contractDays);
        
                foreach ($contractDays as $day) {
                    $potentialDate = (clone $lastOrderDate)->next($day);
        
                    if (!$nextOrderDate || $potentialDate->lt($nextOrderDate)) {
                        $nextOrderDate = $potentialDate;
                    }
                }
            }
            if (!$nextOrderDate) {
                return response()->json([
                    'status' => false,
                    'message' => 'Could not determine the next order date.',
                ], 400);
            }
        
            return response()->json([
                'status' => true,
                'message' => 'No order found for today. Next scheduled order date retrieved.',
                'data' => [
                    'next_order_date' => $nextOrderDate->toDateString(),
                ]
            ], 200);
        }
    
        $orderData = [
            'id' => $todayOrder->id,
            'delivered_qty' => $todayOrder->delivered_qty,
            'return_qty' => $todayOrder->return_qty,
            'driver_name' => optional($todayOrder->drivers)->name,
            'driver_phone_no' => optional($todayOrder->drivers)->phone_no,
            'status' => $todayOrder->status,
            'created_at' => $todayOrder->created_at->toDateTimeString(),
        ];
    
        return response()->json([
            'status' => true,
            'message' => 'Today\'s order retrieved successfully.',
            'data' => $orderData
        ], 200);
        

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
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'date'=>'required|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = auth()->user();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function products(){
        $products = Product::all();
        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }
}
