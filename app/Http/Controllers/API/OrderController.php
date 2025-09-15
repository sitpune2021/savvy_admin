<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeliveryChallanMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Orders;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 25);
        $page = $request->query('page', 1);
        $status = $request->status;
        $count = $request->count;
        $today = Carbon::today();
        $ordersQuery = Orders::with(['customers', 'drivers','shipping' ]);

        if ($this->plantManagerId) {
            $ordersQuery->forPlantManager($this->plantManagerId);
        } else {
            if ($this->vendorId !== null) {
                $ordersQuery->whereHas('drivers', function ($query) {
                    $query->where('vendor_id', $this->vendorId);
                });
            }
            if($this->driverId){
                $ordersQuery->where('driver_id', $this->driverId);
            }
        }

        if ($status) {
            if ($status !== 'all') {
                $ordersQuery->where('status', $status);
            }
            $orders = $ordersQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

            $transformedOrders = $orders->getCollection()->transform(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => optional($order->customers)->name,
                    'contract_id' => $order->contract_id,
                    'driver_id' => $order->driver_id,
                    'driver_name' =>optional($order->drivers)->name,
                    'shipping_id' => $order->shipping_id,
                    'shipping_address' => optional($order->shipping)->shipping_address,
                    'status' => $order->status,
                    'develivered_qty' => $order->develivered_qty,
                    'return_qty' => $order->return_qty,
                    'balance' => strval(optional($order?->contract)->quantity),
                    'delevered_card_img' => $order->delevered_card_img 
                    ? url('storage/OrderCard/' . $order->delevered_card_img) 
                    : null,
                    'return_card_img' => $order->return_card_img 
                    ? url('storage/OrderCard/' . $order->return_card_img) 
                    : null,
                    'deleted_at' => $order->deleted_at,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'shipping_contacts' => $order->shipping?->contacts?->map(function ($contact) {
                        return [
                            'name' => $contact?->shippingContact?->name,
                            'phone' => $contact?->shippingContact?->phone,
                        ];
                    })
                ];
            });
            $pagination = $orders->toArray();
            unset($pagination['data']);
            return response()->json([
                'status' => true,
                'message' =>$status.' Order retrieved successfully.',
                'data' => $transformedOrders,
                'pagination' => $pagination ,
            ]);
        }
        if ($count) {
            $todayQuery = (clone $ordersQuery)->whereDate('created_at', $today);
            $totalContractQuantityToday = $todayQuery->with('contract')->get()->sum(function ($order) {
                            return optional($order->contract)->quantity;
                        });
                        $statuses = ['pending', 'completed', 'in-progress', 'cancelled'];
            $data = [
                'all_orders' => $ordersQuery->count(),
                'todays_orders' => $todayQuery->count(),
            ];
    
            foreach ($statuses as $status) {
                $data["all_{$status}_orders"] = (clone $ordersQuery)->where('status', $status)->count();
                $data["todays_{$status}_orders"] = (clone $todayQuery)->where('status', $status)->count();
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Order statistics retrieved successfully.',
                'data' => $data
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Please provide either a count flag or a status value.',
        ], 400);
    }


    public function show(string $id)
    {
        $with = ['shipping.contacts']; // Always load shipping.contacts if any role is active

        if ($this->vendorId !== null) {
            $with = array_merge($with, ['drivers:id,name', 'customers:id,name']);
        }

        $orderQuery = Orders::with($with)->where('id', $id);

        if ($this->driverId) {
            $orderQuery->where('driver_id', $this->driverId);
        }

        if ($this->vendorId !== null) {
            $orderQuery->whereHas('drivers', function ($query) {
                $query->where('vendor_id', $this->vendorId);
            });
        }

        $order = $orderQuery->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $order->delevered_card_img = $order->delevered_card_img 
            ? url('storage/OrderCard/' . $order->delevered_card_img) 
            : null;

        $order->return_card_img = $order->return_card_img 
            ? url('storage/OrderCard/' . $order->return_card_img) 
            : null;

           

        if ($this->driverId) {
            return response()->json([
                'status' => true,
                'message' => 'Order retrieved successfully.',
                'data' => $order
            ], 200);

        }

        if ($this->vendorId !== null) {
             $transformedOrder = [
                'id' => $order->id,
                'customer_id' => $order->customer_id,
                'customer_name' => optional($order->customers)->name,
                'shipping_id' => $order->shipping_id,
                'shipping_address' => optional($order->shipping)->shipping_address,
                'contract_id' => $order->contract_id,
                'driver_id' => $order->driver_id,
                'driver_name' => optional($order->drivers)->name,
                'shipping_contacts' => $order?->shipping?->contacts?->map(function ($contact) {
                    return [
                        'name' => $contact?->shippingContact?->name,
                        'phone' => $contact?->shippingContact?->phone,
                    ];
                }),
                
                'status' => $order->status,
                'develivered_qty' => $order->develivered_qty,
                'return_qty' => $order->return_qty,
                'balance' => strval(optional($order->contract)->quantity),
                'delevered_card_img' => $order->delevered_card_img,
                'return_card_img' => $order->return_card_img,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Order retrieved successfully.',
                'data' => $transformedOrder
            ], 200);
        } 

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized access or invalid role.',
        ], 403);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'develivered_qty' => 'required|integer|min:0',
            'return_qty' => 'required|integer|min:0',
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
            $order->status = 'in-progress';
            $order->update($request->except('delevered_card_img', 'return_card_img', 'status', 'driver_id'));

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

            $data = [
                'order_id' => $order->id ,
                'challan_no'       => 'CH-' . $order->id,
                'date'             => now()->format('d-m-Y'),
                'customer_name'    => optional($order->customers)->name,
                'customer_address'    => optional($order->customers)->billing_address,
                'c_phone_no'    => optional($order->customers)->phone_no,
                'c_email'    => optional($order->customers)->email,
                'shipping_address' => optional($order->shipping)->shipping_address,
                'name' => optional($order->shipping)->contacts[0]?->shippingContact?->name ?? '',
                'phone' => optional($order->shipping)->contacts[0]?->shippingContact?->phone ?? '',
                'items'            => [
                    [
                        'develivered_qty' => $order->develivered_qty,
                        'product_name' => optional($order->shipping)->Contract?->product?->name,
                        'product_code' => optional($order->shipping)->Contract?->product?->code,
                        'return_qty' => $order->return_qty,
                        'balance' => strval(optional($order?->contract)->quantity),
                    ]
                ],
                'driver_name'      => $order->drivers->name,
                'in_progress_at'      => $order->in_progress_at,
            ];

            if (!empty($order->customers?->email)) {
                if ($order->delivered_qty > 0) {
                    $pdf = Pdf::loadView('pdf.delivery_challan', $data)->output();
                    Mail::to($order->customers->email)->send(new DeliveryChallanMail($data, $pdf));
                }
            } else {
                Log::channel('challan')->warning('Delivery challan not sent: Missing email for customer', [
                    'order_id'      => $order->id,
                    'customer_id'   => $order->customers?->id,
                    'customer_name' => $order->customers?->name,
                ]);
            }
    
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
}
