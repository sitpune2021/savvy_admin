<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $driverId = $request->driver_id;
            $type = $request->type;


            if (!$driverId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Driver ID is required.',
                ], 422);
            }

            // Assuming you have a Maintenance model and a relationship set up
            $maintenanceRecords = Maintenance::where('driver_id', $driverId)->get();
            if ($type) {
                $maintenanceRecords = $maintenanceRecords->where('type', $type);
            }
            if ($maintenanceRecords->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No maintenance records found for this driver.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Maintenance records retrieved successfully.',
                'data' => $maintenanceRecords
            ], 200);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve maintenance records: '.$e->getMessage(),
            ], 500);
        }
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
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:drivers,id',
            'type' => 'required|string|in:fule,other',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $data = $request->all();
            Maintenance::create($data);
            return response()->json([
                'status' => true,
                'message' => 'Maintenance record added successfully.',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add maintenance record: '.$e->getMessage(),
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
