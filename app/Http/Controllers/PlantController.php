<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Routes;
use App\Models\Drivers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class PlantController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Plant::orderBy('created_at', 'desc');

        if ($this->vendorId !== null) {
            $query->where('vendor_id', $this->vendorId);
        }

        $Plants = $query->get();
        return view('pages.plant.index', compact('Plants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false; 
        $vendors = Vendor::with('user:id,name')->get();
        return view('pages.plant.add-edit',compact('show', 'vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('plants')->whereNull('deleted_at'),
            ],
            'address' => 'required|string|max:255',
            'manager' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'email' => [
                'required',
                'email',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->where('role', 'plant-manager');
                }),
            ],
            'location' => 'required|string|max:255',
            'pincode' => 'required|string|digits:6',
            'details' => 'nullable|string|max:255',
            'vendor_id' => (Auth::user()->role === 'admin' && $request->type === 'pan_india')
                        ? 'required|exists:vendors,id'
                        : 'nullable|exists:vendors,id',

        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            if ($this->vendorId !== null) {
                $data['vendor_id'] = $this->vendorId;
            }
                $user = User::create([
                    'name' => $request->manager,
                    'email' => $request->email,
                    'password' => Hash::make('Saavy@123'),
                    'role' => 'plant-manager',
                ]);
                $data['manager_id'] = $user->id;
            Plant::create($data);
            
            return response()->json(['message' => 'Plant added successfully'], 200);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to add plant: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $Plant = Plant::findOrFail($id);
        $vendors = Vendor::with('user:id,name')->get();
        return view('pages.plant.add-edit',compact('show', 'Plant', 'vendors'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Plant = Plant::findOrFail($id);
            $vendors = Vendor::with('user:id,name')->get();
            return view('pages.plant.add-edit',compact('show', 'Plant', 'vendors'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Plant not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Plant for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('plants', 'name')->ignore($id)->whereNull('deleted_at'),
            ],
            'address' => 'required|string|max:255',
            'manager' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'manager_id' => 'nullable|exists:users,id',
            'email' => [
                'required',
                'email',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('users', 'email')->ignore($request->manager_id)->where(function ($query) {
                    return $query->where('role', 'plant-manager');
                }),
            ],
            'location' => 'required|string|max:255',
            'pincode' => 'required|string|digits:6',
            'details' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $Plant = Plant::findOrFail($id);
            $data = $request->all(); 
            if($Plant->manager_id == null){
                $user = User::create([
                    'name' => $request->manager,
                    'email' => $request->email,
                    'password' => Hash::make('Saavy@123'),
                    'role' => 'plant-manager',
                ]);
                $data['manager_id'] = $user->id;
            }else{
                $user = User::findOrFail($Plant->manager_id);
                $user->name = $request->manager;
                $user->email = $request->email;
                $user->save();
            }

            Routes::where('plant_id', $id)->update(['vendor_id' => $request->vendor_id]);
            Drivers::where('plant_id', $id)->update(['vendor_id' => $request->vendor_id]);
            $Plant->update($data);
            return response()->json(['message' => 'Plant updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['errors' => 'Failed to update plant: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Plant = Plant::findOrFail($id);
            $Plant->delete();
            return redirect()->route('plant.index')->with('success', 'Plant deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Plant not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while deleting the Plant: ' . $e->getMessage()]);
        }
    }
}
