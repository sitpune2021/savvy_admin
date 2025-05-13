<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Facades\Hash;


class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::with('user:id,name,email')->orderBy('created_at', 'desc')->get();
        return view('pages.Admin.vendor.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;  
        return view('pages.Admin.vendor.add-edit',compact('show'));  
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'email' => ['required','email','regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/', Rule::unique('users')],
            'phone_number' => ['required','string','digits:10',Rule::unique('vendors')->whereNull('deleted_at')],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'country' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('Saavy@123'),
                'role' => 'vendor',
            ]);
            $data['user_id'] = $user->id;
            Vendor::create($data);
            return response()->json([
                'message' => 'Vendor created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $vendor = Vendor::findOrFail($id);
        return view('pages.Admin.vendor.add-edit',compact('show', 'vendor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $vendor = Vendor::findOrFail($id);
            return view('pages.Admin.vendor.add-edit',compact('show', 'vendor'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'vendor not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the vendor for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'email' => ['required','email','regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/', Rule::unique('users')->ignore($vendor->user_id)],
            'phone_number' => ['required','string','digits:10',Rule::unique('vendors')->ignore($id)->whereNull('deleted_at')],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'country' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $vendor = Vendor::findOrFail($id);
            $user = User::findOrFail($vendor->user_id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            $vendor->update($request->all());
            return response()->json([
                'message' => 'Vendor updated successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Vendor not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
