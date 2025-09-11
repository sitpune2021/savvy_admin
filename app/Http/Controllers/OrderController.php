<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\Orders;
use App\Models\Product;
use App\Models\Contracts;
use App\Models\Routes;
use App\Models\ShippingAddress;
use App\Models\JarTransportation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

use Exception;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $ordersQuery = Orders::whereHas('drivers')->with(['customers', 'drivers', 'shipping'])->orderBy('created_at', 'desc');
            if($this->plantManagerId) {
                $ordersQuery = $ordersQuery->forPlantManager($this->plantManagerId);
            }else{
                if ($this->vendorId !== null) {
                    $ordersQuery = $ordersQuery->forVendor($this->vendorId, false, false);
                }
            }
                    $user = auth()->user();
                    $serial = 0;

            return DataTables::of($ordersQuery)
                ->addColumn('order_id', function ($order) use (&$serial, $user) {
                    $serial++; // Manually increment row number
                    $icon1 = '';
                    $icon2 = '';

                    if ($user?->vendor?->id === null && $order->drivers?->vendor_id != null && $user?->plantManager?->id == null) {
                        $icon1 = '<i class="ri-user-shared-line"></i>';
                    }
                    if ($order->type == 'additional') {
                        $icon2 = '<i class="ri-shopping-cart-line"></i>';
                    }

                    return $serial . $icon1 . $icon2;
                })
                ->addColumn('plant', fn($order) => $order->shipping->Plant->name ?? '')
                ->addColumn('customer', fn($order) => $order->customers->name ?? '')
                ->addColumn('shipping_address', fn($order) => $order->shipping->shipping_address ?? '')
                ->addColumn('driver', fn($order) => $order->drivers->name ?? '')
                ->addColumn('status_label', function ($order) {
                      $statusClasses = [
                        'cancelled' => 'bg-danger-subtle text-danger',
                        'pending' => 'bg-warning-subtle text-warning',
                        'completed' => 'bg-success-subtle text-success',
                        'in_progress' => 'bg-info-subtle text-info',
                    ]; 
                    return '<span class="badge ' . ($statusClasses[$order->status] ?? 'bg-secondary') . '">' .
                        ucfirst(str_replace('_', ' ', $order->status)) .
                        '</span>';
                })
                ->addColumn('date', fn($order) => $order->created_at->format('d-m-Y'))
                ->addColumn('date_complete', fn($order) => $order->updated_at->format('d-m-Y'))
                ->addColumn('actions', function ($order) {
                    $Url = route('order.show', $order->id);
                    return view('components.orderActions', compact('order', 'Url'))->render();
                })
                ->filter(function ($query) {
                    if (request()->has('search') && $search = request('search')['value']) {
                        $query->where(function ($q) use ($search) {
                            $q->where('orders.id', 'like', "%{$search}%")
                            ->orWhere('orders.status', 'like', "%{$search}%")
                            ->orWhereDate('orders.created_at', 'like', "%{$search}%")
                            ->orWhereDate('orders.updated_at', 'like', "%{$search}%")
                            ->orWhereHas('customers', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('shipping', function ($sub) use ($search) {
                                $sub->where('shipping_address', 'like', "%{$search}%");
                            })
                            ->orWhereHas('drivers', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%{$search}%");
                            });
                        });
                    }
                })
                ->rawColumns(['order_id','status_label', 'actions'])
                ->make(true);
        }
        return view('pages.order.index');
    }

    public function create()
    {
        $show = false;
        $customers= Customers::whereHas('contracts')->whereHas('shippingAddresses')->with('contracts.product', 'shippingAddresses')->get();
        $contracts = Contracts::all();
        $shippingAddresses = ShippingAddress::all();
        return view('pages.order.add-edit',compact('show', 'customers' ,  'contracts', 'shippingAddresses'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'shipping_id' => 'required|array',
            'shipping_id.*' => Rule::exists('shipping_addresses', 'id')->where(function ($query) use ($request) {
                                    return $query->where('customer_id', $request->customer_id);
                                }),
            'develivered_qty' => 'nullable|integer|min:0',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            foreach ($request->shipping_id as $shippingId) {
            $shipping = ShippingAddress::where('id', $shippingId)->first();
                Orders::create([
                    'customer_id' => $request->customer_id,
                    'contract_id' => $shipping?->contract_id,
                    'shipping_id' => $shippingId,
                    'route_id' => $shipping?->route_id,
                    'driver_id' => $shipping?->driver_id,
                    'status' => 'pending',
                ]);
                $jar4 = JarTransportation::where('date',  Carbon::today())
                        ->where('driver_id', $shipping->driver_id)
                        ->where('plant_id', $shipping->plant_id)
                        ->first();

                if ($jar4) {
                    // If already exists, increment total_quantity
                    $jar4->total_quantity += $shipping->Contract->quantity;
                    $jar4->allocat_quantity += $shipping->Contract->quantity; // Consider fixing typo if not intentional
                    $jar4->save();

                } else {
                    $newJarAdd4 =  JarTransportation::create([
                        'plant_id' => $address->plant_id,
                        'driver_id' => $address->driver_id,
                        'date' =>  Carbon::today(),
                        'status' => 'dispatching',
                        'total_quantity' => $shipping->Contract->quantity,
                        'allocated_quantity' => 0,
                        'allocat_quantity' => $shipping->Contract->quantity, // Consider fixing typo if not intentional
                    ]);
                }
            }
            return response()->json([
                'message' => 'Order created successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $show = true;
        $Order = Orders::findOrFail($id);
        $customers= Customers::whereHas('contracts')->whereHas('shippingAddresses')->with('contracts.product', 'shippingAddresses')->get();
        $contracts = Contracts::all();
        $shippingAddresses = ShippingAddress::all();
        return view('pages.order.add-edit',compact('show', 'customers' , 'Order', 'contracts', 'shippingAddresses'));
    }

    public function edit(string $id)
    {
        try {
            $show = false;
            $Order = Orders::findOrFail($id);
            $customers= Customers::whereHas('contracts')->whereHas('shippingAddresses')->with('contracts.product', 'shippingAddresses')->get();
            $contracts = Contracts::all();
            $shippingAddresses = ShippingAddress::all();
            return view('pages.order.add-edit',compact('show', 'customers' , 'Order','contracts', 'shippingAddresses'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Orders not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Orders for editing: ' . $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $Orders = Orders::findOrFail($id);
            $Orders->delete();
            return response()->json([
                'message' => 'Order deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Order not found.',
                'message' => $e->getMessage(),
            ], 404); 
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the  Order.',
                'message' => $e->getMessage(),
            ], 500); 
        }
    }

    public function assignDriver(string $id)
    {
        try {
            $show = false;
            $assign = true;
            $Order = Orders::findOrFail($id);
            $customers= Customers::all();
            $drivers= Drivers::all();
            return view('pages.order.add-edit',compact('show', 'customers' , 'drivers', 'Order', 'assign'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Orders not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Orders for editing: ' . $e->getMessage()]);
        }
    }

    public function storeRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [   
        'order.*.shipping_id' => 'required|integer|exists:shipping_addresses,id',
        'order.*.route_id' => 'required|integer|exists:routes,id',
        ], [
            'order.*.shipping_id.required' => 'Shipping ID is required for each order.',
            'order.*.route_id.required' => 'Route ID is required for each order.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            foreach ($request->order as $orderData) {
                $order = Orders::findOrFail($orderData['id']);
                $driver_id = Drivers::where('route_id', $orderData['route_id'])->first();
                $order->update([
                    'shipping_id' => $orderData['shipping_id'],
                    'route_id' => $orderData['route_id'],
                    'driver_id' => $driver_id->id ?? null,
                ]);
            }
            return response()->json([
                'message' => 'Orders updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
