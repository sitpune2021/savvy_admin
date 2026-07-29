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
use App\Models\ShippingContactsMultiple;
use App\Models\Routes;
use App\Models\Vendor;
use App\Models\JarTransportation;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Requests\StoreUpdateCustomerShippingRequest;


class CustomerController extends BaseController
{
    public function index(Request $request)
    {
        $contractScope = function ($query) {
            if ($this->plantManagerId) {
                $query->whereHas('shippingAddress', fn($shippingQuery) =>
                    $shippingQuery->where('plant_id', $this->plantManagerId)
                );
            } elseif ($this->vendorId !== null) {
                $query->whereHas('shippingAddress', fn($shippingQuery) =>
                    $shippingQuery->where('type', 'pan_india')
                        ->where('vendor_id', $this->vendorId)
                );
            }
        };

        $query = Customers::withCount([
            'contracts as active_contracts_count' => function ($query) use ($contractScope) {
                $query->where('type', 'contracts')->where('status', 'active');
                $contractScope($query);
            },
            'contracts as expired_contracts_count' => function ($query) use ($contractScope) {
                $query->where('type', 'contracts')->where('status', 'expired');
                $contractScope($query);
            },
        ])->orderBy('created_at', 'desc');
        if($this->plantManagerId){
            $query->whereHas('shippingAddresses', function($query) {
                $query->where('plant_id', $this->plantManagerId);

            });
        }else{
           if ($this->vendorId !== null) {
                $query->whereHas('shippingAddresses', function($query) {
                    $query->where('type', 'pan_india')
                        ->where('vendor_id', $this->vendorId);
                });
           }
        }

        $allCustomersCount = (clone $query)->count();

        $activeContractsQuery = Contracts::where('type', 'contracts')->where('status', 'active');
        $contractScope($activeContractsQuery);
        $activeContractsCount = $activeContractsQuery->count();

        $expiredContractsQuery = Contracts::where('type', 'contracts')->where('status', 'expired');
        $contractScope($expiredContractsQuery);
        $expiredContractsCount = $expiredContractsQuery->count();

        if (in_array($request->contract_status, ['active', 'expired'], true)) {
            $query->whereHas('contracts', function ($contractQuery) use ($request, $contractScope) {
                $contractQuery->where('type', 'contracts')
                    ->where('status', $request->contract_status);
                $contractScope($contractQuery);
            });
        }

        $customers = $query->get();


        return view('pages.customer.index', compact(
            'customers',
            'allCustomersCount',
            'activeContractsCount',
            'expiredContractsCount'
        ));
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
                $contract->shipping_addresses_id = $shipping->id;
                $contract->save();
    
                // Save shipping contacts
                foreach ($shippingData['shipping_contacts'] as $contact) {
                    $exit = $contact['exit'] ?? null;
                    if ($exit === 'on') {
                        $shippingContactId = $contact['id'];
                    } else {
                        $shippingContact = ShippingContact::create([
                            'customer_id' => $customer->id,
                            // 'shipping_id' => $shipping->id,
                            'name'        => $contact['name'],
                            'phone'       => $contact['phone'],
                        ]);

                        $shippingContactId = $shippingContact->id;
                    }
                    ShippingContactsMultiple::create([
                        'shipping_id'          => $shipping->id,
                        'shipping_contacts_id' => $shippingContactId,
                        'mode'                 => $exit === 'on' ? 'exit' : 'main' // fallback mode if needed
                    ]);
                }

    
                // Auto-create order if applicable
                if ($shipping->type === 'local') {
                    $shouldCreateOrder = false;

                    $todayDay = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
                    $todayDate = strval(Carbon::now()->day);            // e.g. '19' as string

                    $contractDaysRaw = strtolower($contract->days ?? '');
                    $contractDays = array_map('trim', explode('|', $contractDaysRaw));

                    if (in_array($contract->frequency, ['daily', 'alternate_day'])) {
                        $shouldCreateOrder = true;

                    } elseif ($contract->frequency === 'weekly') {
                        foreach ($contractDays as $day) {
                            if (in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])) {
                                if ($todayDay === $day) {
                                    $shouldCreateOrder = true;
                                }
                            }
                        }

                    } elseif ($contract->frequency === 'monthly') {
                        foreach ($contractDays as $day) {
                            if (ctype_digit($day) && $day === $todayDate) {
                                $shouldCreateOrder = true;
                            }
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
                        $jar = JarTransportation::where('date',  Carbon::today())
                        ->where('driver_id', $shipping->driver_id)
                        ->where('plant_id', $shipping->plant_id)
                        ->first();

                        if ($jar) {
                            // If already exists, increment total_quantity
                            $jar->total_quantity += $contract->quantity;
                            $jar->allocat_quantity += $contract->quantity;
                            $jar->save();

                        } else {
                        $newJarAdd =  JarTransportation::create([
                                'plant_id' => $shipping->plant_id,
                                'driver_id' => $shipping->driver_id,
                                'date' =>  Carbon::today(),
                                'status' => 'dispatching',
                                'total_quantity' => $contract->quantity,
                                'allocated_quantity' => 0,
                                'allocat_quantity' => $contract->quantity, // Consider fixing typo if not intentional
                            ]);

                        }
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
                            $query->with(['dispensary']);
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
        $contacts = ShippingContact::where('customer_id', $id)->get();
        
        return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products', 'routes', 'drivers', 'vendors', 'contacts'));
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
                           $query->with(['contacts.shippingContact']);
                           $query->withCount(['dispensary']);
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
            $contacts = ShippingContact::with('shippingContactMultiples')->where('customer_id', $id)->get();
            return view('pages.customer.add-edit',compact('show', 'Customer', 'plants', 'products', 'routes', 'drivers', 'vendors', 'contacts'));
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
                    $updatedDays = is_array($contractData['days'] ?? null)
                        ? implode('|', $contractData['days'])
                        : null;

                    if (!empty($contractData['reactivate'])) {
                        if ($contract->type !== 'contracts') {
                            throw ValidationException::withMessages([
                                "contract.$key.frequency" => 'Only regular contracts can be reactivated.',
                            ]);
                        }

                        $frequencyChanged =
                            $contract->frequency !== $contractData['frequency']
                            || (string) $contract->frequency_count !== (string) ($contractData['frequency_count'] ?? '')
                            || (string) $contract->days !== (string) $updatedDays;

                        if (!$frequencyChanged) {
                            throw ValidationException::withMessages([
                                "contract.$key.frequency" => 'Change the delivery frequency, frequency count, or delivery days before reactivating this contract.',
                            ]);
                        }
                    }

                    $contract->update([
                        'product_id'      => $contractData['product_id'],
                        'quantity'        => $contractData['quantity'],
                        'price'           => $contractData['price'],
                        'duration'        => $contractData['duration'],
                        'duration_type'   => $contractData['duration_type'],
                        'frequency'       => $contractData['frequency'],
                        'frequency_count' => $contractData['frequency_count'],
                        'days'            => $updatedDays,
                        'status'          => !empty($contractData['reactivate'])
                                                    ? 'active'
                                                    : $contract->status,
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
                        'status'          => 'active',
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


                $contract->update([
                    'shipping_addresses_id'   => $address->id,
                ]);

        
                
               if (isset($shippingData['shipping_contacts']) && is_array($shippingData['shipping_contacts'])) {
                    // Get existing contacts linked to this shipping address
                    $existingContacts = ShippingContactsMultiple::where('shipping_id', $address->id)
                        ->with(['shippingContact:id'])
                        ->get();

                    $existingContactIds = $existingContacts->pluck('shippingContact.id')->filter()->toArray();
                    $existingMultiIds = $existingContacts->pluck('id')->toArray();

                    $receivedContactIds = [];
                    $receivedMultiIds = [];

                    foreach ($shippingData['shipping_contacts'] as $contact) {

                        $mode = (isset($contact['exit']) && $contact['exit'] === 'on') ? 'exit' : 'main';

                        if (!empty($contact['id'])) {
                            $contactModel = ShippingContact::find($contact['id']);
                            if (!$contactModel) {
                                return response()->json([
                                    'error' => 'Contact not found.',
                                    'id' => $contact['id'],
                                ], 404);
                            }

                            $contactModel->update([
                                'name' => $contact['name'],
                                'phone' => $contact['phone'],
                            ]);
                        } else {
                            $contactModel = ShippingContact::create([
                                'customer_id' => $customer->id,
                                'name' => $contact['name'],
                                'phone' => $contact['phone'],
                            ]);
                        }

                        $receivedContactIds[] = $contactModel->id;

                        $multiple = ShippingContactsMultiple::updateOrCreate(
                            [
                                'shipping_id'           => $address->id,
                                'shipping_contacts_id'  => $contactModel->id,
                            ],
                            ['mode' => $mode]
                        );

                        $receivedMultiIds[] = $multiple->id;
                    }

                    $multiToDelete = array_diff($existingMultiIds, $receivedMultiIds);

                    if (!empty($multiToDelete)) {
                        ShippingContactsMultiple::whereIn('id', $multiToDelete)->delete();
                    }
                
                    $contactsToDelete = array_diff($existingContactIds, $receivedContactIds);

                    if (!empty($contactsToDelete)) {
                        $stillReferenced = ShippingContactsMultiple::whereIn(
                                'shipping_contacts_id',
                                $contactsToDelete
                            )
                            ->pluck('shipping_contacts_id')
                            ->unique()
                            ->toArray();

                        $safeToDelete = array_diff($contactsToDelete, $stillReferenced);

                        if (!empty($safeToDelete)) {
                            ShippingContact::whereIn('id', $safeToDelete)->delete();
                        }
                    }
                }

        
                $todayDay = strtolower(Carbon::now()->format('l')); // e.g. 'monday'
                $todayDate = strval(Carbon::now()->day);            // e.g. '19' as string

                $contractDaysRaw = strtolower($contract->days ?? '');
                $contractDays = array_map('trim', explode('|', $contractDaysRaw)); // Normalize entries

                $shouldCreateOrder = false;

                foreach ($contractDays as $day) {
                    // Check if the day is a weekday name
                    if (in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])) {
                        if ($todayDay === $day) {
                            $shouldCreateOrder = true;
                            break;
                        }
                    }
                    // Else assume it's a string date
                    elseif (ctype_digit($day) && $day === $todayDate) {
                        $shouldCreateOrder = true;
                        break;
                    }
                }

                if ($shouldCreateOrder) {
                    $existingOrder = Orders::where('customer_id', $customer->id)
                        ->where('contract_id', $contract->id)
                        ->where('shipping_id', $address->id)
                        ->whereDate('created_at', Carbon::today())
                        ->first();

                    if ($existingOrder && $existingOrder->status === 'complete') {
                        continue;
                    }

                    if ($existingOrder && $existingOrder->status === 'pending') {
                        $existingOrder->update([
                            'develivered_qty' => $contract->quantity,
                            'driver_id'       => $address->driver_id,
                            'route_id'        => $address->route_id,
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
                        $jar2 = JarTransportation::where('date',  Carbon::today())
                        ->where('driver_id', $address->driver_id)
                        ->where('plant_id', $address->plant_id)
                        ->first();

                        if ($jar2) {
                            // If already exists, increment total_quantity
                            $jar2->total_quantity += $contract->quantity;
                            $jar2->allocat_quantity += $contract->quantity;
                            $jar2->save();

                        } else {
                            $newJarAdd2 =  JarTransportation::create([
                                'plant_id' => $address->plant_id,
                                'driver_id' => $address->driver_id,
                                'date' =>  Carbon::today(),
                                'status' => 'dispatching',
                                'total_quantity' => $contract->quantity,
                                'allocated_quantity' => 0,
                                'allocat_quantity' => $contract->quantity, // Consider fixing typo if not intentional
                            ]);
                        }
                    }
                }

            }
        
            DB::commit();
        
            return response()->json([
                'message' => 'Shipping addresses updated successfully!',
            ]);
        
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    public function expireContract(Contracts $contract)
    {
        abort_unless($contract->type === 'contracts', 404);

        $contract->update(['status' => 'expired']);

        return back()->with('success', 'Contract marked as expired successfully.');
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
                                $jar3 = JarTransportation::where('date',  Carbon::today())
                                    ->where('driver_id', $address->driver_id)
                                    ->where('plant_id', $address->plant_id)
                                    ->first();

                                if ($jar3) {
                                    // If already exists, increment total_quantity
                                    $jar3->total_quantity += $contract->quantity;
                                    $jar3->allocat_quantity += $contract->quantity;
                                    $jar3->save();

                                } else {
                                    $newJarAdd3 =  JarTransportation::create([
                                        'plant_id' => $address->plant_id,
                                        'driver_id' => $address->driver_id,
                                        'date' =>  Carbon::today(),
                                        'status' => 'dispatching',
                                        'total_quantity' => $contract->quantity,
                                        'allocated_quantity' => 0,
                                        'allocat_quantity' => $contract->quantity, // Consider fixing typo if not intentional
                                    ]);
                                }
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
                                    $jar4 = JarTransportation::where('date',  Carbon::today())
                                        ->where('driver_id', $address->driver_id)
                                        ->where('plant_id', $address->plant_id)
                                        ->first();

                                    if ($jar4) {
                                        // If already exists, increment total_quantity
                                        $jar4->total_quantity += $contract->quantity;
                                        $jar4->allocat_quantity += $contract->quantity;
                                        $jar4->save();

                                    } else {
                                        $newJarAdd4 =  JarTransportation::create([
                                            'plant_id' => $address->plant_id,
                                            'driver_id' => $address->driver_id,
                                            'date' =>  Carbon::today(),
                                            'status' => 'dispatching',
                                            'total_quantity' => $contract->quantity,
                                            'allocated_quantity' => 0,
                                            'allocat_quantity' => $contract->quantity, // Consider fixing typo if not intentional
                                        ]);
                                    }
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
