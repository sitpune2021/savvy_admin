<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        
            $title = $type === 'fuel' ? 'Fuel' : 'Maintenance';
        
            // Fetch maintenance records with the driver relationship
            $maintenanceRecords = Maintenance::where('driver_id', $driverId)
                ->when($type, fn($q) => $q->where('type', $type))
                ->with('driver')
                ->get();
        
            if ($maintenanceRecords->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => "No $title records found for this driver.",
                ], 404);
            }
        
            foreach ($maintenanceRecords as $record) {
                $record->vehicle_no = $record->driver->vehicle_no;
                $images = is_array($record->image) ? $record->image : json_decode($record->image, true);
                if (is_array($images)) {
                    foreach ($images as $key => $imgPath) {
                        $record->$key = $imgPath ? url('storage/'.$type.'/'. $imgPath) : null;
                    }
                }
        
                // Optionally remove original image column
                unset($record->image);
                unset($record->driver); // if you don't want to expose the full driver model
            }
        
            return response()->json([
                'status' => true,
                'message' => "$title records retrieved successfully.",
                'data' => $maintenanceRecords
            ], 200);
        
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve records: ' . $e->getMessage(),
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
            'type' => 'required|string|in:fuel,other',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'images.bill' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // for fuel
            'images.metercopy' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // for other
            'images.recipt' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // for other
            'date' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $data = $request->all();
            $imagePaths = []; // Initialize an empty array to store image paths
            // $title = $request->type == 'fuel' ? 'Fuel' : 'Maintenance';
            if ($request->type == 'other' && $request->hasFile('images.bill')) {
                $billImage = $request->file('images.bill');
                $billImageName = Str::random(10) . '.' . $billImage->getClientOriginalExtension();
                $billImage->storeAs('public/other/', $billImageName); // Store in other directory
                $imagePaths['bill'] = $billImageName; // Save the image file name
            }
    
            if ($request->type == 'fuel') {
                if ($request->hasFile('images.metercopy')) {
                    $metercopyImage = $request->file('images.metercopy');
                    $metercopyImageName = Str::random(10) . '.' . $metercopyImage->getClientOriginalExtension();
                    $metercopyImage->storeAs('public/fuel/', $metercopyImageName); // Store in fuel directory
                    $imagePaths['metercopy'] = $metercopyImageName;
                }
    
                if ($request->hasFile('images.recipt')) {
                    $reciptImage = $request->file('images.recipt');
                    $reciptImageName = Str::random(10) . '.' . $reciptImage->getClientOriginalExtension();
                    $reciptImage->storeAs('public/fuel/', $reciptImageName); // Store in fuel directory
                    $imagePaths['recipt'] = $reciptImageName;
                }
            }
    
            $data['image'] = json_encode($imagePaths); // Store the image paths as JSON
    
            Maintenance::create($data);
    
            return response()->json([
                'status' => true,
                'message' => 'record added successfully.',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' =>    'Failed to add record: ' . $e->getMessage(),
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
