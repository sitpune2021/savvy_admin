<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customers;
use App\Models\Contracts;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Orders;
use App\Models\ShippingAddress;
use App\Models\Drivers;
use App\Models\ShippingContact;
use App\Models\Routes;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Requests\StoreUpdateCustomerShippingRequest;


class CustomerController extends BaseController
{
    public function index()
    {
        $query = Customers::orderBy('created_at', 'desc');
        if ($this->vendorId !== null) {
            $query->whereHas('shippingAddresses', function($query) {
                $query->where('type', 'pan_india')
                    ->where('vendor_id', $this->vendorId);
            });
        }
        $customers = $query->get();
        return view('pages.customer.index', compact('customers'));
    }

    public function create()
    {
        $show = false;
        $query = Plant::orderBy('created_at', 'desc');
            if ($this->vendorId !== null) {
                $query->where('vendor_id', $this->vendorId);
            }
            else{
                $query->where('vendor_id', null);
            }
            $plants = $query->get();    
        $products = Product::latest()->get();
        $routes = Routes::with('plant')->whereHas('drivers')->get();
        $drivers = Drivers::with('routes')->get();
        $vendors = Vendor::with('user:id,name')->get();
        return view('pages.customer.add-edit',compact('show', 'plants', 'products', 'routes', 'drivers',  'vendors'));;
    }

