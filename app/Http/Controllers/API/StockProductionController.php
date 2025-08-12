<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PlantProduction;
use App\Models\PlantProductionDetail;
use App\Models\RawStockForPlant;
use App\Models\rawMaterialVariants;
use App\Models\Orders;
use App\Models\Drivers;
use App\Models\JarMaintance;
use Carbon\Carbon;

class StockProductionController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->status;
        $count = $request->count;
        $today = Carbon::today();

        if ($count) {
            return $this->getCountStatistics($today);
        }

        if ($status) {
            return $this->getDriversByStatus($status, $today);
        }
        

        return response()->json([
            'status' => false,
            'message' => 'Please provide either a count flag or a status value.',
        ], 400);
    }

    protected function getCountStatistics($today)
    {
        $plantManagerId = $this->plantManagerId;

        // 1. Get Jar Stocks
        $jarStocks = RawStockForPlant::whereHas('rawMaterialVariant.rawMaterial', function ($q) {
            $q->where('name', 'Jar');
        })
        ->when($plantManagerId, fn($q) => $q->where('plant_id', $plantManagerId))
        ->with('rawMaterialVariant.rawMaterial')
        ->get();

        // 2. Total Production & Breakdown
        $jarWiseBreakdown = [];
        $totalProduction = 0;

        foreach ($jarStocks as $stock) {
            $variantName = $stock->rawMaterialVariant->rawMaterial->name . ' ' . ($stock->rawMaterialVariant->variant_name ?? 'Unknown Variant');
            $jarWiseBreakdown[$variantName] = ($jarWiseBreakdown[$variantName] ?? 0) + $stock->total_production_quantity;
            $totalProduction += $stock->total_production_quantity;
        }

        // 3. Prepare Initial Data
        $data = [
            'jar_total_production_quantity' => $totalProduction,
            'jar_variant_breakdown' => $jarWiseBreakdown,
            'dispatching' => 0,
            'dispatched' => 0,
            'receiving' => 0,
            'received' => 0,
            'driver_counts' => [
                'dispatching' => 0,
                'receiving' => 0,
                'received' => 0,
            ]
        ];

        // 4. Fetch & Process Orders Grouped by Driver
        $driverOrders = $this->getOrdersGroupedByDriver($today);

        $driverOrders->each(function ($orders) use (&$data) {
            $firstOrder = $orders->first();
            $driver = $firstOrder->drivers;
            $transport = $driver?->jarTransportation?->first();
            $status = $transport?->status;

            $balanceQty = $orders->sum(fn($o) => optional($o->contract)->quantity ?? 0);

            if (in_array($status, ['dispatching', 'receiving', 'received'])) {
                $data['driver_counts'][$status]++;
            }

            match ($status) {
                'dispatching' => $data['dispatching'] += $balanceQty,
                'receiving' => [
                    $data['dispatched'] += $balanceQty,
                    $data['receiving'] += $balanceQty
                ],
                'received' => $data['received'] += $balanceQty,
                default => null
            };
        });

        return response()->json([
            'status' => true,
            'message' => 'Order statistics retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    protected function getDriversByStatus($status, $today)
    {
        $driverOrders = $this->getOrdersGroupedByDriver($today);

        $filteredDrivers = $driverOrders->map(function ($orders) {
            $firstOrder = $orders->first();
            $status = optional($firstOrder->drivers->jarTransportation)->status;

            return [
                'jarTransportation' => $status,
                'driver_id' => $firstOrder->driver_id,
                'driver_name' => $firstOrder->drivers->name ?? 'Unknown',
                'total_delivered_qty' => $orders->sum(fn($order) => optional($order->contract)->quantity ?? 0),
                'total_return_qty' => $orders->sum(fn($order) => optional($order->contract)->quantity ?? 0),
                'total_balance_qty' => $orders->sum(fn($order) => optional($order->contract)->quantity ?? 0),
            ];
        })->filter(function ($driver) use ($status) {
            return $status === 'dispatched' 
                ? $driver['jarTransportation'] === 'receiving'
                : $driver['jarTransportation'] === $status;
        })->values();

        return response()->json([
            'status' => true,
            'message' => "Drivers filtered by status: {$status}",
            'data' => ['drivers' => $filteredDrivers]
        ], 200);
    }

    protected function getOrdersGroupedByDriver($today)
    {
        return Orders::whereDate('created_at', $today)
            ->whereNotNull('driver_id')
            ->whereHas('drivers', function ($query) {
                $query->where('plant_id', $this->plantManagerId);
            })
            ->with([
                'drivers:id,name,plant_id',
                'contract:id,quantity',
                'drivers.jarTransportation' => fn($q) => $q->whereDate('date', $today)
            ])
            ->get()
            ->groupBy('driver_id');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'total_count' => 'required|numeric|min:1',
            'production' => 'required|array|min:1',
            'production.*' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $plantId = auth()->user()->plantManager->id;

            // Check Cap stock quantity
            $capQuantity = RawStockForPlant::where('plant_id', $plantId)
                ->whereHas('rawMaterialVariant.rawMaterial', fn($q) => $q->where('name', 'Cap'))
                ->sum('total_quantity');

            if ($request->total_count > $capQuantity) {
                return response()->json([
                    'status' => false,
                    'message' => "Insufficient Cap stock: Available = $capQuantity, Requested = {$request->total_count}.",
                ], 400);
            }

            // Fetch rawMaterialVariants for Label and Jar once
            $rawStock = rawMaterialVariants::with('rawMaterial')
                ->whereHas('rawMaterial', fn($q) => $q->whereIn('name', ['Label', 'Jar']))
                ->select('id', 'variant_name', 'raw_material_id')
                ->get();

            // Separate labelled and unlabelled variants keyed by variant_name for quick lookup
            $unlabelled = $rawStock
                ->filter(fn($item) => !str_starts_with($item->variant_name, 'with Label - '))
                ->keyBy('variant_name');

            $labelled = $rawStock
                ->filter(fn($item) => str_starts_with($item->variant_name, 'with Label - '))
                ->keyBy('id');

            // Get the jar without label variant for convenience
            $jarWithoutLabelVariant = $unlabelled->get('without Label');
            $jarWithoutLabelVariantId = $jarWithoutLabelVariant->id ?? null;
            $jarWithoutLabelRawMaterialId = $jarWithoutLabelVariant->raw_material_id ?? null;

            // Load current stock grouped by material and variant name for fast lookup
            $rawStockPlant = RawStockForPlant::with('rawMaterialVariant.rawMaterial')
                ->where('plant_id', $plantId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(fn($item) => $item->rawMaterialVariant->rawMaterial->name ?? 'N/A')
                ->mapWithKeys(function ($items, $materialName) {
                    $variants = $items->mapWithKeys(fn($item) => [
                        $item->rawMaterialVariant->variant_name => $item->total_quantity
                    ]);
                    return [$materialName => $variants];
                });

            // Helper function to get quantity safely
            $getQty = fn($material, $variant) => $rawStockPlant[$material][$variant] ?? 0;

            // Prepare production data with stock validation
            $productionData = $labelled->map(function ($item) use ($unlabelled, $request, $getQty) {
                $labelName = trim(str_replace('with Label - ', '', $item->variant_name));
                $productionQty = $request->production[$item->id] ?? 0;

                $labelQty = $getQty('Label', $labelName);
                $jarWithLabelQty = $getQty('Jar', $item->variant_name);
                $jarWithoutLabelQty = $getQty('Jar', 'without Label');

                $labelId = $unlabelled[$labelName]->id ?? null;

                // Check stock availability
                $hasEnoughStock = $jarWithLabelQty >= $productionQty ||
                    ($jarWithoutLabelQty >= $productionQty && $labelQty >= $productionQty);

                return [
                    'label_id' => $labelId,
                    'variant_name' => $labelName,
                    'id' => $item->id,
                    'requested_qty' => $productionQty,
                    'available_labels' => $labelQty,
                    'available_jars_with_label' => $jarWithLabelQty,
                    'available_jars_without_label' => $jarWithoutLabelQty,
                    'has_enough_stock' => $hasEnoughStock,
                    'status' => $hasEnoughStock ? 'ok' : 'low stock',
                ];
            })->filter(fn($item) => array_key_exists($item['id'], $request->production))
            ->values();

            // Check if any production item lacks stock, fail early
            $lowStockItems = $productionData->filter(fn($item) => !$item['has_enough_stock']);
            if ($lowStockItems->isNotEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient stock for some items.',
                    'details' => $lowStockItems,
                ], 400);
            }

            // Deduct caps stock by total_count
            RawStockForPlant::where('plant_id', $plantId)
                ->whereHas('rawMaterialVariant.rawMaterial', fn($q) => $q->where('name', 'Cap'))
                ->decrement('total_quantity', $request->total_count);

            // Create PlantProduction record
            $production = PlantProduction::create([
                'plant_id' => $plantId,
                'production_date' => now(),
                'quantity' => $request->total_count,
            ]);

            // Loop through productionData to subtract stock and create details
            foreach ($productionData as $item) {
                $requestedQty = $item['requested_qty'];

                $jarWithLabelVariantId = $item['id']; // jar with label variant id
                $labelVariantId = $item['label_id']; // label variant id
                $labelName = $item['variant_name'];

                $jarWithLabelQty = $item['available_jars_with_label'];
                $jarWithoutLabelQty = $item['available_jars_without_label'];
                $labelQty = $item['available_labels'];

                if ($jarWithLabelQty >= $requestedQty) {
                    // Enough jars with label, decrement from jar with label stock
                    PlantProductionDetail::create([
                        'plant_production_id' => $production->id,
                        'raw_material_id' => $unlabelled[$labelName]->raw_material_id ?? null,
                        'quantity' => $requestedQty,
                    ]);

                    RawStockForPlant::where('plant_id', $plantId)
                        ->where('raw_material_variants_id', $jarWithLabelVariantId)
                        ->decrement('total_quantity', $requestedQty);

                    RawStockForPlant::where('plant_id', $plantId)
                        ->where('raw_material_variants_id', $jarWithLabelVariantId)
                        ->increment('total_production_quantity', $requestedQty);

                } else {
                    // Not enough jars with label, use jars without label + labels

                    // Decrement jars without label stock
                    RawStockForPlant::where('plant_id', $plantId)
                        ->where('raw_material_variants_id', $jarWithoutLabelVariantId)
                        ->decrement('total_quantity', $requestedQty);

                    RawStockForPlant::where('plant_id', $plantId)
                        ->where('raw_material_variants_id', $jarWithoutLabelVariantId)
                        ->increment('total_production_quantity', $requestedQty);

                    // Decrement label stock
                    RawStockForPlant::where('plant_id', $plantId)
                        ->where('raw_material_variants_id', $labelVariantId)
                        ->decrement('total_quantity', $requestedQty);

                    RawStockForPlant::where('plant_id', $plantId)
                        ->where('raw_material_variants_id', $labelVariantId)
                        ->increment('total_production_quantity', $requestedQty);

                    // Create PlantProductionDetail for jar without label
                    PlantProductionDetail::create([
                        'plant_production_id' => $production->id,
                        'raw_material_id' => $jarWithoutLabelRawMaterialId,
                        'quantity' => $requestedQty,
                    ]);

                    // Create PlantProductionDetail for label
                    PlantProductionDetail::create([
                        'plant_production_id' => $production->id,
                        'raw_material_id' => $unlabelled[$labelName]->raw_material_id ?? null,
                        'quantity' => $requestedQty,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Production recorded successfully.',
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Production Store Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving production data.',
                'error' => config('app.debug') ? $e->getMessage() : 'Please contact support.',
            ], 500);
        }
    }





    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if($request->status == 'dispatching'){
           return $this->dispatch($request, $id);
        }elseif($request->status == 'receiving'){
           return $this->receiving($request, $id);
        }
    }

    private function dispatch($request , $id){
        $validator = Validator::make($request->all(), [
                'status' => 'required|in:dispatching',
                "total_count" => 'required|numeric|min:1',
                'distritute' => 'required|array|min:1',
                'distritute.*' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                DB::beginTransaction();

                $plantId = $this->plantManagerId;
                $status = $request->status;
                $distribution = $request->distritute;

                // Step 1: Deduct distributed quantity from Jar stock
                foreach ($distribution as $variantId => $qty) {
                    $stock = RawStockForPlant::where('plant_id', $plantId)
                        ->where('raw_material_variants_id', $variantId)
                        ->first();

                    if (!$stock || $stock->total_production_quantity < $qty) {
                        throw new \Exception("Insufficient stock for Jar variant ID: ".$stock->rawMaterialVariant->rawMaterial->name. " ".$stock->rawMaterialVariant->variant_name);
                    }
                    $stock->decrement('total_production_quantity', $qty);
                }

                // Step 2: Update jarTransportation status to next step
                $driver = Drivers::with('jarTransportation')->find($id);

                if (!$driver || !$driver->jarTransportation) {
                    throw new \Exception("Driver or transportation data not found.");
                }

                $currentStatus = $driver->jarTransportation->status;
                $nextStatus = match ($currentStatus) {
                    'dispatching' => 'receiving',
                    default => null,
                };

                if (!$nextStatus) {
                    throw new \Exception("Invalid or terminal status: $currentStatus");
                }

                $driver->jarTransportation->update(['status' => $nextStatus]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => "Updated successfully. Status moved from $currentStatus to $nextStatus.",
                ], 200);

            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('Production Update Error: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while updating production data.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Please contact support.',
                ], 500);
            }
    }

    private function receiving($request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:receiving',
        'total_count' => 'required|numeric|min:1',
        'fill_jar' => 'nullable|array',
        'fill_jar.*' => 'nullable|numeric',
        'maintance_jar_green' => 'nullable|array',
        'maintance_jar_green.*' => 'nullable|numeric',
        'maintance_jar_leack' => 'nullable|array',
        'maintance_jar_leack.*' => 'nullable|numeric',
        'jar_with_labels' => 'required|array',
        'jar_with_labels.*' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $plantId = $this->plantManagerId;
        $status = $request->status;
        $jarWithLabels = $request->jar_with_labels;
        $fillJar = $request->fill_jar ?? [];
        $maintanceGreenJar = $request->maintance_jar_green ?? [];
        $maintanceLeackJar = $request->maintance_jar_leack ?? [];

        // ✅ Add to production quantity for filled jars
        foreach ($fillJar as $variantId => $qty) {
            if ($qty > 0) {
                $stock = RawStockForPlant::firstOrNew([
                    'plant_id' => $plantId,
                    'raw_material_variants_id' => $variantId
                ]);
                $stock->total_production_quantity += $qty;
                $stock->save();
            }
        }

        // ✅ Add to stock for labeled jars
        foreach ($jarWithLabels as $variantId => $qty) {
            if ($qty > 0) {
                $stock = RawStockForPlant::firstOrNew([
                    'plant_id' => $plantId,
                    'raw_material_variants_id' => $variantId
                ]);
                $stock->total_quantity += $qty;
                $stock->save();
            }
        }

        // ✅ Save maintenance jars (green)
        foreach ($maintanceGreenJar as $variantId => $qty) {
            if ($qty > 0) {
                JarMaintance::create([
                    'plant_id' => $plantId,
                    'driver_id' => $id,
                    'date' => now(),
                    'qty' => $qty,
                    'raw_material_variants_id' => $variantId,
                    'type' => 'green-jar',
                ]);
            }
        }

        // ✅ Save maintenance jars (leak)
        foreach ($maintanceLeackJar as $variantId => $qty) {
            if ($qty > 0) {
                JarMaintance::create([
                    'plant_id' => $plantId,
                    'driver_id' => $id,
                    'date' => now(),
                    'qty' => $qty,
                    'raw_material_variants_id' => $variantId,
                    'type' => 'leacked-jar',
                ]);
            }
        }

        $driver = Drivers::with('jarTransportation')->find($id);

        if (!$driver || !$driver->jarTransportation) {
            throw new \Exception("Driver or transportation data not found.");
        }

        $currentStatus = $driver->jarTransportation->status;
        $nextStatus = match ($currentStatus) {
            'receiving' => 'received',
            default => null,
        };

        if (!$nextStatus) {
            throw new \Exception("Invalid or terminal status: $currentStatus");
        }

        $driver->jarTransportation->update(['status' => $nextStatus]);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => "Updated successfully. Status moved from $currentStatus to $nextStatus.",
        ], 200);
    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('Production Update Error: ' . $e->getMessage(), [
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'An error occurred while updating production data.',
            'error' => config('app.debug') ? $e->getMessage() : 'Please contact support.',
        ], 500);
    }
}

    


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
