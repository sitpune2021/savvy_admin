<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Drivers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Exception;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = Drivers::all();
        return view('pages.driver.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;        
        return view('pages.driver.add-edit',compact('show'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:drivers,email',
            'phone_no' => 'required|string|max:20',
            'full_address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'license_no' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:255',
            'vehicle_name' => 'nullable|string|max:255',
            'pan_card' => 'nullable|string|max:255',
            'aadhar_card' => 'nullable|string|max:255',
            'pan_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pan_card_FILE = null;
            $aadhar_card_FILE = null;

            if ($request->hasFile('pan_card_FILE')) {
                $panCard = $request->file('pan_card_FILE');
                $panCardFile = Str::random(10) . '.' . $panCard->getClientOriginalExtension();
                $panCard->storeAs('public/driver', $panCardFile);
                $pan_card_FILE = $panCardFile;
            }
            if ($request->hasFile('aadhar_card_FILE')) {
                $aadharCard = $request->file('aadhar_card_FILE');
                $aadharCardFile = Str::random(10) . '.' . $aadharCard->getClientOriginalExtension();
                $aadharCard->storeAs('public/driver', $aadharCardFile);
                $aadhar_card_FILE = $aadharCardFile;
            }
            $data = $request->all();
            $data['pan_card_FILE'] = $pan_card_FILE;
            $data['aadhar_card_FILE'] = $aadhar_card_FILE;

            Drivers::create($data);
            
            return redirect()->route('drivers.index')->with('success', 'Driver added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to add driver: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $Driver = Drivers::findOrFail($id);
        return view('pages.driver.add-edit',compact('show', 'Driver'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Driver = Drivers::findOrFail($id);
            return view('pages.driver.add-edit',compact('show', 'Driver'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Driver not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Driver for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:drivers,email,' . $id,
            'phone_no' => 'required|string|max:20',
            'full_address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'license_no' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:255',
            'vehicle_name' => 'nullable|string|max:255',
            'pan_card' => 'nullable|string|max:255',
            'aadhar_card' => 'nullable|string|max:255',
            'pan_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $Driver = Drivers::findOrFail($id);
            $Driver->update($request->except('pan_card_FILE', 'aadhar_card_FILE'));

        
            // Handle Pan Card Upload
            if ($request->hasFile('pan_card_FILE')) {
                if ($Driver->pan_card_FILE) {
                    Storage::delete('public/driver/' . $Driver->pan_card_FILE); // Corrected $jobPost to $Driver
                }
                $panCard = $request->file('pan_card_FILE');
                $panCardFile = Str::random(10) . '.' . $panCard->getClientOriginalExtension();
                $panCard->storeAs('public/driver', $panCardFile);
                $Driver->pan_card_FILE = $panCardFile;
            }
        
            // Handle Aadhar Card Upload
            if ($request->hasFile('aadhar_card_FILE')) {
                if ($Driver->aadhar_card_FILE) {
                    Storage::delete('public/driver/' . $Driver->aadhar_card_FILE); // Corrected $jobPost to $Driver
                }
                $aadharCard = $request->file('aadhar_card_FILE');
                $aadharCardFile = Str::random(10) . '.' . $aadharCard->getClientOriginalExtension();
                $aadharCard->storeAs('public/driver', $aadharCardFile);
                $Driver->aadhar_card_FILE = $aadharCardFile;
            }
        
            // Update other driver details
            $Driver->update($request->except('aadhar_card_FILE', 'pan_card_FILE'));
        
            return response()->json([
                'message' => 'Driver updated successfully!',
            ]);
        
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Driver not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the Driver: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $driver = Drivers::findOrFail($id);
            if ($driver->pan_card_FILE) {
                Storage::delete('public/driver/' . $driver->pan_card_FILE); 
            }
            if ($driver->aadhar_card_FILE) {
                Storage::delete('public/driver/' . $driver->aadhar_card_FILE);
            }
                $driver->delete();
                return response()->json([
                    'message' => 'Driver  deleted successfully.',
                ], 200);
        } catch (ModelNotFoundException $e) {
                return response()->json([
                    'error' => 'Driver not found.',
                    'message' => $e->getMessage(),
                ], 404); 
        } catch (Exception $e) {
                return response()->json([
                    'error' => 'An error occurred while deleting the  Driver.',
                    'message' => $e->getMessage(),
                ], 500);
        }
    }
}
