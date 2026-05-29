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
use App\Models\rawStockTransactions;
use App\Models\rawMaterialVariants;
use App\Models\rawStockLogs;
use App\Models\JarTransportation;
use App\Models\JarMaintance;
use App\Models\ScrabJar;
use App\Models\DistributorPlantInventory;
use Illuminate\Support\Facades\DB;

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

        $inventories = collect();

        if ($this->distributorId !== null) {
            $inventories = DistributorPlantInventory::where('distributor_id', $this->distributorId)
                ->get()
                ->keyBy('plant_id');
        }

        $plantsWithStock = $plants->map(function ($plant) use ($inventories) {
            $data = [
                'id' => $plant->id,
                'name' => $plant->name,
            ];

            if ($this->distributorId !== null) {
                $inventory = $inventories[$plant->id] ?? null;

                $data['remaining_empty_jars'] = $inventory->empty_jars ?? 0;
            }
            return $data;
        });
        return response()->json([
            'status' => true,
            'data' => $plantsWithStock
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
                    $jar4 = JarTransportation::where('date',  Carbon::today())
                                        ->where('driver_id', $address->driver_id)
                                        ->where('plant_id', $address->plant_id)
                                        ->first();

                                    if ($jar4) {
                                        // If already exists, increment total_quantity
                                        $jar4->total_quantity += $contract->quantity;
                                        $jar4->allocat_quantity += $contract->quantity; // Consider fixing typo if not intentional
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

        $GjarQty = JarMaintance::where('type', 'green-jar')->sum('qty');
        $LjarQty = JarMaintance::where('type', 'leacked-jar')->sum('qty');

        $maintenanceData = [
            'material_name' => 'Maintenance',
            'variants' => [
                [
                    'variant_name' => 'Green Jar',
                    'full_name' => 'Green Jar',
                    'quantity' => $GjarQty,
                ],
                [
                    'variant_name' => 'Leaked Jar',
                    'full_name' => 'Leaked Jar',
                    'quantity' => $LjarQty,
                ],
            ],
        ];

        $rawStock->push($maintenanceData);
        return response()->json([
            'status' => true,
            'message' => 'Stock List retrieved successfully.',
            'data' => $rawStock,
        ]);
    }

    public function acceptStock($id)
    {
        try {
            $user = auth()->user();
            $plantId = $user->plantManager->id;

            DB::beginTransaction();

            $distribution = RawDistributions::findOrFail($id);

            // ✅ Ensure the distribution belongs to the same plant
            if ((int) $distribution->plant_id !== (int) $plantId) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'You are not authorized to accept this distribution.',
                ], 403);
            }

            $transaction = rawStockTransactions::findOrFail((int) $distribution->raw_stock_transactions_id);
            $variant = rawMaterialVariants::findOrFail((int) $transaction->raw_material_variant_id);

            $plantStock = RawStockForPlant::where([
                'plant_id' => $plantId,
                'raw_material_variants_id' => $variant->id,
            ])->firstOrFail();
            // Mark distribution as accepted
            $distribution->status = 'accepted';
            $distribution->accepted_at = now();
            $distribution->save();


            // Update global variant stock
            $variant->decrement('total_quantity', (int) $distribution->quantity);        
            $variant->save();

            // Update plant's stock
            $plantStock->increment('total_quantity', (int) $distribution->quantity);
            $plantStock->save();

            // Log the stock acceptance
            rawStockLogs::create([
                'raw_material_id' => $variant->raw_material_id,
                'user_id' => $user->id,
                'plant_id' => $plantId,
                'action' => 'acceptance',
                'quantity' => $distribution->quantity,
                'note' => 'Accepted distribution to plant',
                'action_time' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Stock accepted successfully.',
                'data' => $distribution,
            ]);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Record not found.',
            ], 404);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLabels(Request $request)
    {
        $For = $request->query('for');
        $type = $request->query('type');
        $rawStock = rawMaterialVariants::whereHas('rawMaterial', function ($query) {
                $query->whereIn('name', ['Label', 'Jar']);
            })
            // ->when(!empty($type), function ($q) use ($type) {
            //     $q->where('type', $type);
            // })
            ->select('id', 'variant_name')
            ->get();

        $unlabelled = $rawStock->filter(function($item) {
            return !str_starts_with($item->variant_name, 'with Label - ');
        })->keyBy('variant_name');  // key by name for easy lookup

        $labelled = $rawStock->filter(function($item) {
            return str_starts_with($item->variant_name, 'with Label - ');
        });

        $rawStockPlant = RawStockForPlant::when(auth()->user()?->plantManager, function ($q) {
                    $q->where('plant_id', auth()->user()->plantManager->id);
            })
            ->with(['plant', 'rawMaterialVariant', 'rawMaterialVariant.rawMaterial'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return optional($item->rawMaterialVariant?->rawMaterial)->name ?? 'N/A';
            })
            ->map(function ($items, $materialName) {
                return [
                    'material_name' => $materialName,
                    'variants' => $items->map(function ($item) {
                        return [
                            'variant_name' => optional($item->rawMaterialVariant)->variant_name,
                            'full_name' => optional($item->rawMaterialVariant?->rawMaterial)->name . ' - ' . optional($item->rawMaterialVariant)->variant_name,
                            'quantity' => $item->total_quantity,
                        ];
                    })->values(),
                ];
            })->values();

            $jarStocks = RawStockForPlant::whereHas('rawMaterialVariant.rawMaterial', function ($query) {
                $query->where('name', 'Jar');
            })
            // ->when(empty($type), function ($q) {
            //     // ✅ Apply plant filter ONLY if type NOT passed
            //     $q->where('plant_id', auth()->user()->plantManager->id);
            // })
            // ->when(auth()->user()->plantManager->id, function ($q) {
            //     $q->where('plant_id', auth()->user()->plantManager->id);
            // })
            ->with('rawMaterialVariant.rawMaterial')
            ->get()
            ->groupBy(function ($item) {
                return optional($item->rawMaterialVariant->rawMaterial)->name;
            })
            ->map(function ($items, $materialName) {
                return [
                    'material_name' => $materialName,
                    'variants' => $items->map(function ($item) {
                        return [
                            'variant_name' => optional($item->rawMaterialVariant)->variant_name,
                            'full_name' => optional($item->rawMaterialVariant->rawMaterial)->name . ' - ' . optional($item->rawMaterialVariant)->variant_name,
                            'quantity' => $item->total_production_quantity,
                        ];
                    })->values(),
                ];
            })->values();

            $finalStock = $For == 'raw' ? $rawStockPlant : $jarStocks;

        // Map labelled to required structure
        $data = $labelled->map(function($item) use ($unlabelled, $finalStock, $For) {
            $labelName = trim(str_replace('with Label - ', '', $item->variant_name));

            // Get grouped material types
            $labelGroup = $finalStock->firstWhere('material_name', 'Label');
            $jarGroup   = $finalStock->firstWhere('material_name', 'Jar');
            $capGroup   = $finalStock->firstWhere('material_name', 'Cap');

            // Initialize quantities
            $labelQty = 0;
            $jarQty   = 0;
            $capQty   = 0;
            $labeledJarQty = 0;

            // Get Label quantity
            if ($labelGroup) {
                $labelVariant = collect($labelGroup['variants'])->firstWhere('variant_name', $labelName);
                $labelQty = $labelVariant['quantity'] ?? 0;
            }

            // Get Jar without Label quantity
            if ($jarGroup) {
                $jarWithoutLabel = collect($jarGroup['variants'])->firstWhere('variant_name', 'without Label');
                $jarQty = $jarWithoutLabel['quantity'] ?? 0;

                // Get Jar *with* this specific label
                $jarWithLabel = collect($jarGroup['variants'])->firstWhere('variant_name', 'with Label - ' . $labelName);
                $labeledJarQty = $jarWithLabel['quantity'] ?? 0;
            }

            // Get Cap quantity
            if ($capGroup) {
                $capVariant = collect($capGroup['variants'])->firstWhere('variant_name', 'Plastic Cap');
                $capQty = $capVariant['quantity'] ?? 0;
            }

            // Logic:
            // Disable if:
            // 1. Cap is 0 OR
            // 2. Jar without Label is 0 OR
            // 3. (Label is 0 AND Jar with Label is 0)
            $disable = $For == 'raw' ? ($capQty == 0 || ($jarQty == 0 && $labeledJarQty == 0) || ($labelQty == 0 && $labeledJarQty == 0)) : ($labeledJarQty == 0) ;

            return [
                'label_id' => $unlabelled[$labelName]->id ?? null,
                'variant_name' => $labelName,
                'id' => $item->id,
                'disable' => $disable,
            ];
        })->filter(function($item) {
            return $item['label_id'] !== null;
        })->values();



        return response()->json([
            'status' => true,
            'message' => 'Label List retrieved successfully.',
            'data' => $data,
        ]);

    }

    public function getJarMaintenanceList(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:green-jar,leacked-jar',
        ]);

        $type = $request->type;
        $jars = JarMaintance::where('type', $type)->get(['id', 'type', 'qty', 'created_at']);
        $formattedName = ucwords(str_replace('-', ' ', $type));

        return response()->json([
            'status' => true,
            'message' => "{$formattedName} list retrieved successfully.",
            'data' => $jars,
        ]);
    }

    public function deductJarQuantity(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'type'      => 'required|string|in:green-jar,leacked-jar',
            'qty'       => 'required|numeric|min:1',
            'amount'    => 'nullable|numeric|min:1',
            'addition'  => 'nullable|array',
            'addition.*'=> 'nullable|numeric|min:0',
        ]);

        $type         = $validated['type'];
        $qtyToDeduct  = $validated['qty'];
        $plantId      = $this->plantManagerId ?? null;

        if (!$plantId) {
            return response()->json([
                'status'  => false,
                'message' => 'Plant Manager ID not found.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $jars = JarMaintance::where('type', $type)
                ->orderBy('created_at', 'asc')
                ->get();

            $remainingToDeduct = $qtyToDeduct;

            foreach ($jars as $jar) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                if ($jar->qty <= $remainingToDeduct) {

                    $remainingToDeduct -= $jar->qty;
                    $jar->delete();
                } else {

                    $jar->qty -= $remainingToDeduct;
                    $jar->status = 'in-prgress';
                    $jar->save();
                    $remainingToDeduct = 0;
                }
            }

            if ($remainingToDeduct > 0) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => "Not enough {$type} quantity. Requested {$qtyToDeduct}, deducted " . ($qtyToDeduct - $remainingToDeduct) . ".",
                ], 400);
            }

            if ($type === 'green-jar' && !empty($validated['addition'])) {
                $additions = $validated['addition'] ?? [];
                
                foreach ($additions as $variantId => $qty) {
                    if ($qty > 0) {
                        $stock = RawStockForPlant::firstOrNew([
                            'plant_id' => $plantId,
                            'raw_material_variants_id' => $variantId,
                        ]);


                        $stock->total_quantity = ($stock->total_quantity ?? 0) + $qty;
                        $stock->save();
                    }
                }
            }
            if ($type === 'leacked-jar') {
                ScrabJar::create([
                    'plant_id' => $plantId,
                    'qty'      => $qtyToDeduct,
                    'amount'   => $validated['amount'] ?? 0,
                ]);
            }
            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => "{$qtyToDeduct} quantity deducted successfully from {$type}.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
