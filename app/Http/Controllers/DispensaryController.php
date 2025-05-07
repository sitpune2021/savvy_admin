<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineDispensary;
use App\Models\Customers;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Validator;
use Exception;


class DispensaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dispensaries = MachineDispensary::with('customer', 'shipping')->orderBy('created_at', 'desc') // or 'id', depending on your use case
        ->get();
        return view('pages.dispensaries.index', compact('dispensaries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;
        $customers = Customers::whereHas('contracts')
        ->whereHas('shippingAddresses', function ($query) {
            $query->where('machine_deployed', 'yes');
        })
        ->with(['shippingAddresses' => function ($query) {
            $query->where('machine_deployed', 'yes');
        }])
        ->get();
        return view('pages.dispensaries.add-edit',compact('show', 'customers' ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_name' => 'required|string',
            'serial_number' => 'required|string|unique:machine_dispensaries',
            'machine_type' => 'required|in:2_tab,3_tab',
            'customer_id' => 'required|exists:customers,id',
            'shipping_id' => 'required|exists:shipping_addresses,id',
            'documents' => 'nullable|string',
            'warranty' => 'required|string',
            'garanty' => 'required|string',
      
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $document = null;
            if ($request->hasFile('documents')) {
                $documents = $request->file('documents');
                $documentsFile = Str::random(10) . '.' . $documents->getClientOriginalExtension();
                $documents->storeAs('public/dispensary', $documentsFile);
                $document = $documentsFile;
            }
            $data = $request->all();
            $data['documents'] = $document;
            MachineDispensary::create($data);
            return response()->json(['message' => 'Dispensary created successfully'], 200);
        }
        catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $Dispensary = MachineDispensary::findOrFail($id);
        $customers = Customers::whereHas('contracts')
        ->whereHas('shippingAddresses', function ($query) {
            $query->where('machine_deployed', 'yes');
        })
        ->with(['shippingAddresses' => function ($query) {
            $query->where('machine_deployed', 'yes');
        }])
        ->get();
        return view('pages.dispensaries.add-edit',compact('show', 'Dispensary', 'customers' ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Dispensary = MachineDispensary::findOrFail($id);
            $customers = Customers::whereHas('contracts')
            ->whereHas('shippingAddresses', function ($query) {
                $query->where('machine_deployed', 'yes');
            })
            ->with(['shippingAddresses' => function ($query) {
                $query->where('machine_deployed', 'yes');
            }])
            ->get();
            return view('pages.dispensaries.add-edit',compact('show', 'Dispensary', 'customers' ));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Dispensary not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Dispensary for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'model_name' => 'required|string',
            'serial_number' => 'required|string|unique:machine_dispensaries,serial_number,' . $id,
            'machine_type' => 'required|in:2_tab,3_tab',
            'customer_id' => 'required|exists:customers,id',
            'shipping_id' => 'required|exists:shipping_addresses,id',
            'documents' => 'nullable|string',
            'warranty' => 'required|string',
            'garanty' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $dispensary = MachineDispensary::findOrFail($id);
            $dispensary->update($request->except('documents'));

            if ($request->hasFile('documents')) {
                if ($dispensary->documents) {
                    Storage::delete('public/dispensary/' . $dispensary->documents); // Corrected $jobPost to $Driver
                }
                $documents = $request->file('documents');
                $documentsFile = Str::random(10) . '.' . $documents->getClientOriginalExtension();
                $documents->storeAs('public/dispensary', $documentsFile);
                $dispensary->documents = $documentsFile;
            }

            $dispensary->update($request->except('documents'));
            return response()->json(['message' => 'Dispensary updated successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Dispensary not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while updating the Dispensary: ' . $e->getMessage()]);
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