    public function store(StoreCustomerRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
    
            // Save customer
            $customerData = $request->only([
                'customer_zohi_id', 'plant_id', 'name', 'email', 'phone_no',
                'billing_address', 'billing_country', 'billing_state', 'billing_city', 'billing_pincode'
            ]);
    
            $customer = Customers::create($customerData);
            $orders = [];
    
            foreach ($request->shipping as $key => $shippingData) {
                // Save contract
                $contractData = $request->contract[$key];
                $contract = Contracts::create([
                    'customer_id'       => $customer->id,
                    'product_id'        => $contractData['product_id'],
                    'quantity'          => $contractData['quantity'],
                    'price'             => $contractData['price'],
                    'duration'          => $contractData['duration'],
                    'duration_type'     => $contractData['duration_type'],
                    'frequency'         => $contractData['frequency'],
                    'frequency_count'   => $contractData['frequency_count'],
                    'days'              => is_array($contractData['days'] ?? null)
                                            ? implode('|', $contractData['days'])
                                            : null,
                    'status'            => 'active',
                ]);
    
                $shippingData['customer_id'] = $customer->id;
                $shippingData['contract_id'] = $contract->id;
                $shipping = ShippingAddress::create($shippingData);
    
                // Save shipping contacts
                foreach ($shippingData['shipping_contacts'] as $contact) {
                    ShippingContact::create([
                        'shipping_id' => $shipping->id,
                        'name'        => $contact['name'],
                        'phone'       => $contact['phone'],
                    ]);
                }
    
                // Auto-create order if applicable
                if ($shipping->type === 'local') {
                    $shouldCreateOrder = false;
    
                    if (in_array($contract->frequency, ['daily', 'alternate_day'])) {
                        $shouldCreateOrder = true;
                    } elseif ($contract->frequency === 'weekly') {
                        $today = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
                        $contractDays = explode('|', strtolower($contract->days ?? ''));
                        if (in_array($today, $contractDays)) {
                            $shouldCreateOrder = true;
                        }
                    }
    
                    if ($shouldCreateOrder) {
                        $order = Orders::create([
                            'customer_id' => $customer->id,
                            'contract_id' => $contract->id,
                            'driver_id'   => $shipping->driver_id,
                            'shipping_id' => $shipping->id,
                            'route_id'    => $shipping->route_id,
                            'status'      => 'pending',
                        ]);
                        $orders[] = $order;
                    }
                }
            }
    
            DB::commit();
            return response()->json([
                'message' => 'Customer created successfully!',
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function show(string $id)
    {
        $show = true;
        $Customer = Customers::with([
                        'contracts',
                        'shippingAddresses' => function ($query) {
                            if ($this->vendorId !== null) {
                                $query->where('type', 'pan_india')
                                    ->where('vendor_id', $this->vendorId);
                            }
                        }
                    ])->findOrFail($id);
        $query = Plant::orderBy('created_at', 'desc');
            if ($this->vendorId !== null) {
                $query->where('vendor_id', $this->vendorId);
            }
            else{
                $query->where('vendor_id', null);
            }
        $plants = $query->get();    
        $products = Product::all();
        $routes = Routes::with('plant')->whereHas('drivers')->get();
        $drivers = Drivers::with('routes')->get();
        $vendors = Vendor::with('user:id,name')->get();
        return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products', 'routes', 'drivers', 'vendors'));
    }

    public function edit(string $id)
    {
        try {
            $show = false;
            $Customer = Customers::with([
                        'contracts',
                        'shippingAddresses' => function ($query) {
                            if ($this->vendorId !== null) {
                                $query->where('type', 'pan_india')
                                    ->where('vendor_id', $this->vendorId);
                            }
                        }
                    ])->findOrFail($id);
            $query = Plant::orderBy('created_at', 'desc');
            if ($this->vendorId !== null) {
                $query->where('vendor_id', $this->vendorId);
            }
            else{
                $query->where('vendor_id', null);
            }

            $plants = $query->get();    
            $products = Product::all();
            $routes = Routes::with('plant')->whereHas('drivers')->get();
            $drivers = Drivers::with('routes')->get();
            $vendors = Vendor::with('user:id,name')->get();
            return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products', 'routes', 'drivers', 'vendors'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Customer not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Customer for editing: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateCustomerRequest $request, string $id)
    {
        try {
            $data = $request->validated(); // safe to use — already validated
    
            $customer = Customers::findOrFail($id);
            $customer->update($data);
    
            $shippingAddress = ShippingAddress::where('customer_id', $id)->get();
    
            if ($shippingAddress->isEmpty()) {
                return response()->json([
                    'error' => 'No shipping address found for this customer, please add a shipping address.',
                ], 404);
            }
    
            return response()->json([
                'message' => 'Customer updated successfully!',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $orderspending = Orders::where('customer_id', $id)->where('status', 'pending')->get();
            foreach ($orderspending as $orderP) {
                $orderP->forceDelete();  
            }
        
            $orderscompleted = Orders::where('customer_id', $id)->where('status', 'completed')->get();
            foreach ($orderscompleted as $orderC) {
                $orderC->delete(); 
            }
        
            $shippingAddress = ShippingAddress::where('customer_id', $id)->get();
            foreach ($shippingAddress as $address) {
                $address->forceDelete();  
            }
        
            $contracts = Contracts::where('customer_id', $id)->get();
            foreach ($contracts as $contract) {
                $contract->forceDelete();  
            }
        
            $customer = Customers::findOrFail($id);
            $customer->forceDelete(); 
            return back()->with('success', 'Customer deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Customer not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Customer for editing: ' . $e->getMessage()]);
        }
        
    }

    public function storeUpdateShippingAddress(StoreUpdateCustomerShippingRequest $request, $id)
    {
        $validator = $request->validated();
        try {
            DB::beginTransaction(); // Make sure the transaction is started
            $customer = Customers::findOrFail($id);
            $orders = [];
        
            foreach ($request->shipping as $key => $shippingData) {
                $contractData = $request->contract[$key] ?? [];
                // 1. Handle Contract
                if (!empty($contractData['id'])) {
                    $contract = Contracts::findOrFail($contractData['id']);
                    $contract->update([
                        'product_id'      => $contractData['product_id'],
                        'quantity'        => $contractData['quantity'],
                        'price'           => $contractData['price'],
                        'duration'        => $contractData['duration'],
                        'duration_type'   => $contractData['duration_type'],
                        'frequency'       => $contractData['frequency'],
                        'frequency_count' => $contractData['frequency_count'],
                        'days'            => is_array($contractData['days'] ?? null)
                            ? implode('|', $contractData['days'])
                            : null,
                    ]);
                } else {
                    $contract = Contracts::create([
                        'customer_id'     => $customer->id,
                        'product_id'      => $contractData['product_id'],
                        'quantity'        => $contractData['quantity'],
                        'price'           => $contractData['price'],
                        'duration'        => $contractData['duration'],
                        'duration_type'   => $contractData['duration_type'],
                        'frequency'       => $contractData['frequency'],
                        'frequency_count' => $contractData['frequency_count'],
                        'days'            => is_array($contractData['days'] ?? null)
                            ? implode('|', $contractData['days'])
                            : null,
                        'status' => 'active',
                    ]);
                }
        
                // 2. Handle Shipping Address
                $shippingData['customer_id'] = $customer->id;
                $shippingData['contract_id'] = $contract->id;

                if (!empty($shippingData['id'])) {
                    $address = ShippingAddress::findOrFail($shippingData['id']);
                    if($shippingData['vendor_id'] !=  $address->vendor_id){
                        $shippingData['plant_id'] = null;
                        $shippingData['route_id'] = null;
                        $shippingData['driver_id'] = null;
                    }
                    $address->update($shippingData);
                } else {
                    $address = ShippingAddress::create($shippingData);
                }

                if (isset($shippingData['shipping_contacts']) && is_array($shippingData['shipping_contacts'])) {
                    $existingContacts = ShippingContact::where('shipping_id', $address->id)->get();
                    $existingIds = $existingContacts->pluck('id')->toArray();
                    $receivedIds = []; // Will collect IDs from the incoming data
                    foreach ($shippingData['shipping_contacts'] as $contact) {
                        if (!empty($contact['id'])) {
                            $receivedIds[] = $contact['id']; // Track received ID
                            $contactModel = ShippingContact::findOrFail($contact['id']);
                            $contactModel->update([
                                'name' => $contact['name'],
                                'phone' => $contact['phone'],
                            ]);
                        } else {
                            $newContact = ShippingContact::create([
                                'shipping_id' => $address->id,
                                'name' => $contact['name'],
                                'phone' => $contact['phone'],
                            ]);
                            $receivedIds[] = $newContact->id; // Track new ID too
                        }
                    }
                
                    $contactsToDelete = array_diff($existingIds, $receivedIds);
                    if (!empty($contactsToDelete)) {
                        ShippingContact::whereIn('id', $contactsToDelete)->delete();
                    }
                }
                

        
                $todayDay = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
                $contractDays = explode('|', strtolower($contract->days ?? ''));
                $existingOrder = Orders::where('customer_id', $customer->id)
                        ->where('contract_id', $contract->id)
                        ->where('shipping_id', $address->id)
                        ->whereDate('created_at', Carbon::today())
                        ->first();
                if (in_array($todayDay, $contractDays)) {
                    if ($existingOrder && $existingOrder->status === 'complete') {
                        continue;
                    }
        
                    if ($existingOrder && $existingOrder->status === 'pending') {
                        $existingOrder->update([
                            'develivered_qty' => $contract->quantity, // ✅ corrected spelling
                            'driver_id'     => $address->driver_id,
                            'route_id'      => $address->route_id,
                        ]);
                        $orders[] = $existingOrder;
                    } else {
                        $order = Orders::create([
                            'customer_id'    => $customer->id,
                            'contract_id'    => $contract->id,
                            'driver_id'      => $address->driver_id,
                            'shipping_id'    => $address->id,
                            'route_id'       => $address->route_id,
                            'status'         => 'pending',
                        ]);
                        $orders[] = $order;
                    }
                }
            }
        
            DB::commit();
        
            return response()->json([
                'message' => 'Shipping addresses updated successfully!',
            ]);
        
        }
         catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    public function updateShippingAddressForVendor(Request $request){
        $validator = Validator::make($request->all(), [
            'shipping.*.plant_id' => 'required|exists:plants,id',
            'shipping.*.route_id' => 'required|exists:routes,id',
            'shipping.*.driver_id' => 'required|exists:drivers,id',
            ],
            [
                'shipping.*.plant_id.required' => 'The plant ID is required.',
                'shipping.*.plant_id.exists' => 'The selected plant ID is invalid.',
                'shipping.*.route_id.required' => 'The route ID is required.',
                'shipping.*.route_id.exists' => 'The selected route ID is invalid.',
                'shipping.*.driver_id.required' => 'The driver ID is required.',
                'shipping.*.driver_id.exists' => 'The selected driver ID is invalid.',
            ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            foreach ($request->shipping as $key => $shippingData) {
                if (!empty($shippingData['id'])) {
                    $address = ShippingAddress::findOrFail($shippingData['id']);
                    $address->update($shippingData);
                    
                    $contract = Contracts::findOrFail($address->contract_id);

                    $todayDay = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
                    $contractDays = explode('|', strtolower($contract->days ?? ''));
                    $existingOrder = Orders::where('customer_id', $address->customer_id)
                            ->where('contract_id', $contract->id)
                            ->where('shipping_id', $address->id)
                            ->whereDate('created_at', Carbon::today())
                            ->first();
                        if($contract->frequency == 'daily')
                        {
                            if ($existingOrder && $existingOrder->status === 'complete') {
                                continue;
                            }
                
                            if ($existingOrder && $existingOrder->status === 'pending') {
                                $existingOrder->update([
                                    'develivered_qty' => $contract->quantity, // ✅ corrected spelling
                                    'driver_id'     => $address->driver_id,
                                    'route_id'      => $address->route_id,
                                ]);
                                $orders[] = $existingOrder;
                            } else {
                                $order = Orders::create([
                                    'customer_id'    => $address->customer_id,
                                    'contract_id'    => $contract->id,
                                    'driver_id'      => $address->driver_id,
                                    'shipping_id'    => $address->id,
                                    'route_id'       => $address->route_id,
                                    'status'         => 'pending',
                                ]);
                                $orders[] = $order;
                            }
                        }
                        if($contract->frequency == 'weekly'){
                            if (in_array($todayDay, $contractDays)) {
                                if ($existingOrder && $existingOrder->status === 'complete') {
                                    continue;
                                }
                    
                                if ($existingOrder && $existingOrder->status === 'pending') {
                                    $existingOrder->update([
                                        'develivered_qty' => $contract->quantity, // ✅ corrected spelling
                                        'driver_id'     => $address->driver_id,
                                        'route_id'      => $address->route_id,
                                    ]);
                                    $orders[] = $existingOrder;
                                } else {
                                    $order = Orders::create([
                                        'customer_id'    => $address->customer_id,
                                        'contract_id'    => $contract->id,
                                        'driver_id'      => $address->driver_id,
                                        'shipping_id'    => $address->id,
                                        'route_id'       => $address->route_id,
                                        'status'         => 'pending',
                                    ]);
                                    $orders[] = $order;
                                }
                            }
                            else{
                                dd($existingOrder);
                                if ($existingOrder && $existingOrder->status === 'pending') {
                                    $existingOrder->delete();
                                }
                            }
                        }
                }

            }
            return response()->json(['message' => 'Shipping address updated successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update shipping address: ' . $e->getMessage()], 500);
        }
    }
}
