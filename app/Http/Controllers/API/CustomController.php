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
        $type = $request->type;

        $query = ShippingAddress::with(['Customers:id,name', 'Contract']) // Load only id and name from customers
            ->where('type', 'pan_india');

        if ($this->vendorId !== null) {
            $query->where('vendor_id', $this->vendorId);
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

        $shippingAddresses = $query->orderBy('created_at', 'desc')->get();

        if ($shippingAddresses->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Shipping address not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Shipping addresses retrieved successfully',
            'data' => $shippingAddresses
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

}
