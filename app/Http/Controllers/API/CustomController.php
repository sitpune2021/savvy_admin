<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Models\ShippingAddress;
use App\Models\Contracts;
use App\Models\Drivers;
use App\Models\Orders;
use App\Models\Routes;
use App\Models\Plant;
use App\Models\Reasons;
use App\Models\DigitalCard;
use App\Models\rawDistributions;
use App\Models\RawStockForPlant;
use App\Models\RawStockTransactions;
use App\Models\RawMaterialVariants;
use App\Models\RawStockLogs;


use Carbon\Carbon;
use Exception;


class CustomController extends BaseController
{
    public function plants()
    {
        $query = Plant::orderBy('created_at', 'desc');
            if ($this->vendorId !== null) {
                $query->where('vendor_id', $this->vendorId);
            }
            $plants = $query->select('id', 'name')->get();    
        return response()->json([
            'status' => true,
            'data' => $plants
        ], 200);
    }

    public function getRoutesByPlant($plantId)
    {
        $query = Routes::where('plant_id', $plantId)->whereHas('drivers');
        if ($this->vendorId !== null) {
            $query->where('vendor_id', $this->vendorId);
        }
        $routes = $query->select('id', 'name', 'path')->get();
        if (!$routes) {
            return response()->json([
                'status' => false,
                'message' => 'Routes not found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Routes retrieved successfully',
            'data' => $routes
        ], 200);

    }

    public function getDriversByRoute($routeId)
    {
        $query = Drivers::where('route_id', $routeId);
        if ($this->vendorId !== null) {
            $query->where('vendor_id', $this->vendorId);
        }
        $drivers = $query->select('id', 'name')->get();
        if (!$drivers) {
            return response()->json([
                'status' => false,
                'message' => 'Drivers not found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Drivers retrieved successfully',
            'data' => $drivers
        ], 200);
    }

    public function getShipingAddress(Request $request)
    {
        $perPage = $request->query('per_page', 25);
        $page = $request->query('page', 1);
        $type = $request->type;
        $query = ShippingAddress::with(['Customers:id,name', 'Contract']);
        if ($this->plantManagerId) {
            $query->where('plant_id', auth()->user()->plantManager->id);

        } else {
            if ($this->vendorId !== null) {
                $query->where('type', 'pan_india')->where('vendor_id', $this->vendorId);
            }

            if ($type == 'assigned') { 
                $query->whereNotNull('plant_id')
                    ->whereNotNull('route_id')
                    ->whereNotNull('driver_id');
            } elseif ($type == 'unassigned') {
                $query->whereNull('plant_id')
                    ->whereNull('route_id')
                    ->whereNull('driver_id');
            }
        }

        $shippingAddresses = $query->orderBy('created_at', 'desc')->get();
        
        if ($shippingAddresses->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Shipping address not found'
            ], 404);
        }
            $pagination = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page)->toArray();
            unset($pagination['data']);
        return response()->json([
            'status' => true,
            'message' => 'Shipping addresses retrieved successfully',
            'data' => $shippingAddresses,
            'pagination' => $pagination
        ], 200);
    }

    public function updateShippingAddressForVendor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'plant_id' => 'required|exists:plants,id',
            'route_id' => 'required|exists:routes,id',
            'driver_id' => 'required|exists:drivers,id',
        ], [
            'plant_id.required' => 'The plant ID is required.',
            'plant_id.exists' => 'The selected plant ID is invalid.',
            'route_id.required' => 'The route ID is required.',
            'route_id.exists' => 'The selected route ID is invalid.',
            'driver_id.required' => 'The driver ID is required.',
            'driver_id.exists' => 'The selected driver ID is invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $address = ShippingAddress::findOrFail($id);

            // Prepare the shipping data for update
            $shippingData = [
                'plant_id' => $request->plant_id,
                'route_id' => $request->route_id,
                'driver_id' => $request->driver_id,
            ];

            $address->update($shippingData);

            $contract = Contracts::findOrFail($address->contract_id);
            $todayDay = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
            $contractDays = explode('|', strtolower($contract->days ?? ''));
            
            $existingOrder = Orders::where('customer_id', $address->customer_id)
                ->where('contract_id', $contract->id)
                ->where('shipping_id', $address->id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            $orders = [];

            if ($contract->frequency === 'daily') {
                if ($existingOrder && $existingOrder->status === 'pending') {
                    $existingOrder->update([
                        'develivered_qty' => $contract->quantity,
                        'driver_id' => $address->driver_id,
                        'route_id' => $address->route_id,
                    ]);
                    $orders[] = $existingOrder;
                } else {
                    $order = Orders::create([
                        'customer_id' => $address->customer_id,
                        'contract_id' => $contract->id,
                        'driver_id' => $address->driver_id,
                        'shipping_id' => $address->id,
                        'route_id' => $address->route_id,
                        'status' => 'pending',
                    ]);
                    $orders[] = $order;
                }
            }

            if ($contract->frequency === 'weekly') {
                if (in_array($todayDay, $contractDays)) {
                    if ($existingOrder && $existingOrder->status === 'pending') {
                        $existingOrder->update([
                            'develivered_qty' => $contract->quantity,
                            'driver_id' => $address->driver_id,
                            'route_id' => $address->route_id,
                        ]);
                        $orders[] = $existingOrder;
                    } else {
                        $order = Orders::create([
                            'customer_id' => $address->customer_id,
                            'contract_id' => $contract->id,
                            'driver_id' => $address->driver_id,
                            'shipping_id' => $address->id,
                            'route_id' => $address->route_id,
                            'status' => 'pending',
                        ]);
                        $orders[] = $order;
                    }
                } else {
                    if ($existingOrder && $existingOrder->status === 'pending') {
                        $existingOrder->delete();
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Shipping address updated successfully.',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update shipping address.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReasons()
    {
        $table = auth()->user()->getTable();
        $reasons = null;

        if ($table == 'drivers') {
            $reasons = Reasons::where('for', 'driver')->get();
        } elseif ($table == 'shipping_contacts') {
            $reasons = Reasons::where('for', 'client')->get();
        } else {
            $reasons = false;
        }

        if ($reasons === false || $reasons->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Reasons not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Reasons retrieved successfully',
            'data' => $reasons
        ], 200);

    }

    public function getDigitalCard(Request $request)
    {
        $user = auth()->user();

        // Get query parameters
        $shippingId = $request->query('shipping_id'); // for shipping login
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $shippingAddressId = null;

        // If shipping_id is provided, validate ownership
        if ($shippingId) {
            $shippingAddresses = $user->shippingContactMultiples
                ->pluck('shippingAddress')
                ->filter()
                ->values();

            $shippingAddress = $shippingAddresses->firstWhere('id', $shippingId);
            if (!$shippingAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shipping address not found',
                    'data' => null,
                ], 404);
            }

            $shippingAddressId = $shippingAddress->id;
        }
        // Fetch digital cards with filters
        $digitalCards = DigitalCard::whereHas('order', function ($query) use ($shippingAddressId) {

            if ($this->driverId) {
                $query->where('driver_id', $this->driverId);
            }else{
                if ($shippingAddressId) {
                    $query->where('shipping_id', $shippingAddressId);
                }
            }

            
        })
        ->with('order', 'acceptBy:id,name', 'order.drivers:id,name', 'order.customers:id,name', 'order.shipping:id,shipping_address')
        ->paginate($perPage, ['*'], 'page', $page);
        // ->get();

        return response()->json([
            'status' => true,
            'message' => 'Digital cards retrieved successfully.',
            'data' => $digitalCards,
        ]);
    }

    public function getOrderRequest(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
            
        $contracts = Contracts::whereHas('shippingAddress', function ($addrQuery) {
            $addrQuery->where('plant_id', auth()->user()->plantManager->id);
        })
        ->with([
            'customer:id,name',
            'product:id,name',
            'sender:id,name',
            'shippingAddress:id,shipping_address'
        ])
        ->where('type', 'additional')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page); 

        $pagination = $contracts->toArray();
        $data = $pagination['data'];
        unset($pagination['data']);

        return response()->json([
            'status' => true,
            'message' => 'Order requests retrieved successfully.',
            'data' => $data,
            'pagination' => $pagination
        ]);
    }

    public function getNewStockList(Request $request){
        $status = $request->query('status');
        $distributions = rawDistributions::where('plant_id', auth()->user()->plantManager->id)->where('status', $status)
        ->with(['plant', 'transaction', 'transaction.variant', 'transaction.variant.rawMaterial'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'plant_id' => $item->plant_id,
                'plant_name' => optional($item->plant)->name ?? 'N/A',
                'variants_id' => $item->transaction->raw_material_variant_id,
                'varient_name' => optional($item->transaction->variant->rawMaterial)->name . ' - ' . optional($item->transaction->variant)->variant_name,
                'quantity' => $item->quantity,
                'status' => $item->status,
                'accepted_at' => $item->accepted_at,
                'deleted_at' => $item->deleted_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });


        return response()->json([
            'status' => true,
            'message' => 'Stock List retrieved successfully.',
            'data' => $distributions ,
            // 'pagination' => $pagination
        ]);
    }

    public function getRawStock()
    {
        $rawStock = RawStockForPlant::where('plant_id', auth()->user()->plantManager->id)
            ->with(['plant', 'rawMaterialVariant', 'rawMaterialVariant.rawMaterial'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return optional($item->rawMaterialVariant->rawMaterial)->name ?? 'N/A';
            })
            ->map(function ($items, $materialName) {
                return [
                    'material_name' => $materialName,
                    'variants' => $items->map(function ($item) {
                        return [
                            'variant_name' => optional($item->rawMaterialVariant)->variant_name,
                            'full_name' => optional($item->rawMaterialVariant->rawMaterial)->name . ' - ' . optional($item->rawMaterialVariant)->variant_name,
                            'quantity' => $item->total_quantity,
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json([
            'status' => true,
            'message' => 'Stock List retrieved successfully.',
            'data' => $rawStock,
        ]);
    }
    
    public function acceptStock($id)
    {
        $user = auth()->user();
        $plantId = $user->plantManager->id;

        $distribution = RawDistributions::findOrFail($id);

        // ✅ Ensure the distribution belongs to the same plant
        if ((int) $distribution->plant_id !== (int) $plantId) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to accept this distribution.',
            ], 403);
        }

        $transaction = RawStockTransactions::findOrFail((int) $distribution->raw_stock_transactions_id);
        $variant = RawMaterialVariants::findOrFail((int) $transaction->raw_material_variant_id);

        $plantStock = RawStockForPlant::where([
            'plant_id' => $plantId,
            'raw_material_variants_id' => $variant->id,
        ])->firstOrFail();
          // Mark distribution as accepted
        $distribution->status = 'accepted';
        $distribution->accepted_at = now();
        $distribution->save();


        // Update global variant stock
        $variant->decrement('total_quantity', $distribution->quantity);
        $variant->increment('remain_quantity', $distribution->quantity);
        $variant->save();

        // Update transaction with plant ID if not set
        $transaction->plant_id = $distribution->plant_id ?? $plantId;
        $transaction->save();

      

        // Update plant's stock
        $plantStock->increment('total_quantity', $distribution->quantity);
        $plantStock->save();

        // Log the stock acceptance
        RawStockLogs::create([
            'raw_material_id' => $variant->raw_material_id,
            'user_id' => $user->id,
            'plant_id' => $plantId,
            'action' => 'acceptance',
            'quantity' => $distribution->quantity,
            'note' => 'Accepted distribution to plant',
            'action_time' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Stock accepted successfully.',
            'data' => $distribution,
        ]);
    }
}
