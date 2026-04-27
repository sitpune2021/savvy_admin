<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Distributor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Exception;


class DistributorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $distributors = Distributor::orderBy('created_at', 'desc')->get();
        return view('pages.Admin.distributor.index', compact('distributors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;  
        return view('pages.Admin.distributor.add-edit',compact('show'));  
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zoho_id' => [
                'required',
                'string',
                Rule::unique('distributors')->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('distributors')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('distributors')->whereNull('deleted_at'),
            ],
            'phone_no' => [
                'required',
                'digits:10',
                Rule::unique('distributors')->whereNull('deleted_at'),
            ],
            
            'full_address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'pincode' => 'required|digits:6',
            'po_no' => 'nullable|string|max:255',
            'license_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{6}$/',
            'tempo_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{2}[A-Z]{1,2}[0-9]{4}$/',
            'tempo_name' => 'nullable|string|max:255',
            'pan_card' => 'nullable|string|max:255|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'aadhar_card' => 'nullable|string|max:255|regex:/^[0-9]{12}$/',
            'pan_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            $pan_card_FILE = null;
            $aadhar_card_FILE = null;

            if ($request->hasFile('pan_card_FILE')) {
                $panCard = $request->file('pan_card_FILE');
                $panCardFile = Str::random(10) . '.' . $panCard->getClientOriginalExtension();
                $panCard->storeAs('public/distributor', $panCardFile);
                $pan_card_FILE = $panCardFile;
            }
            if ($request->hasFile('aadhar_card_FILE')) {
                $aadharCard = $request->file('aadhar_card_FILE');
                $aadharCardFile = Str::random(10) . '.' . $aadharCard->getClientOriginalExtension();
                $aadharCard->storeAs('public/distributor', $aadharCardFile);
                $aadhar_card_FILE = $aadharCardFile;
            }
            $data = $request->all();
            $data['pan_card_FILE'] = $pan_card_FILE;
            $data['aadhar_card_FILE'] = $aadhar_card_FILE;
            $data['password'] = Hash::make('Distributor@123');
            Distributor::create($data);
            return response()->json([
                'message' => 'Distributor created successfully',
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
        $distributor = Distributor::findOrFail($id);
        return view('pages.Admin.distributor.add-edit',compact('show', 'distributor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $distributor = Distributor::findOrFail($id);
            return view('pages.Admin.distributor.add-edit',compact('show', 'distributor'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Distributor not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the distributor for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $distributor = Distributor::findOrFail($id);
        $validator = Validator::make($request->all(), [
           'zoho_id' => [
                'required',
                'string',
                Rule::unique('distributors')->whereNull('deleted_at')->ignore($distributor->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('distributors')->whereNull('deleted_at')->ignore($distributor->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
                Rule::unique('distributors')->whereNull('deleted_at')->ignore($distributor->id),
            ],
            'phone_no' => [
                'required',
                'digits:10',
                Rule::unique('distributors')->whereNull('deleted_at')->ignore($distributor->id),
            ],
            
            'full_address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'pincode' => 'required|digits:6',
            'po_no' => 'nullable|string|max:255',
            'license_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{6}$/',
            'tempo_no' => 'nullable|string|max:255|regex:/^[A-Z]{2}[0-9]{2}[A-Z]{1,2}[0-9]{4}$/',
            'tempo_name' => 'nullable|string|max:255',
            'pan_card' => 'nullable|string|max:255|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'aadhar_card' => 'nullable|string|max:255|regex:/^[0-9]{12}$/',
            'pan_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhar_card_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $distributor = Distributor::findOrFail($id);
            $data = $request->except('pan_card_FILE', 'aadhar_card_FILE');
            $distributor->update($data);

        
            if ($request->hasFile('pan_card_FILE')) {
                if ($distributor->pan_card_FILE) {
                    Storage::delete('public/distributor/' . $distributor->pan_card_FILE); // Corrected $jobPost to $distributor
                }
                $panCard = $request->file('pan_card_FILE');
                $panCardFile = Str::random(10) . '.' . $panCard->getClientOriginalExtension();
                $panCard->storeAs('public/distributor', $panCardFile);
                $distributor->pan_card_FILE = $panCardFile;
            }
        
            if ($request->hasFile('aadhar_card_FILE')) {
                if ($distributor->aadhar_card_FILE) {
                    Storage::delete('public/distributor/' . $distributor->aadhar_card_FILE); // Corrected $jobPost to $distributor
                }
                $aadharCard = $request->file('aadhar_card_FILE');
                $aadharCardFile = Str::random(10) . '.' . $aadharCard->getClientOriginalExtension();
                $aadharCard->storeAs('public/distributor', $aadharCardFile);
                $distributor->aadhar_card_FILE = $aadharCardFile;
            }
        
            $distributor->update($request->except('aadhar_card_FILE', 'pan_card_FILE'));
            return response()->json([
                'message' => 'Distributor updated successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Distributor not found'], 404);
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
