<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Drivers;
use App\Models\Routes;
use App\Models\Plant;

use Exception;

class DriverController extends BaseController
{
    public function index()
    {
        $query = Drivers::orderBy('created_at', 'desc');
        if ($this->vendorId !== null) {
            $query->where('vendor_id', $this->vendorId);
        }
        $drivers = $query->get();
        if ($drivers->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No drivers found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Drivers retrieved successfully',
            'data' => $drivers
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'plant_id' => 'required|exists:plants,id',
            'route_path' => 'nullable',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('drivers')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('drivers')->whereNull('deleted_at'),
            ],
            'phone_no' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('drivers')->whereNull('deleted_at'),
            ],
            'full_address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'pincode' => 'required|digits:6',
            'license_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{6}$/',
            'vehicle_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{2}[A-Z]{1,2}[0-9]{4}$/',
            'vehicle_name' => 'nullable|string|max:255',
            'pan_card' => 'nullable|string|max:255|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'aadhar_card' => 'nullable|string|max:255|regex:/^[0-9]{12}$/',
            'pan_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'license_no.regex' => 'License No must be 2 uppercase letters followed by 6 digits.',
            'vehicle_no.regex' => 'Invalid vehicle number format. Use format like MH12AB1234.',
            'pan_card.regex' => 'Invalid PAN card format. Use format like ABCDE1234F.',
            'aadhar_card.regex' => 'Aadhaar number must be exactly 12 digits.',
        ]);

    
        if ($validator->fails()) {
            if ($this->vendorId !== null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => collect($validator->errors()->all())
                ], 422);
            }
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
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
            if ($this->vendorId !== null) {
                $data['vendor_id'] = $this->vendorId;
            }
            Drivers::create($data);
            return response()->json([
                'status' => true,
                'message' => 'Driver created successfully!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
                'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $Driver = Drivers::findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Driver retrieved successfully.',
                'data' => $Driver
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not found.',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'plant_id' => 'required|exists:plants,id',
            'route_id' => 'required|exists:routes,id',
            'route_path' => 'nullable',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('drivers')->ignore($id)->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('drivers')->ignore($id)->whereNull('deleted_at'),
            ],
            'phone_no' => [
                'required',
                'string',
                'digits:10',
                Rule::unique('drivers')->ignore($id)->whereNull('deleted_at'),
            ],
            'full_address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'pincode' => 'required|digits:6',
            'license_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{6}$/',
            'vehicle_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{2}[A-Z]{1,2}[0-9]{4}$/',
            'vehicle_name' => 'nullable|string|max:255',
            'pan_card' => 'nullable|string|max:255|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'aadhar_card' => 'nullable|string|max:255|regex:/^[0-9]{12}$/',
            'pan_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'license_no.regex' => 'License No must be 2 uppercase letters followed by 6 digits.',
            'vehicle_no.regex' => 'Invalid vehicle number format. Use format like MH12AB1234.',
            'pan_card.regex' => 'Invalid PAN card format. Use format like ABCDE1234F.',
            'aadhar_card.regex' => 'Aadhaar number must be exactly 12 digits.',


        ]);

        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
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
                'status' => true,
                'message' => 'Driver updated successfully!',
            ]);
        
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not found.',
                'error' => 'Driver not found.'], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the Driver.',
                'error' => 'An error occurred while updating the Driver: ' . $e->getMessage()], 500);
        }
    }

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
                    'status' => true,
                    'message' => 'Driver  deleted successfully.',
                ], 200);
        } catch (ModelNotFoundException $e) {
                return response()->json([
                    'status' => false,
                    'error' => 'Driver not found.',
                    'message' => $e->getMessage(),
                ], 404); 
        } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'error' => 'An error occurred while deleting the  Driver.',
                    'message' => $e->getMessage(),
                ], 500);
        }
    }
}
