<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{rawMaterialVariants, rawStockTransactions, rawDistributions, rawStockLogs, Plant, RawStockForPlant};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RawMaterialsStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materialVariants = rawMaterialVariants::orderBy('created_at', 'desc')
            ->with(['rawMaterial'])
            ->get();
        return view('pages.stocks.raw.index', compact('materialVariants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.stocks.raw.add-edit');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:raw_material_variants,variant_name',
                'type' => 'nullable|in:main,distributor',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $type = $request->input('type', 'main');

            DB::beginTransaction();

            // ✅ Create variants
            $variant1 = rawMaterialVariants::create([
                'raw_material_id' => 2,
                'variant_name' => $request->name,
                'total_quantity' => 0,
                'remain_quantity' => 0,
                'type' => $type,
            ]);

            $variant2 = rawMaterialVariants::create([
                'raw_material_id' => 1,
                'variant_name' => 'with Label - ' . $request->name,
                'total_quantity' => 0,
                'remain_quantity' => 0,
                'type' => $type,
            ]);

            // 🔥 Get all plants
            $plants = Plant::all();

            // 🔥 Insert plant-wise stock
            foreach ($plants as $plant) {

                // for variant 1
                RawStockForPlant::create([
                    'plant_id' => $plant->id,
                    'raw_material_variants_id' => $variant1->id,
                    'total_quantity' => 0,
                    'total_production_quantity' => 0,
                ]);

                // for variant 2
                RawStockForPlant::create([
                    'plant_id' => $plant->id,
                    'raw_material_variants_id' => $variant2->id,
                    'total_quantity' => 0,
                    'total_production_quantity' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Label + plant stock created successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Label Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = rawMaterialVariants::with([
        'transactions' => function ($q) {
            $q->orderBy('created_at', 'desc');
        },
        'transactions.distributions.plant'
    ])->findOrFail($id);
        $show = true;
        return view('pages.stocks.raw.add-edit', compact('product', 'show'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = rawMaterialVariants::find($id);
        $show = false;
        $plant =Plant::pluck('name', 'id');
        return view('pages.stocks.raw.add-edit', compact('product', 'show', 'plant', 'id'));
    }

    public function distribute(string $id)
    {
        $product = rawMaterialVariants::find($id);
        $show = false;
        $plant =Plant::pluck('name', 'id');
        $distribute = true;
        return view('pages.stocks.raw.add-edit', compact('product', 'show', 'plant', 'id', 'distribute'));
    }

    public function purchesDistribute(string $id)
    {
        $product = rawMaterialVariants::find($id);
        $show = false;
        $plant =Plant::pluck('name', 'id');
        return view('pages.stocks.raw.add-edit', compact('product', 'show', 'plant', 'id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:raw_material_variants,id',
            'total_quantity' => 'required|numeric|min:1',
            'allocations' => 'required|array|min:1',
            'allocations.*' => 'required|numeric|min:1',
            'remain_quantity' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $id) {
            $variant = rawMaterialVariants::findOrFail((int) $id);
            $userId = Auth::id();
            $rawMaterialId = $variant->raw_material_id;
            $totalQuantity = (float) $request->total_quantity;
            $isPurchase = (int) $request->distribute === 0;
            $hasAllocations = isset($request->allocations) && is_array($request->allocations) && count($request->allocations) > 0;

            $transactionType = $isPurchase ? 'purchase' : 'distribution';

            if ($isPurchase) {
                // Increase stock and update remaining quantity
                $variant->increment('total_quantity', $totalQuantity);
                $variant->increment('remain_quantity', (float) $request->remain_quantity);

                rawStockLogs::create([
                    'raw_material_id' => $rawMaterialId,
                    'user_id' => $userId,
                    'action' => 'purchase',
                    'quantity' => $totalQuantity,
                    'note' => 'Purchased and added to stock',
                    'action_time' => now(),
                ]);
            } else {
                // Only update remain_quantity on distribution
                $variant->remain_quantity = (float) $request->remain_quantity;
            }

            $variant->save();

            // Create a transaction only if there are allocations
            if ($hasAllocations) {
                $transaction = rawStockTransactions::create([
                    'raw_material_variant_id' => $variant->id,
                    'type' => $transactionType,
                    'quantity' => $isPurchase ? $totalQuantity : array_sum($request->allocations),
                ]);

                foreach ($request->allocations as $plantId => $qty) {
                    $qty = (float) $qty;

                    rawDistributions::create([
                        'raw_stock_transactions_id' => $transaction->id,
                        'plant_id' => (int) $plantId,
                        'quantity' => $qty,
                        'status' => 'pending',
                    ]);

                    rawStockLogs::create([
                        'raw_material_id' => $rawMaterialId,
                        'user_id' => $userId,
                        'plant_id' => (int) $plantId,
                        'action' => 'distribution',
                        'quantity' => $qty,
                        'note' => 'Distributed to plant (pending)',
                        'action_time' => now(),
                    ]);
                }
            }
        });


        return response()->json(['message' => 'Stock updated and distributed (pending) successfully.']);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
