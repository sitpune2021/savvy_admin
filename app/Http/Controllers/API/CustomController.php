<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Plant;
use App\Models\Routes;
use App\Models\Drivers;
use App\Models\ShippingAddress;


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
}
