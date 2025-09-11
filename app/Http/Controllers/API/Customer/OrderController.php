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
use App\Models\DigitalCard;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shippingAddresses = $user->shippingContactMultiples
            ->pluck('shippingAddress')
            ->filter()
            ->values();

        $shippingIds = $shippingAddresses->pluck('id')->toArray();
        $status = $request->status;
        $today = Carbon::today();

        $ordersQuery = Orders::with([
            'drivers:id,name,phone_no',
            'contract:id,quantity,product_id',
            'contract.product',
            'shipping'
        ])->whereIn('shipping_id', $shippingIds);

        // Handle filtering by status
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
                    'shipping_id' => $order->shipping_id,
                    'shipping_address' => optional($order->shipping)->shipping_address,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Order history retrieved successfully.',
                'data' => $formattedOrders,
            ]);
        }

        // Fetch today's orders
        $todayOrders = (clone $ordersQuery)
            ->whereDate('created_at', $today)
            ->get();

        $shippingOrders = [];

        foreach ($shippingAddresses as $address) {
            $entry = [
                'shipping_id' => $address->id,
                'shipping_address' => $address->shipping_address,
            ];

            // Check if today's order exists for this address
            $order = $todayOrders->firstWhere('shipping_id', $address->id);

            if ($order) {
                $entry['ongoing_order'] = [
                    'id' => $order->id,
                    'delivered_qty' => $order->develivered_qty,
                    'return_qty' => $order->return_qty,
                    'balance' => strval(optional($order->contract)->quantity),
                    'driver_name' => optional($order->drivers)->name,
                    'driver_phone_no' => optional($order->drivers)->phone_no,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toDateTimeString(),
                ];
            } else {
                $contract = $address->contract ?? Contracts::find($address->contract_id);
                if ($contract) {
                    $latestOrder = Orders::where('shipping_id', $address->id)
                        ->where('contract_id', $contract->id)
                        ->latest('created_at')
                        ->first();

                    $lastOrderDate = $latestOrder ? Carbon::parse($latestOrder->created_at) : $today;
                    $nextOrderDate = $this->getNextOrderDate($contract, $lastOrderDate);

                    if ($nextOrderDate) {
                        $entry['next_order_date'] = $nextOrderDate->toDateString();
                    }
                }
            }

            $shippingOrders[] = $entry;
        }

        return response()->json([
            'status' => true,
            'message' => "Order data retrieved successfully.",
            'data' => [
                'shipping_orders' => $shippingOrders
            ],
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

    public function store(Request $request, $id)
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
            $shippingAddresses = $user->shippingContactMultiples
            ->pluck('shippingAddress')
            ->filter()
            ->values();
            $exists = $shippingAddresses->pluck('id')->contains($id);
            $shippingAddress = $shippingAddresses->firstWhere('id', $id);

            if (!$user || !$exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'User or shipping address not found.'
                ], 404);
            }
    
            $shipping = ShippingAddress::find($id);
            $activeContract = $shipping->Contract()
                ->where('type', 'contracts')
                ->where('status', 'active')
                ->first();
    
            foreach ($request->product as $productData) {
                Contracts::create([
                    'type'             => 'additional',
                    'customer_id'      => $shippingAddress->customer_id,
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
                    'shipping_addresses_id' => $id,
                    'accepted_status'  => 'pending',
                ]);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Order request sent successfully',
            ], 201);
    
        } catch (\Exception $e) {
    
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

    public function getAdditionalOrders()
    {
        $user = auth()->user();
        $orders = Contracts::with(['product:id,name,image', 'sender:id,name', 'shippingAddress:id,shipping_address'])->where('send_by', $user->id)->where('type', 'additional')
                    ->whereHas('sender.shippingContactMultiples.shippingAddress', function ($query) {
                        $query->whereNotNull('plant_id')
                            ->whereNotNull('route_id')
                            ->whereNotNull('driver_id');
                    })
                    ->get()->map(function ($order) {
                $data = $order->toArray();
                unset($data['product'], $data['sender'], $data['shipping_address']);
                $result = [];
                foreach ($data as $key => $value) {
                    $result[$key] = $value;
                    if ($key === 'product_id') {
                        $result['product_name'] = $order->product->name ?? null;
                        $result['product_image'] = $order->product->image ?  url('storage/product/'.$order->product->image) : null;

                    }
                    if ($key === 'send_by') {
                        $result['send_by_name'] = $order->sender->name ?? null;
                    }
                    if ($key === 'shipping_addresses_id') {
                        $result['shipping_addresses'] = $order->shippingAddress->shipping_address ?? null;
                    }
                }

                return $result;
            });

        return response()->json([
            'status' => true,
            'message' => 'Requested orders retrieved successfully',
            'data' => $orders
        ], 200);
    }

    public function requestOrderList()
    {
        $user = auth()->user();

        if (!$user->shippingContactMultiples || $user->shippingContactMultiples->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No shipping contacts found.',
                'data' => [],
            ], 200);
        }

        $shippingIds = $user->shippingContactMultiples->pluck('shipping_id')->toArray();

        $orders = Orders::with(['drivers:id,name', 'shipping:id,shipping_address'])
            ->whereIn('shipping_id', $shippingIds)
            ->where('status', 'in-progress')
            ->get()
            ->map(function ($order) {
                $data = $order->toArray();
                unset($data['drivers'], $data['shipping']);
                $result = [];
                foreach ($data as $key => $value) {
                    $result[$key] = $value;
                    if ($key === 'shipping_id') {
                        $result['shipping_address'] = $order->shipping->shipping_address ?? null;
                    }
                    if ($key === 'driver_id') {
                        $result['driver_name'] = $order->drivers->name ?? null;
                    }
                }

                return $result;
            });




        return response()->json([
            'status' => true,
            'message' => 'Order list retrieved successfully.',
            'data' => $orders,
        ], 200);
    }

    
    public function manageOrders(Request $request, $type, $id)
    {
        if (!in_array($type, ['accept', 'cancel', 'additional-order'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid order action type.',
            ], 400);
        }
        if( $type === 'additional-order') {
            return $this->store($request, $id);
        }

        $order = Orders::find($id);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($type === 'accept') {
            $order->status = 'completed';
            $order->save();

            DigitalCard::create([
                'order_id'    => $order->id,
                'balance'     => optional($order->contract)->quantity,
                'accept_by'   => auth()->id(),
                'created_at'  => $order->in_progress_at,
                'updated_at'  => $order->updated_at,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Order accepted successfully.',
                'data' => $order,
            ], 200);
        }

        if ($type === 'cancel') {
            // $order->status = 'completed';
            // $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully.',
                'data' => $order,
            ], 200);
        }
    }


    public function shippingAddresses()
    {
        $user = auth()->user();
        $shippingAddresses = $user->shippingContactMultiples
            ->pluck('shippingAddress')
            ->filter()
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Shipping addresses retrieved successfully',
            'data' => $shippingAddresses
        ], 200);
    }

}
