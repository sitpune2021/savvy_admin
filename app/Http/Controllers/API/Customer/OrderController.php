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

        $shippingId = $user->shippingAddress->id;
        $status = $request->status;
        $today = Carbon::today();

        $ordersQuery = Orders::with(['drivers:id,name,phone_no', 'contract:id,quantity,product_id', 'contract.product'])
            ->where('shipping_id', $shippingId);

        if ($status) {
            $orderHistory = (clone $ordersQuery)->where('status', $status)->get();

            $formattedOrders = $orderHistory->map(function ($order) {
                return [
                    'id' => $order->id,
                    'delivered_qty' => $order->develivered_qty, // Fixed typo from 'develivered_qty'
                    'return_qty' => $order->return_qty,
                    'balance' => strval(optional($order->contract)->quantity),
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
            $contract = $user->shippingAddress->contract ?? Contracts::find($user->shippingAddress->contract_id);
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
            'balance' => strval(optional($todayOrder->contract)->quantity),
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
            'product'           => 'required|array|min:1',
            'product.*.product_id' => 'required|exists:products,id',
            'product.*.quantity'   => 'required|integer|min:1',
            'date'             => 'required|date_format:d-m-Y',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            $date = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
            $user = auth()->user();
    
            if (!$user || !$user->shippingAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'User or shipping address not found.'
                ], 404);
            }
    
            $shipping = ShippingAddress::find($user->shippingAddress->id);
            $activeContract = $shipping->Contract()
                ->where('type', 'contracts')
                ->where('status', 'active')
                ->first();
    
            foreach ($request->product as $productData) {
                Contracts::create([
                    'type'             => 'additional',
                    'customer_id'      => $user->shippingAddress->customer_id,
                    'product_id'       => $productData['product_id'],
                    'quantity'         => $productData['quantity'],
                    'price'            => $activeContract ? $activeContract->price : 0,
                    'duration'         => 1,
                    'duration_type'    => 'days',
                    'frequency'        => null,
                    'frequency_count'  => null,
                    'days'             => null,
                    'status'           => 'active',
                    'date'             => $date,
                    'send_by'          => $user->id,
                    'accepted_status'  => 'pending',
                ]);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Order request sent successfully',
            ], 201);
    
        } catch (\Exception $e) {
            Log::error('Contract creation failed: ' . $e->getMessage());
    
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function products()
    {
        $products = Product::all();

        $products->transform(function($product) {
            $product->image = url('storage/product/' . $product->image);
            return $product;
        });

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);

    }

    public function requestOrderList()
    {
        $user = auth()->user();
        $orders = Orders::where('shipping_id', $user->shippingAddress->id)->where('status', 'in-progress')->get();
            return response()->json([
                'status' => true,
                'message' => 'Order list retrieved successfully',
                'data' => $orders
            ], 200);
    }

    public function requestOrderUpdate($id)
    {
        $order = Orders::findOrFail($id);
        $order->status = 'completed';
        $order->save();
        return response()->json([
            'status' => true,
            'message' => 'Order accepted successfully',
            'data' => $order // Use $order, not $orders
        ], 200);
    }

    public function getRequestedOrders()
    {
        $user = auth()->user();
        $orders = Contracts::where('send_by', $user->id)
                    ->whereHas('sender.shippingAddress', function ($query) {
                        $query->whereNotNull('plant_id')
                            ->whereNotNull('route_id')
                            ->whereNotNull('driver_id');
                    })
                    ->get();

        return response()->json([
            'status' => true,
            'message' => 'Requested orders retrieved successfully',
            'data' => $orders
        ], 200);
    }



}
