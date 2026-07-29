<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\DistributorPlantOrder;
use App\Models\DistributorPlantInventory;
use App\Models\DistributorPlantProduction;
use App\Models\DistributorPlantDispatch;
use App\Models\DistributorPlantOrderAcceptance;

class DistributorOrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Validate status if provided
            $status = $request->input('status'); 
            
            $query = DistributorPlantOrder::with([
                'plant:id,name',
                'distributor:id,name'
            ])->latest();

            if ($this->plantManagerId) {
                $query->with('distributor')->where('plant_id', $this->plantManagerId);
            } else {
                // Ensure the user is a distributor
                $query->with('plant')->where('distributor_id', auth()->id());
            }

            // Apply status filter only if it's not null/empty
            $query->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            });

            // Use pagination for better performance
            $orders = $query->paginate(10);
            $orders->getCollection()->transform(function ($order) {

                $available = $order->delivered_jars + $order->used_previous_stock;
                $required = $order->required_labeled_jars + $order->required_unlabeled_jars;

                $isPlantManager = $this->plantManagerId ? true : false;

                return [
                    'id' => $order->id,
                    'status' => $order->status,

                    // 🔁 Dynamic based on role
                    $isPlantManager ? 'distributor' : 'plant' => [
                        'id' => $isPlantManager 
                            ? $order->distributor->id 
                            : $order->plant->id,

                        'name' => $isPlantManager 
                            ? $order->distributor->name 
                            : $order->plant->name,
                    ],

                    'summary' => [
                        'available' => $available,
                        'required' => $required,
                        'remaining' => $available - $required
                    ],

                    'created_at' => $order->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch orders.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function summary()
    {
        $userId = auth()->id();

        $ordersQuery = DistributorPlantOrder::where('distributor_id', $userId);

        // ✅ Dynamic status counts (NO missing status ever)
        $statuses = [
              'pending',
                'approved',
                'in_production',
                'production_completed',
                'dispatched',
                'delivered',
                'closed',
                'rejected'
        ];

        $orderCounts = [
            'total' => (clone $ordersQuery)->count()
        ];

        foreach ($statuses as $status) {
            $orderCounts[$status] = (clone $ordersQuery)
                ->where('status', $status)
                ->count();
        }

        // 🧠 Get plant IDs
        $plantIds = (clone $ordersQuery)->distinct()->pluck('plant_id');

        // 📦 Get inventories WITH plant
        $inventories = DistributorPlantInventory::with('plant:id,name')
            ->whereIn('plant_id', $plantIds)
            ->get()
            ->keyBy('plant_id');

        // 📊 Plant stock
        $plantStock = collect($plantIds)->map(function ($plantId) use ($inventories) {

            $inventory = $inventories[$plantId] ?? null;

            return [
                'plant_id' => $plantId,
                'plant_name' => $inventory?->plant?->name ?? 'Plant #'.$plantId,
                'remaining_empty_jars' => $inventory->empty_jars ?? 0
            ];
        });

        $totalStock = $plantStock->sum('remaining_empty_jars');

        return response()->json([
            'orders' => $orderCounts,
            'plant_stock' => [
                'total_empty_jars' => $totalStock,
                'plants' => $plantStock
            ]
        ]);
    }

    public function plantWiseSummery()
    {
               $inventories = DistributorPlantInventory::with('plant:id,name')
            ->whereIn('plant_id', $plantIds)
            ->get()
            ->keyBy('plant_id');

        // 📊 Plant stock
        $plantStock = collect($plantIds)->map(function ($plantId) use ($inventories) {

            $inventory = $inventories[$plantId] ?? null;

            return [
                'plant_id' => $plantId,
                'plant_name' => $inventory?->plant?->name ?? 'Plant #'.$plantId,
                'remaining_empty_jars' => $inventory->empty_jars ?? 0
            ];
        });

        $totalStock = $plantStock->sum('remaining_empty_jars');

        return response()->json([
            'orders' => $orderCounts,
            'plant_stock' => [
                'total_empty_jars' => $totalStock,
                'plants' => $plantStock
            ]
        ]); 
    }

    public function action(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'action' => 'required|in:approve,reject',
                'remarks' => 'nullable|string|max:500'
            ]);

            $user = auth()->user();

            // 🔒 Lock the row to avoid race conditions
            $order = DistributorPlantOrder::where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            // 🔐 Authorization (only plant manager of this plant)
            if ($this->plantManagerId && $order->plant_id !== $this->plantManagerId) {
                return response()->json([
                    'message' => 'Unauthorized action'
                ], 403);
            }

            // 🔁 Idempotency: same action already applied → return OK
            if ($order->status === 'approved' && $request->action === 'approve') {
                DB::commit();
                return response()->json([
                    'message' => 'Order already approved',
                    'data' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'approved_at' => $order->approved_at
                    ]
                ]);
            }

            if ($order->status === 'rejected' && $request->action === 'reject') {
                DB::commit();
                return response()->json([
                    'message' => 'Order already rejected',
                    'data' => [
                        'id' => $order->id,
                        'status' => $order->status
                    ]
                ]);
            }

            // ❌ Only pending can transition
            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Order already processed'
                ], 422);
            }

            // 🔄 Apply action
            if ($request->action === 'approve') {

                // Option A: keep 'approved'
                // Option B (simpler flow): jump directly to 'in_production'
                $order->update([
                    'status' => 'approved', // or 'in_production'
                    'approved_at' => now(),
                    'approved_by' => $user->id, // optional column if you add it
                ]);

            } else {

                $order->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),     // add this column if you can
                    'rejection_remarks' => $request->remarks,
                    'rejected_by' => $user->id // optional
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => $request->action === 'approve'
                    ? 'Order approved successfully'
                    : 'Order rejected successfully',
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'approved_at' => $order->approved_at ?? null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function produce(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'leak_jars' => 'nullable|integer|min:0',
                'green_jars' => 'nullable|integer|min:0',
            ]);

            $user = auth()->user();

            // 🔒 Lock order
            $order = DistributorPlantOrder::where('id', $id)->firstOrFail();

            if (!in_array($order->status, ['approved', 'in_production'])) {
                return response()->json([
                    'message' => 'Order not ready for production'
                ], 422);
            }

            if ($this->plantManagerId && $order->plant_id !== $this->plantManagerId) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (DistributorPlantProduction::where('distributor_plant_orders_id', $order->id)->exists()) {
                return response()->json([
                    'message' => 'Production already completed'
                ], 422);
            }

            // 🔒 LOCK INVENTORY (IMPORTANT)
            $inventory = DistributorPlantInventory::where([
                'plant_id' => $order->plant_id,
                'distributor_id' => $order->distributor_id,
            ])->lockForUpdate()->first();

            if (!$inventory) {
                $inventory = DistributorPlantInventory::create([
                    'plant_id' => $order->plant_id,
                    'distributor_id' => $order->distributor_id,
                    'empty_jars' => 0
                ]);
            }

            // 📦 Values
            $previousStock = $inventory->empty_jars;
            $delivered = $order->delivered_jars;

            $labeled = $order->required_labeled_jars;
            $unlabeled = $order->required_unlabeled_jars;

            $leak = $request->leak_jars ?? 0;
            $green = $request->green_jars ?? 0;

            // 🧮 Calculation
            $totalAvailable = $previousStock + $delivered;
            $defects = $leak + $green;
            $usable = $totalAvailable - $defects;
            $required = $labeled + $unlabeled;

            if ($usable < $required) {
                return response()->json([
                    'message' => 'Not enough usable jars after defects',
                    'usable' => $usable,
                    'required' => $required
                ], 422);
            }

            $remaining = $usable - $required;

            if (!$order->allow_remaining_stock && $remaining > 0) {
                return response()->json([
                    'message' => 'Remaining stock not allowed'
                ], 422);
            }
            $usedPreviousStock = min($previousStock, $required);

            // ✅ SAVE PRODUCTION
            $production = DistributorPlantProduction::create([
                'distributor_plant_orders_id' => $order->id,
                'delivered_jars' => $delivered,
                'used_previous_stock'=> $usedPreviousStock,
                'total_available' => $totalAvailable,
                'leak_jars' => $leak,
                'green_jars' => $green,
                'usable_jars' => $usable,
                'labeled_jars' => $labeled,
                'unlabeled_jars' => $unlabeled,
                'remaining_stock' => $remaining,
                'label_breakdown' => $order->jars_with_label,
                'completed_at' => now()
            ]);

            $inventory->empty_jars = $remaining;
            $inventory->save();


            // ✅ UPDATE ORDER
            $order->update([
                'status' => 'production_completed'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Production completed successfully',
                'data' => [
                    'previous_stock' => $previousStock,
                    'delivered' => $delivered,
                    'total_available' => $totalAvailable,
                    'usable' => $usable,
                    'required' => $required,
                    'remaining' => $remaining,
                    'leak_jars' => $leak,
                    'green_jars' => $green
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dispatch(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'dispatch_labeled_jars' => 'required|integer|min:0',
                'dispatch_unlabeled_jars' => 'nullable|integer|min:0',
            ]);

            // 🔒 Lock order (better safety)
            $order = DistributorPlantOrder::with('production')
                ->lockForUpdate()
                ->findOrFail($id);

            // ❌ Prevent duplicate dispatch
            if (DistributorPlantDispatch::where('distributor_plant_orders_id', $order->id)->exists()) {
                return response()->json([
                    'message' => 'Already dispatched'
                ], 422);
            }

            // ❌ Only after production
            if ($order->status !== 'production_completed') {
                return response()->json([
                    'message' => 'Order not ready for dispatch'
                ], 422);
            }

            $production = $order->production;

            $dispatchLabeled = $request->dispatch_labeled_jars;
            $dispatchUnlabeled = $request->dispatch_unlabeled_jars ?? 0;

            // ✅ Validation: cannot dispatch more than produced
            if (
                $dispatchLabeled > $production->labeled_jars ||
                $dispatchUnlabeled > $production->unlabeled_jars
            ) {
                return response()->json([
                    'message' => 'Dispatch exceeds produced quantity'
                ], 422);
            }

            // ✅ Create dispatch record (NO inventory change)
            $dispatch = DistributorPlantDispatch::create([
                'distributor_plant_orders_id' => $order->id,
                'dispatched_labeled_jars' => $dispatchLabeled,
                'dispatched_unlabeled_jars' => $dispatchUnlabeled,
                'label_breakdown' => $production->label_breakdown,
                'dispatched_at' => now()
            ]);

            // ✅ Update order status
            $order->update([
                'status' => 'dispatched'
            ]);

            DB::commit();

            // 🎯 CLEAN RESPONSE (same format as production)
            return response()->json([
                'message' => 'Dispatched successfully',
                'data' => [
                    'delivered_jars' => $dispatchLabeled + $dispatchUnlabeled,
                    'dispatched_labeled_jars' => $dispatchLabeled,
                    'dispatched_unlabeled_jars' => $dispatchUnlabeled,
                    'status' => 'dispatched'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function accept(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'received_labeled_jars' => 'required|integer|min:0',
                'received_unlabeled_jars' => 'nullable|integer|min:0',
                'leak_jars' => 'nullable|integer|min:0',
                'green_jars' => 'nullable|integer|min:0',
                'status' => 'nullable|in:accepted,partial,rejected',
                'remarks' => 'nullable|string'
            ]);

            $user = auth()->user();

            $order = DistributorPlantOrder::with('dispatch')->findOrFail($id);

            // ❌ Only dispatched orders
            if ($order->status !== 'dispatched') {
                return response()->json([
                    'message' => 'Order not ready for acceptance'
                ], 422);
            }

            $dispatch = $order->dispatch;

            if (!$dispatch) {
                return response()->json([
                    'message' => 'Dispatch record not found'
                ], 422);
            }

            $receivedLabeled = $request->received_labeled_jars;
            $receivedUnlabeled = $request->received_unlabeled_jars ?? 0;
            $damaged = $request->leak_jars ?? 0;
            $green = $request->green_jars ?? 0;

            $dispatchedTotal = $dispatch->dispatched_labeled_jars + $dispatch->dispatched_unlabeled_jars;

            $receivedTotal = $receivedLabeled + $receivedUnlabeled + $damaged;

            // ❌ Basic validation
            if ($receivedTotal > $dispatchedTotal) {
                return response()->json([
                    'message' => 'Received count exceeds dispatched'
                ], 422);
            }

            // 🔎 Decide status automatically if not provided
            $status = $request->status;

            if (!$status) {
                if ($receivedTotal == $dispatchedTotal && $damaged == 0) {
                    $status = 'accepted';
                } elseif ($receivedTotal > 0) {
                    $status = 'partial';
                } else {
                    $status = 'rejected';
                }
            }

            // ✅ Save acceptance
            $acceptance = DistributorPlantOrderAcceptance::create([
                'distributor_plant_orders_id' => $order->id,
                'received_labeled_jars' => $receivedLabeled,
                'received_unlabeled_jars' => $receivedUnlabeled,
                'damaged_jars' => $damaged,
                'remarks' => $request->remarks,
                'status' => $status,
                'accepted_at' => now()
            ]);

            // 🔁 OPTIONAL: return damaged jars to plant inventory
            if ($damaged > 0) {
                $inventory = DistributorPlantInventory::where('plant_id', $order->plant_id)->where('distributor_id', $user->id)->first();

                if ($inventory) {
                    $inventory->empty_jars += $damaged;
                    $inventory->save();
                }
            }

            // ✅ Update order status
            $order->update([
                'status' => $status === 'accepted' ? 'closed' : $status
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order verification completed',
                'data' => [
                    'acceptance' => $acceptance,
                    'final_status' => $order->status
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();

            // ✅ VALIDATION
            $request->validate([
                'plant_id' => 'required|exists:plants,id',
                'delivered_jars' => 'required|integer|min:0',

                'required_labeled_jars' => 'nullable|integer|min:0',
                'required_unlabeled_jars' => 'nullable|integer|min:0',

                'jars_with_label' => 'nullable|array',
                'allow_remaining_stock' => 'required|boolean',
            ]);

            // This controller is also exposed through shared authenticated routes.
            // Apply allocation restrictions only when the authenticated account is
            // a distributor; driver, customer, vendor, user and plant-manager
            // behavior must remain unchanged.
            if (
                $this->distributorId !== null
                && !$user->plants()->whereKey($request->plant_id)->exists()
            ) {
                DB::rollBack();

                return response()->json([
                    'message' => 'The selected plant is not allocated to this distributor.'
                ], 403);
            }

            $labeled = $request->required_labeled_jars ?? 0;
            $unlabeled = $request->required_unlabeled_jars ?? 0;

            // ❌ At least one production required
            if (($labeled + $unlabeled) == 0) {
                return response()->json([
                    'message' => 'Order must have production quantity'
                ], 422);
            }

            // ✅ Get or create inventory
            $inventory = DistributorPlantInventory::firstOrCreate(
                [
                    'plant_id' => $request->plant_id,
                    'distributor_id' => $user->id,
                ],
                [
                    'empty_jars' => 0
                ]
            );

            // ❌ Label required if labeled jars exist
            if ($labeled > 0 && !$request->jars_with_label) {
                return response()->json([
                    'message' => 'Label details required for labeled jars'
                ], 422);
            }

            // ✅ Label validation
            if ($request->jars_with_label) {
                if (array_sum($request->jars_with_label) != $labeled) {
                    return response()->json([
                        'message' => 'Label count mismatch'
                    ], 422);
                }
            }

            // ✅ CORE LOGIC
            $previousStock = $inventory->empty_jars;
            $delivered = $request->delivered_jars;

            $totalAvailable = $previousStock + $delivered;
            $totalRequired = $labeled + $unlabeled;

            // ❌ Not enough jars
            if ($totalRequired > $totalAvailable) {
                return response()->json([
                    'message' => 'Not enough jars for production'
                ], 422);
            }

            // ✅ Remaining calculation
            $remaining = $totalAvailable - $totalRequired;

            // ❌ If leftover not allowed
            if (!$request->allow_remaining_stock && $remaining > 0) {
                return response()->json([
                    'message' => 'Remaining stock not allowed. Adjust quantities.',
                    'suggestion' => [
                        'required_unlabeled_jars' => $totalAvailable - $labeled
                    ],
                    'debug' => [
                        'previous_stock' => $previousStock,
                        'delivered' => $delivered,
                        'total_available' => $totalAvailable,
                        'total_required' => $totalRequired,
                        'remaining' => $remaining
                    ]
                ], 422);
            }

            // ✅ UPDATE INVENTORY (IMPORTANT)
            // $inventory->empty_jars = $remaining;
            // $inventory->save();

            // ✅ CREATE ORDER
            $order = DistributorPlantOrder::create([
                'plant_id' => $request->plant_id,
                'distributor_id' => $user->id,

                'delivered_jars' => $delivered,

                'required_labeled_jars' => $labeled,
                'required_unlabeled_jars' => $unlabeled,

                'jars_with_label' => $request->jars_with_label,
                'allow_remaining_stock' => $request->allow_remaining_stock,

                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status
                ],
                'summary' => [
                    'previous_stock' => $previousStock,
                    'delivered_jars' => $delivered,
                    'total_available' => $totalAvailable,
                    'total_required' => $totalRequired,
                    'remaining_stock' => $remaining
                ]
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $order = DistributorPlantOrder::query()
                ->when($this->plantManagerId, function ($q) {
                    $q->where('plant_id', $this->plantManagerId)
                    ->with('distributor');
                }, function ($q) {
                    $q->where('distributor_id', auth()->id())
                    ->with('plant');
                })
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Order not found or unauthorized'
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
