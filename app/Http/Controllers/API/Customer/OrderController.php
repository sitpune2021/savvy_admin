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

        // Base query with eager loading
        $ordersQuery = Orders::with(['drivers:id,name,phone_no', 'contract.product'])
            ->where('shipping_id', $shippingId);

        // If status is passed, return filtered order history
        if ($status) {
            $orderHistory = (clone $ordersQuery)->where('status', $status)->get();

            $formattedOrders = $orderHistory->map(function ($order) {
                return [
                    'id' => $order->id,
                    'delivered_qty' => $order->develivered_qty, // Fixed typo from 'develivered_qty'
                    'return_qty' => $order->return_qty,
                    'driver_name' => optional($order->drivers)->name,
                    'driver_phone_no' => optional($order->drivers)->phone_no,
                    'status' => $order->status,
                    'product_name' => optional($order->contract->product)->name,
                    'created_at' => $order->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Order history retrieved successfully.',
                'data' => $formattedOrders,
            ]);
        }

        // Today's order
        $todayOrder = (clone $ordersQuery)->whereDate('created_at', $today)->first();

        if (!$todayOrder) {
            $contract = $user->contract ?? Contracts::find($user->contract_id);
            if (!$contract) {
                return response()->json([
                    'status' => false,
                    'message' => 'No contract found for the user.',
                ], 404);
            }

            $latestOrder = (clone $ordersQuery)
                ->where('contract_id', $contract->id)
                ->latest('created_at')
                ->first();

            $lastOrderDate = $latestOrder ? Carbon::parse($latestOrder->created_at) : $today;
            $nextOrderDate = $this->getNextOrderDate($contract, $lastOrderDate);

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
                ],
            ]);
        }

        // Today's order exists
        $orderData = [
            'id' => $todayOrder->id,
            'delivered_qty' => $todayOrder->develivered_qty,
            'return_qty' => $todayOrder->return_qty,
            'driver_name' => optional($todayOrder->drivers)->name,
            'driver_phone_no' => optional($todayOrder->drivers)->phone_no,
            'status' => $todayOrder->status,
            'created_at' => $todayOrder->created_at->toDateTimeString(),
        ];

        return response()->json([
            'status' => true,
            'message' => "Today's order retrieved successfully.",
            'data' => [
                'ongoing_order' => $orderData,
            ]
        ]);
    }

    private function getNextOrderDate($contract, $lastOrderDate)
    {
        $today = Carbon::today();

        return match ($contract->frequency) {
            'daily' => $today->copy()->addDay(),
            'alternate_day' => $today->copy()->addDays(2),
            'weekly' => $this->getNextWeeklyDate($contract->days, $lastOrderDate),
            default => null,
        };
    }

    private function getNextWeeklyDate($days, $fromDate)
    {
        $contractDays = array_map('trim', explode('|', strtolower($days)));
        $nextDate = null;

        foreach ($contractDays as $day) {
            $potentialDate = (clone $fromDate)->next($day);
            if (!$nextDate || $potentialDate->lt($nextDate)) {
                $nextDate = $potentialDate;
            }
        }

        return $nextDate;
    }

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

    public function products()
    {
        $products = Product::all();
        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }

    public function requestOrderList()
    {
        $user = auth()->user();
        $orders = Orders::where('shipping_id', $user->id)->where('status', 'in-progress')->get();
            return response()->json([
                'status' => true,
                'message' => 'Order list retrieved successfully',
                'data' => $orders
            ], 200);
    }

    public function requestOrderUpdate($id)
    {
        $user = auth()->user();
        $order = Orders::findOrFail($id);
        $order->status = 'completed';
        $order->save();
        return response()->json([
            'status' => true,
            'message' => 'Order accepted successfully',
            'data' => $order // Use $order, not $orders
        ], 200);
    }



}
